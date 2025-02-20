<?php

use RA\ArticleType;
use RA\Permissions;
use RA\SubscriptionSubjectType;

function RenderCommentsComponent(
    ?string $user,
    int $numComments,
    array $commentData,
    int $articleID,
    int $articleTypeID,
    int $permissions,
    int $count = 20,
    int $offset = 0,
    bool $embedded = true
): void {
    $userID = getUserIDFromUser($user);

    echo "<div class='commentscomponent'>";

    echo "<div class='flex justify-between items-center mb-3'>";
    echo "<div>";
    if ($numComments == 0) {
        echo "<i>No comments</i>";
    } elseif (!$embedded) {
        if ($numComments > $count) {
            RenderPaginator($numComments, $count, $offset, "/comments.php?t=$articleTypeID&i=$articleID&o=");
        }
    } elseif ($numComments > count($commentData)) {
        echo "Recent comments: <span class='smalltext'>(<a href='/comments.php?t=$articleTypeID&i=$articleID'>All $numComments</a>)</span>";
    } else {
        echo "Comments:";
    }
    echo "</div>";
    if (isset($user)) {
        $subjectType = SubscriptionSubjectType::fromArticleType($articleTypeID);
        if ($subjectType !== null) {
            $isSubscribed = isUserSubscribedToArticleComments($articleTypeID, $articleID, $userID);
            echo "<div>";
            RenderUpdateSubscriptionForm("updatesubscription", $subjectType, $articleID, $isSubscribed, $embedded ? 'comments' : null);
            echo "</div>";
        }
    }
    echo "</div>";

    echo "<table id='feed'><tbody>";

    $lastID = 0;
    $lastKnownDate = 'Init';

    foreach ($commentData as $comment) {
        $nextTime = $comment['Submitted'];

        $dow = date("d/m", $nextTime);
        if ($lastKnownDate == 'Init') {
            $lastKnownDate = $dow;
        } elseif ($lastKnownDate !== $dow) {
            $lastKnownDate = $dow;
        }

        if ($lastID != $comment['ID']) {
            $lastID = $comment['ID'];
        }

        $canDeleteComment = $articleTypeID == ArticleType::User && $userID == $articleID || $permissions >= Permissions::Admin;

        RenderArticleComment(
            $articleID,
            $comment['User'],
            $comment['CommentPayload'],
            $comment['Submitted'],
            $user,
            $articleTypeID,
            $comment['ID'],
            $canDeleteComment
        );
    }

    if (isset($user)) {
        // User comment input:
        RenderCommentInputRow($user, $articleTypeID, $articleID);
    }

    echo "</tbody></table>";

    echo "</div>";
}

function RenderArticleComment(
    $articleID,
    $user,
    $comment,
    $submittedDate,
    $localUser,
    $articleTypeID,
    $commentID,
    $allowDelete
): void {
    $class = '';
    $deleteIcon = '';

    if ($user && $user == $localUser || $allowDelete) {
        $img = "<img src='" . asset('assets/images/icon/cross.png') . "' width='16' height='16' alt='delete comment'/>";
        $deleteIcon = "<div style='float: right;'><a onclick=\"removeComment($articleTypeID, $articleID, $commentID); return false;\" href='#'>$img</a></div>";
    }

    if ($user === 'Server') {
        $deleteIcon = null;
        $class .= ' system';
    }

    $artCommentID = "artcomment_{$articleTypeID}_{$articleID}_{$commentID}";
    echo "<tr class='comment$class' id='$artCommentID'>";

    $niceDate = date("j M Y ", $submittedDate);
    $niceDate .= date("H:i", $submittedDate);

    sanitize_outputs($user, $comment);
    $comment = nl2br($comment);

    echo "<td class='align-top py-2'>";
    if ($user !== 'Server') {
        echo userAvatar($user, label: false);
    }
    echo "</td>";
    echo "<td class='w-full py-2' colspan='3'>";
    echo $deleteIcon;
    echo "<div>";
    if ($user !== 'Server') {
        echo userAvatar($user, label: true);
    }
    echo " <span class='smalldate'>$niceDate</span>";
    echo "</div>";

    echo "<div style='word-break: break-word'>";
    echo $comment;
    echo "</div>";
    echo "</td>";

    echo "</tr>";
}

function RenderCommentInputRow($user, $articleTypeId, $articleId): void
{
    sanitize_outputs($user, $formStr);
    $commentId = "art_{$articleTypeId}_{$articleId}";
    $submitImageUrl = asset('assets/images/icon/submit.png');
    $loadingImageUrl = asset('assets/images/icon/loading.gif');
    $csrfField = csrf_field();
    $avatar = media_asset("/UserPic/$user.png");

    echo <<<EOL
        <tr id="comment_$commentId">
            <td class='align-top pb-3'>
                <img alt="$user" title="$user" class="badgeimg" src="$avatar" width="32" height="32">
            </td>
            <td class='w-full pb-3' colspan="3">
                <form action="/request/comment/create.php" method="post">
                    $csrfField
                    <input type="hidden" name="commentable_id" value="$articleId">
                    <input type="hidden" name="commentable_type" value="$articleTypeId">
                    <div class="flex align-center mb-1">
                        <textarea
                            class="comment-textarea"
                            name="body"
                            maxlength="2000"
                            placeholder="Enter a comment here..."
                            id="comment_textarea_$commentId"
                        ></textarea>
                        <button class="comment-submit-button">
                            <img src="$submitImageUrl" alt="Submit">
                        </button>
                        <span class="comment-loading-indicator">
                            <img src="$loadingImageUrl" alt="Loading">
                        </span>
                    </div>
                    <div class="textarea-counter" data-textarea-id="comment_textarea_$commentId"></div>
                    <div class="text-danger hidden"></div>
                </form>
            </td>
        </tr>
    EOL;
}
