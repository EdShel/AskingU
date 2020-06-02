<?php
require_once 'classes/MVC/Controller.php';
require_once 'classes/ErrorHandler.php';

if (ErrorHandler::GetErrorsCount() == 0){
    ErrorHandler::AddError("Неизвестная ошибка!");
}

Controller::View("main.php");