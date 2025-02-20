<?php

use App\Legacy\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RA\ActivityType;
use RA\Permissions;

function authenticateFromPasswordOrAppToken($user, $pass, $token): array
{
    sanitize_sql_inputs($user, $token);

    $query = null;
    $response = [];

    $passwordProvided = (isset($pass) && mb_strlen($pass) >= 1);
    $tokenProvided = (isset($token) && mb_strlen($token) >= 1);

    if (!isset($user) || $user == false || mb_strlen($user) < 2) {
        // username failed: empty user
    } else {
        if ($passwordProvided) {
            // Password provided, validate it
            if (authenticateFromPassword($user, $pass)) {
                $query = "SELECT RAPoints, RASoftcorePoints, Permissions, appToken FROM UserAccounts WHERE User='$user'";
            }
        } elseif ($tokenProvided) {
            // Token provided, look for match
            $query = "SELECT RAPoints, RASoftcorePoints, Permissions, appToken, appTokenExpiry FROM UserAccounts WHERE User='$user' AND appToken='$token'";
        }
    }

    if (!$query) {
        $response['Success'] = false;
        $response['Error'] = "Invalid User/Password combination. Please try again";

        return $response;
    }

    $dbResult = s_mysql_query($query);

    if (!$dbResult) {
        $response['Success'] = false;
        $response['Error'] = "Invalid User/Password combination. Please try again";

        return $response;
    }

    $data = mysqli_fetch_assoc($dbResult);
    if ($data !== false && mysqli_num_rows($dbResult) == 1) {
        // Test for expired tokens!
        if ($tokenProvided) {
            $expiry = $data['appTokenExpiry'];
            if (time() > strtotime($expiry)) {
                generateAppToken($user, $token);
                // Expired!
                $response['Success'] = false;
                $response['Error'] = "Automatic login failed (token expired), please login manually";

                return $response;
            }
        }

        if (mb_strlen($data['appToken']) !== 16) {   // Generate if new
            generateAppToken($user, $tokenInOut);
        } else {
            // Return old token if not
            $token = $data['appToken'];

            // Update app token expiry now anyway
            $expDays = 14;
            $expiryStr = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * $expDays);
            $query = "UPDATE UserAccounts SET appTokenExpiry='$expiryStr' WHERE User='$user'";
            s_mysql_query($query);
        }

        postActivity($user, ActivityType::Login, "");

        $response['Success'] = true;
        $response['User'] = $user;
        $response['Token'] = $token;
        $response['Score'] = (int) $data['RAPoints'];
        $response['SoftcoreScore'] = (int) $data['RASoftcorePoints'];
        $response['Messages'] = GetMessageCount($user, $totalMessageCount);
        $response['Permissions'] = (int) $data['Permissions'];
        $response['AccountType'] = Permissions::toString($response['Permissions']);
    } else {
        $response['Success'] = false;
        $response['Error'] = "Invalid User/Password combination. Please try again";
    }

    return $response;
}

/*
 * PASSWORD
 */

function authenticateFromPassword(&$user, $pass): bool
{
    if (!isValidUsername($user)) {
        return false;
    }

    sanitize_sql_inputs($user);

    $query = "SELECT ID, User, Password, SaltedPass, cookie, Permissions FROM UserAccounts WHERE User='$user'";
    $result = s_mysql_query($query);
    if (!$result) {
        return false;
    }

    $row = mysqli_fetch_array($result);

    if (!$row) {
        return false;
    }

    if ($row['Permissions'] < Permissions::Unregistered) {
        return false;
    }

    $hashedPassword = $row['Password'];

    if (mb_strlen($row['SaltedPass']) === 32) {
        $pepperedPassword = md5($pass . config('app.legacy_password_salt'));
        if ($row['SaltedPass'] !== $pepperedPassword) {
            return false;
        }
        $hashedPassword = migratePassword($user, $pass);
    }

    if (!password_verify($pass, $hashedPassword)) {
        return false;
    }

    Auth::loginUsingId($row['ID']);

    $user = $row['User'];

    return true;
}

