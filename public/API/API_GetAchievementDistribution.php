<?php

/*
 *  API_GetAchievementDistribution - returns a mapping of the number of players who have earned each quantity of achievements for a game
 *    i : game id
 *    h : hardcore - 1 to only query hardcore unlocks, 0 to query all unlocks (default: 0)
 *    f : flags - 3 for core achievements, 5 for unofficial (default: 3)
 *
 *  map
 *   string     [key]      number of achievements earned
 *    int        [value]   number of players who have earned that many achievements
 */

$gameID = (int) request()->query('i');
$hardcore = (int) request()->query('h');
$requestedBy = request()->query('z');
$flags = (int) request()->query('f', '3');

return response()->json(getAchievementDistribution($gameID, $hardcore, $requestedBy, $flags));
