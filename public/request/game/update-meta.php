<?php

use Illuminate\Support\Facades\Validator;
use RA\ClaimSetType;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::JuniorDeveloper)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'game' => 'required|integer|exists:mysql_legacy.GameData,ID',
    'developer' => 'nullable|string|max:50',
    'publisher' => 'nullable|string|max:50',
    'genre' => 'nullable|string|max:50',
    'release' => 'nullable|string|max:50',
]);

$gameId = (int) $input['game'];

// Only allow jr. devs if they are the sole author of the set or have the primary claim
if ($permissions === Permissions::JuniorDeveloper && (!checkIfSoleDeveloper($user, $gameId) && !hasSetClaimed($user, $gameId, true, ClaimSetType::NewSet))) {
    return back()->withErrors(__('legacy.error.permissions'));
}

if (modifyGameData($user, $gameId, $input['developer'], $input['publisher'], $input['genre'], $input['release'])) {
    return back()->with('success', __('legacy.success.update'));
}

return back()->withErrors(__('legacy.error.error'));
