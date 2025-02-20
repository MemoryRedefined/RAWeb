<?php

use App\Legacy\Models\User;
use Illuminate\Support\Facades\Validator;

/** @var ?User $user */
$user = request()->user();
if (!$user) {
    return back()->withErrors(__('legacy.error.account'));
}

$input = Validator::validate(request()->post(), [
    'password_current' => 'required',
    'password' => 'required|confirmed|min:8|different:username',
]);

$username = $user->User;

if (!authenticateFromPassword($username, $input['password_current'])) {
    return back()->withErrors(__('legacy.error.account'));
}

RemovePasswordResetToken($username);

if (changePassword($username, $input['password'])) {
    generateAppToken($username, $tokenInOut);

    return back()->with('success', __('legacy.success.password_change'));
}

return back()->withErrors(__('legacy.error.error'));
