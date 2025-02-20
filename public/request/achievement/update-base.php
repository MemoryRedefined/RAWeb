<?php

use Illuminate\Support\Facades\Validator;
use RA\Permissions;

if (!authenticateFromCookie($user, $permissions, $userDetails, Permissions::JuniorDeveloper)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

$input = Validator::validate(request()->post(), [
    'achievement' => 'required|integer|exists:mysql_legacy.Achievements,ID',
    'title' => 'required|string|max:64',
    'description' => 'required|max:255',
    'points' => 'required|integer',
]);

$achievement = GetAchievementData((int) $input['achievement']);

// Only allow jr. devs to update base data if they are the author
if ($permissions == Permissions::JuniorDeveloper && $user != $achievement['Author']) {
    abort(403);
}

if (UploadNewAchievement(
    author: $user,
    gameID: $achievement['GameID'],
    title: $input['title'],
    desc: $input['description'],
    progress: $achievement['Progress'],
    progressMax: $achievement['ProgressMax'],
    progressFmt: $achievement['ProgressFormat'],
    points: $input['points'],
    mem: $achievement['MemAddr'],
    type: $achievement['Flags'],
    idInOut: $achievement['ID'],
    badge: $achievement['BadgeName'],
    errorOut: $errorOut
)) {
    return response()->json(['message' => __('legacy.success.achievement_update')]);
}

abort(400);
