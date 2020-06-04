<?php

require_once "classes/ErrorHandler.php";
require_once "classes/DbAccess.php";
require_once "model/Poll.php";
require_once "model/User.php";
require_once "model/PollVisit.php";
require_once "classes/MVC/Controller.php";

// If has no db connection, create a new one
if (!isset($db)) {
    $db = new DbAccess();
}

// Id of the poll
$pollUrl = $_GET["id"];

// Try to identify the user
$userId = User::GetUserIdWithAccessTokenValidation($db);

// Get poll by id
$pollResult = Poll::GetQueryResultByURL($db, $pollUrl);

// If can't find the poll
if ($pollResult == NULL) {
    ErrorHandler::AddError("Такого опроса не существует!");
} else {
    $model = Poll::FromDb($db, $pollResult, $userId, true);

    // Delete this session in 15 minutes
    session_cache_expire(15);
    // Remember in session cache that the user has visited this page
    session_start();
    // Key for session variable
    $pollSessionKey = "visit-$pollUrl";
    // If the user hasn't visited the page yet
    if (!isset($_SESSION[$pollSessionKey])){
        // Increase poll's visits counter
        Poll::IncrementVisitsCount($db, $model->Id);

        // If this user is authorized
        if ($userId !== -1){
            // Increase unique users visit counter
            (new PollVisit($userId, $model->Id))->IncrementVisitCounter($db);
        }

        // Remember that the user has already visited this page
        $_SESSION[$pollSessionKey] = true;
    }
}

Controller::View("viewPoll.php", $model);