<?php

require_once "model/Poll.php";
require_once "model/Like.php";
require_once "classes/DbAccess.php";
require_once "model/User.php";
require_once "classes/MVC/Controller.php";


if (isset($_POST['pollId'])) {

    $pollId = $_POST['pollId'];
    $userId = User::GetUserIdFromCookies();

    if (!isset($db))
    {
        $db = new DbAccess();
    }

    $hasLiked = 1 == $db->SQLSingle("SELECT COUNT(*) FROM likes WHERE pollId = $pollId AND userId = $userId;", FALSE);

    if ($hasLiked){
        $db->SQLRun("DELETE FROM likes WHERE userId = $userId AND pollId = $pollId;");
    }
    else{
        (new Like($userId, $pollId))->ToDb($db);
    }

}
Controller::RedirectBack();