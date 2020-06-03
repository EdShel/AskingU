<?php

require_once "classes/ErrorHandler.php";
require_once "classes/DbAccess.php";
require_once "classes/Poll.php";
require_once "classes/User.php";
require_once "classes/MVC/Controller.php";

if (!isset($db)) {
    $db = new DbAccess();
}

$pollUrl = $_GET["id"];
$userId = User::GetUserIdFromCookies();
$pollResult = $db->SQLMultiple(
    "SELECT * FROM polls WHERE url = '$pollUrl'")->fetch(PDO::FETCH_LAZY);

if ($pollResult == NULL) {
    ErrorHandler::AddError("Такого опроса не существует!");
} else {
    $model = Poll::FromDb($db, $pollResult, $userId, true);

    // Delete this session in 15 minutes
    session_cache_expire(15);
    session_start();
    $pollSessionKey = "visit-$pollUrl";
    if (!isset($_SESSION[$pollSessionKey])){
        $db->SQLRun("UPDATE polls SET views = views + 1 WHERE Id = $model->Id;");
    }
    $_SESSION[$pollSessionKey] = true;
}


Controller::View("viewPoll.php", $model);