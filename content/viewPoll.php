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
}


Controller::View("viewPoll.php", $model);