<?php

use Illuminate\Support\Facades\Validator;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::Developer)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'game' => 'required|integer|exists:mysql_legacy.GameData,ID',
]);

if (generateGameForumTopic($user, (int) $input['game'], $forumTopicID)) {
    return redirect(url("/viewtopic.php?t=$forumTopicID"))->with('success', __('legacy.success.create'));
}

return back()->withErrors(__('legacy.error.error'));
