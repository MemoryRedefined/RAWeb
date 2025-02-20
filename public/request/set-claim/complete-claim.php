<?php

use Illuminate\Support\Facades\Validator;
use RA\ArticleType;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::JuniorDeveloper)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'game' => 'required|integer|exists:mysql_legacy.GameData,ID',
]);

$gameID = (int) $input['game'];

if (completeClaim($user, $gameID)) { // Check that the claim was successfully completed
    addArticleComment("Server", ArticleType::SetClaim, $gameID, "Claim completed by " . $user);

    // Send email to set requestors
    $requestors = getSetRequestorsList($gameID, true); // need this to get email and probably game name to pass in.
    foreach ($requestors as $requestor) {
        sendSetRequestEmail($requestor['Requestor'], $requestor['Email'], $gameID, $requestor['Title']);
    }

    return back()->with('success', __('legacy.success.ok'));
}

return back()->withErrors(__('legacy.error.error'));
