<?php

use App\Legacy\Models\ForumTopic;
use Illuminate\Support\Facades\Validator;
use RA\Permissions;

if (!authenticateFromCookie($username, $permissions, $userDetails, Permissions::Registered)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'topic' => 'required|integer|exists:mysql_legacy.ForumTopic,ID',
    'title' => 'required|string|max:255',
]);

/** @var ForumTopic $topic */
$topic = ForumTopic::find($input['topic']);

if ($permissions < Permissions::Admin && $topic->Author !== $username) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$topic->Title = $input['title'];
$topic->save();

return back()->with('success', __('legacy.success.modify'));