function changePassword($user, $pass): bool
{
    sanitize_sql_inputs($user);

    $hashedPassword = hashPassword($pass);
    $query = "UPDATE UserAccounts SET Password='$hashedPassword', SaltedPass='', Updated=NOW() WHERE user='$user'";
    if (s_mysql_query($query) === false) {
        log_sql_fail();

        return false;
    }

    return true;
}

function hashPassword($pass): string
{
    return password_hash($pass, PASSWORD_ARGON2ID, [
        'memory_cost' => 1024,
        'threads' => 2,
        'time' => 1,
    ]);
}

function migratePassword($user, $pass): string
{
    $hashedPassword = hashPassword($pass);
    s_mysql_query("UPDATE UserAccounts SET Password='$hashedPassword', SaltedPass='' WHERE User='$user'");

    return $hashedPassword;
}

/*
 * COOKIE
 */

function authenticateFromCookie(
    ?string &$userOut,
    ?int &$permissionsOut,
    ?array &$userDetailsOut = null,
    ?int $minPermissions = null
): bool {
    $userOut = null;
    $permissionsOut = Permissions::Unregistered;

    // remove legacy cookies
    cookie()->forget('RA_User');
    cookie()->forget('RA_Cookie');
    cookie()->forget('RAPrefs_CSS');

    /** @var ?User $user */
    $user = auth()->user();

    if (!$user) {
        return false;
    }

    $userDetailsOut = $user->toArray();
    $userOut = $user->getAttribute('User');
    $permissionsOut = $user->getAttribute('Permissions');

    if ($permissionsOut === Permissions::Banned) {
        return false;
    }

    // valid active account. update the last activity timestamp
    userActivityPing($userOut);

    // validate permissions for the current page if required
    if (isset($minPermissions) && $permissionsOut < $minPermissions) {
        if (request()->wantsJson()) {
            abort(403);
        }

        return false;
    }

    return true;
}

/*
 * TOKEN
 */

function authenticateFromAppToken(
    &$userOut,
    $token,
    &$permissionOut
): bool {
    if ($userOut == null || $userOut == '') {
        return false;
    }
    if (!isValidUsername($userOut)) {
        return false;
    }
    if (empty($token)) {
        return false;
    }

    sanitize_sql_inputs($userOut);

    /** @var ?User $user */
    $user = auth('connect-token-legacy')->user();

    if (!$user) {
        return false;
    }

    $userOut = $user->getAttribute('User');
    $permissionOut = $user->getAttribute('Permissions');

    return true;
}

function generateAppToken($user, &$tokenOut): bool
{
    if (empty($user)) {
        return false;
    }
    sanitize_sql_inputs($user);
    $newToken = Str::random(16);

    $expDays = 14;
    $expiryStr = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * $expDays);
    $query = "UPDATE UserAccounts SET appToken='$newToken', appTokenExpiry='$expiryStr', Updated=NOW() WHERE User='$user'";
    $result = s_mysql_query($query);
    if ($result !== false) {
        $tokenOut = $newToken;

        return true;
    } else {
        return false;
    }
}

/*
 * WEB API Key
 * TODO replace with passport personal token
 */

function generateAPIKey($user): string
{
    sanitize_sql_inputs($user);

    if (!getAccountDetails($user, $userData)) {
        return "";
    }

    if ($userData['Permissions'] < Permissions::Registered) {
        return "";
    }

    $newKey = Str::random(32);

    $query = "UPDATE UserAccounts AS ua
              SET ua.APIKey='$newKey', Updated=NOW()
              WHERE ua.User = '$user'";

    $dbResult = s_mysql_query($query);
    if (!$dbResult) {
        return "";
    }

    return $newKey;
}

function GetAPIKey($user): ?string
{
    sanitize_sql_inputs($user);

    if (!isValidUsername($user)) {
        return null;
    }

    $query = "SELECT APIKey FROM UserAccounts AS ua
        WHERE ua.User = '$user' AND ua.Permissions >= " . Permissions::Registered;

    $dbResult = s_mysql_query($query);
    if (!$dbResult) {
        return null;
    } else {
        $db_entry = mysqli_fetch_assoc($dbResult);

        return $db_entry['APIKey'];
    }
}
