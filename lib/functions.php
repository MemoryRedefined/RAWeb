<?php

// TODO migrate to/replace with controllers, models, views

require_once __DIR__ . '/database/achievement.php';
require_once __DIR__ . '/database/achievement-creator.php';
require_once __DIR__ . '/database/achievement-points.php';
require_once __DIR__ . '/database/code-note.php';
require_once __DIR__ . '/database/console.php';
require_once __DIR__ . '/database/forum.php';
require_once __DIR__ . '/database/game.php';
require_once __DIR__ . '/database/hash.php';
require_once __DIR__ . '/database/leaderboard.php';
require_once __DIR__ . '/database/message.php';
require_once __DIR__ . '/database/player-achievement.php';
require_once __DIR__ . '/database/player-game.php';
require_once __DIR__ . '/database/player-history.php';
require_once __DIR__ . '/database/player-rank.php';
require_once __DIR__ . '/database/rating.php';
require_once __DIR__ . '/database/release.php';
require_once __DIR__ . '/database/search.php';
require_once __DIR__ . '/database/set-claim.php';
require_once __DIR__ . '/database/set-request.php';
require_once __DIR__ . '/database/site-award.php';
require_once __DIR__ . '/database/static.php';
require_once __DIR__ . '/database/subscription.php';
require_once __DIR__ . '/database/ticket.php';
require_once __DIR__ . '/database/user.php';
require_once __DIR__ . '/database/user-account-deletion.php';
require_once __DIR__ . '/database/user-activity.php';
require_once __DIR__ . '/database/user-auth.php';
require_once __DIR__ . '/database/user-email-verify.php';
require_once __DIR__ . '/database/user-password-reset.php';
require_once __DIR__ . '/database/user-permission.php';
require_once __DIR__ . '/database/user-relationship.php';

require_once __DIR__ . '/render/achievement.php';
require_once __DIR__ . '/render/avatar.php';
require_once __DIR__ . '/render/code-note.php';
require_once __DIR__ . '/render/comment.php';
require_once __DIR__ . '/render/content.php';
require_once __DIR__ . '/render/forum.php';
require_once __DIR__ . '/render/game.php';
require_once __DIR__ . '/render/layout.php';
require_once __DIR__ . '/render/leaderboard.php';
require_once __DIR__ . '/render/news.php';
require_once __DIR__ . '/render/player-game-compare.php';
require_once __DIR__ . '/render/set-claim.php';
require_once __DIR__ . '/render/shortcode.php';
require_once __DIR__ . '/render/site-award.php';
require_once __DIR__ . '/render/static.php';
require_once __DIR__ . '/render/subscription.php';
require_once __DIR__ . '/render/ticket.php';
require_once __DIR__ . '/render/twitch.php';
require_once __DIR__ . '/render/user.php';

require_once __DIR__ . '/util/array.php';
require_once __DIR__ . '/util/bit.php';
require_once __DIR__ . '/util/cookie.php';
require_once __DIR__ . '/util/database.php';
require_once __DIR__ . '/util/date.php';
require_once __DIR__ . '/util/mail.php';
require_once __DIR__ . '/util/media.php';
require_once __DIR__ . '/util/mobile-browser.php';
require_once __DIR__ . '/util/recaptcha.php';
require_once __DIR__ . '/util/request.php';
require_once __DIR__ . '/util/string.php';
require_once __DIR__ . '/util/trigger.php';
