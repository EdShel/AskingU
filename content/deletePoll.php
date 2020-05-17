<?php

require_once "classes/DbAccess.php";
require_once "classes/Poll.php";

if (!isset($db)){
    $db = new DbAccess();
}

Poll::DeleteFromDb($db, $_POST["pollId"]);
if (ErrorHandler::GetErrorsCount() === 0){
    header("Location: main");
}
else{
    session_start();
    $_SESSION["errorMessages"] = ErrorHandler::$Errors;
    header("Location: main/error");
}
exit;