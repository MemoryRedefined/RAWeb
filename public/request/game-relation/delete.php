<?php

use Illuminate\Support\Facades\Validator;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::Developer)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'game' => 'required|integer|exists:mysql_legacy.GameData,ID',
    'relations' => 'required|array',
]);

modifyGameAlternatives($user, (int) $input['game'], toRemove: $input['relations']);

return back()->with('success', __('legacy.success.ok'));
