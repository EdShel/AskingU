<?php

require_once 'classes/MVC/Controller.php';
require_once "classes/DbAccess.php";
require_once "model/Poll.php";
require_once "classes/ErrorHandler.php";

// Find user's id
$userId = User::GetUserIdFromCookies();

// Set db connection
if (!isset($db)){
    $db = new DbAccess();
}

// Model to pass to the view
$model = array();

try {
    // Get polls to display
    $model = Poll::GetAllPolls($db, $userId);
} catch (Exception $ex) {
    ErrorHandler::AddError("Не получилось получить опросы! " . $ex->getMessage());
}


Controller::View("main.php", $model);
