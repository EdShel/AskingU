<?php

require_once "classes/ErrorHandler.php";
require_once "classes/DbAccess.php";
require_once "model/User.php";
require_once "model/Poll.php";
require_once "classes/MVC/Controller.php";
require_once "classes/PollComponent.php";

$userId = User::GetUserIdFromCookies();
if ($userId == -1){
    ErrorHandler::AddError("Вы должны быть авторизованы!");
}

if (!isset($db)){
    $db = new DbAccess();
}

// Get user
$user = User::FromUserId($db, $userId);

// Find out his polls
$polls = array();
$pollsStmt = $db->PrepareStatement('SELECT * FROM polls WHERE CreatorId = :user');
$pollsStmt->bindParam(':user', $userId);
$pollsStmt->execute();
while($pollsRes =  $pollsStmt->fetch(PDO::FETCH_ASSOC)){
    $polls[] = Poll::FromDb($db, $pollsRes, $userId, true);
}


// Pass the model to the view
$model = array(
    'user' => $user,
    'polls' => $polls
);

Controller::View("user.php", $model);