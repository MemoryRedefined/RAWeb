<?php

use Illuminate\Support\Facades\Validator;

if (!authenticateFromCookie($user, $permissions, $userDetails)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'message' => 'required|integer|exists:mysql_legacy.Messages,ID',
]);

if (DeleteMessage($user, (int) $input['message'])) {
    return back()->with('success', __('legacy.success.delete'));
}

return back()->withErrors(__('legacy.error.error'));
