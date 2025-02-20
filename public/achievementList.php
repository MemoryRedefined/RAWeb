<?php

use RA\AchievementType;

$consoleList = getConsoleList();
$consoleIDInput = requestInputSanitized('z', 0, 'integer');
$mobileBrowser = IsMobileBrowser();

authenticateFromCookie($user, $permissions, $userDetails);

$maxCount = 25;

$count = requestInputSanitized('c', $maxCount, 'integer');
$offset = requestInputSanitized('o', 0, 'integer');
$params = requestInputSanitized('p', 3, 'integer');
$dev = requestInputSanitized('d');

if ($user == null) {
    $params = 3;
}
$flags = match ($params) {
    5 => 5,
    default => 3,
};

$dev_param = null;
if ($dev != null) {
    $dev_param .= "&d=$dev";
}

$sortBy = requestInputSanitized('s', 17, 'integer');
$achCount = getAchievementsList($consoleIDInput, $user, $sortBy, $params, $count, $offset, $achData, $flags, $dev);

$requestedConsole = "";
if ($consoleIDInput !== 0) {
    $requestedConsole = " " . $consoleList[$consoleIDInput];
}

RenderContentStart("Achievement List" . $requestedConsole);
?>
<div id='mainpage'>
    <div id='fullcontainer'>
        <?php
        echo "<div class='navpath'>";
        if ($requestedConsole == "") {
            echo "<b>Achievement List</b>";
        } // NB. This will be a stub page
        echo "</div>";

        echo "<div class='detaillist'>";

        echo "<h3>";
        if ($dev != null) {
            echo "<a href='/user/$dev'>$dev</a>'s ";
        }
        echo "Achievement List</h3>";

        echo "<div class='flex flex-wrap justify-between'>";
        echo "<div>";

        echo $params !== AchievementType::OfficialCore ? "<a href='/achievementList.php?s=$sortBy&p=" . AchievementType::OfficialCore . "$dev_param'>" : "<b>";
        echo "Achievements in Core Sets";
        echo $params !== AchievementType::OfficialCore ? "</a>" : "</b>";
        echo "<br>";

        if ($user !== null) {
            echo $params !== AchievementType::Unofficial ? "<a href='/achievementList.php?s=$sortBy&p=" . AchievementType::Unofficial . "$dev_param'>" : "<b>";
            echo "Achievements in Unofficial Sets";
            echo $params !== AchievementType::Unofficial ? "</a>" : "</b>";
            echo "<br>";

            echo $params !== 1 ? "<a href='/achievementList.php?s=$sortBy&p=1$dev_param'>" : "<b>";
            echo "My Unlocked Achievements";
            echo $params !== 1 ? "</a>" : "</b>";
            echo "<br>";

            // echo $params !== 2 ? "<a href='/achievementList.php?s=$sortBy&p=2$dev_param'>" : "<b>";
            // echo "Achievements I haven't yet unlocked";
            // echo $params !== 2 ? "</a>" : "</b>";
            // echo "<br>";
        }
        echo "</div>";

        if ($user !== null) {
            echo "<div>";
            echo "Filter by developer:<br>";
            echo "<form action='/achievementList.php'>";
            echo "<input type='hidden' name='s' value='$sortBy'>";
            echo "<input type='hidden' name='p' value='$params'>";
            echo "<input size='28' name='d' type='text' value='$dev'>";
            echo "&nbsp;<input type='submit' value='Select' >";
            echo "</form>";
            echo "</div>";
        }

        echo "</div>";

        echo "<div class='float-right'>* = ordered by</div>";
        echo "<br style='clear:both;' />";

        echo "<div class='table-wrapper'><table><tbody>";

        $sort1 = ($sortBy == 1) ? 11 : 1;
        $sort2 = ($sortBy == 2) ? 12 : 2;
        $sort3 = ($sortBy == 13) ? 3 : 13;
        $sort4 = ($sortBy == 4) ? 14 : 4;
        $sort5 = ($sortBy == 5) ? 15 : 5;
        $sort6 = ($sortBy == 6) ? 16 : 6;
        $sort7 = ($sortBy == 17) ? 7 : 17;
        $sort8 = ($sortBy == 18) ? 8 : 18;

        $mark1 = ($sortBy % 10 == 1) ? '&nbsp;*' : '';
        $mark2 = ($sortBy % 10 == 2) ? '&nbsp;*' : '';
        $mark3 = ($sortBy % 10 == 3) ? '&nbsp;*' : '';
        $mark4 = ($sortBy % 10 == 4) ? '&nbsp;*' : '';
        $mark5 = ($sortBy % 10 == 5) ? '&nbsp;*' : '';
        $mark6 = ($sortBy % 10 == 6) ? '&nbsp;*' : '';
        $mark7 = ($sortBy % 10 == 7) ? '&nbsp;*' : '';
        $mark8 = ($sortBy % 10 == 8) ? '&nbsp;*' : '';

        echo "<th></th>";
        echo "<th>";
        echo "<a href='/achievementList.php?s=$sort1&p=$params$dev_param'>Title</a>$mark1";
        echo " / ";
        echo "<a href='/achievementList.php?s=$sort2&p=$params$dev_param'>Desc.</a>$mark2";
        echo "</th>";

        if (!$mobileBrowser) {
            echo "<th class='whitespace-nowrap'>";
            echo "<a href='/achievementList.php?s=$sort3&p=$params$dev_param'>Points</a>$mark3 ";
            echo "<br><span class='TrueRatio'>(<a href='/achievementList.php?s=$sort4&p=$params$dev_param'>Retro Ratio</a>$mark4)</span>";
            echo "</th>";
            echo "<th><a href='/achievementList.php?s=$sort5&p=$params$dev_param'>Author</a>$mark5</th>";
        }

        echo "<th><a href='/achievementList.php?s=$sort6&p=$params$dev_param'>Game</a>$mark6</th>";
        echo "<th><a href='/achievementList.php?s=$sort7&p=$params$dev_param'>Added</a>$mark7</th>";

        if (!$mobileBrowser) {
            echo "<th><a href='/achievementList.php?s=$sort8&p=$params$dev_param'>Modified</a>$mark8</th>";
        }

        foreach ($achData as $achEntry) {
            // $query = "SELECT ach.ID, ach.Title AS AchievementTitle, ach.Description, ach.Points, ach.Author, ach.DateCreated, ach.DateModified, ach.BadgeName, ach.GameID, gd.Title AS GameTitle, gd.ConsoleID, c.Name AS ConsoleName ";

            $achID = $achEntry['ID'];
            $achTitle = $achEntry['AchievementTitle'];
            $achDesc = $achEntry['Description'];
            $achPoints = $achEntry['Points'];
            $achTruePoints = $achEntry['TrueRatio'];
            $achAuthor = $achEntry['Author'];
            $achDateCreated = $achEntry['DateCreated'];
            $achDateModified = $achEntry['DateModified'];
            $achBadgeName = $achEntry['BadgeName'];
            $gameID = $achEntry['GameID'];
            $gameIcon = $achEntry['GameIcon'];
            $gameTitle = $achEntry['GameTitle'];
            $consoleID = $achEntry['ConsoleID'];
            $consoleName = $achEntry['ConsoleName'];

            sanitize_outputs(
                $achTitle,
                $achDesc,
                $achAuthor,
                $gameTitle,
                $consoleName
            );

            echo "<tr>";

            echo "<td>";
            echo achievementAvatar($achEntry, label: false);
            echo "</td>";
            echo "<td class='w-full'>";
            echo achievementAvatar($achEntry, icon: false);
            echo "<br>$achDesc";
            echo "</td>";

            if (!$mobileBrowser) {
                echo "<td>";
                echo "$achPoints ";
                echo "<span class='TrueRatio'>($achTruePoints)</span>";
                echo "</td>";

                echo "<td>";
                echo userAvatar($achAuthor, label: false);
                echo "</td>";
            }

            echo "<td>";
            echo gameAvatar($achEntry, label: false);
            echo "</td>";

            echo "<td>";
            echo "<span class='smalldate'>" . getNiceDate(strtotime($achDateCreated)) . "</span>";
            echo "</td>";

            if (!$mobileBrowser) {
                echo "<td>";
                echo "<span class='smalldate'>" . getNiceDate(strtotime($achDateModified)) . "</span>";
                echo "</td>";
            }

            echo "</tr>";
        }

        echo "</tbody></table></div>";
        echo "</div>";

        echo "<div class='float-right row'>";
        if ($offset > 0) {
            $prevOffset = $offset - $maxCount;
            echo "<a href='/achievementList.php?s=$sortBy&o=$prevOffset&p=$params$dev_param'>&lt; Previous $maxCount</a> - ";
        }
        if ($achCount == $maxCount) {
            // Max number fetched, i.e. there are more. Can goto next 25.
            $nextOffset = $offset + $maxCount;
            echo "<a href='/achievementList.php?s=$sortBy&o=$nextOffset&p=$params$dev_param'>Next $maxCount &gt;</a>";
        }
        echo "</div>";
        ?>
        <br>
    </div>
</div>
<?php RenderContentEnd(); ?>
