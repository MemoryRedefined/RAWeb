<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::Admin)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'topic' => 'required|integer|exists:mysql_legacy.ForumTopic,ID',
    'permissions' => ['required', 'integer', Rule::in(Permissions::assignable())],
]);

if (updateTopicPermissions((int) $input['topic'], (int) $input['permissions'])) {
    return back()->with('success', __('legacy.success.ok'));
}

return back()->withErrors(__('legacy.error.error'));
