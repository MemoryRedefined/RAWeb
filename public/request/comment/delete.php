<?php

use Illuminate\Support\Facades\Validator;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::Registered)) {
    abort(401);
}

$input = Validator::validate(request()->post(), [
    'comment' => 'required|integer|exists:mysql_legacy.Comment,ID',
]);

if (RemoveComment((int) $input['comment'], $userDetails['ID'], $permissions)) {
    return response()->json(['message' => __('legacy.success.delete')]);
}

abort(400);
