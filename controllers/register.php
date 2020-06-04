<?php
require_once "./model/User.php";
if (isset($_POST['submit'])) {

    require_once "classes/DbAccess.php";
    require_once "classes/ErrorHandler.php";

    if (!isset($db)) {
        $db = new DbAccess();
    }

    if (ErrorHandler::GetErrorsCount() == 0) {

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);

        if (!User::IsNameCorrect($name, $exception)){
            ErrorHandler::AddError("$exception $name");
        }
        if (!User::IsEmailCorrect($email)) {
            ErrorHandler::AddError("Неверный адрес электронной почты! $email");
        }
        if (User::IsUserRegistered($db, $email)) {
            ErrorHandler::AddError("Пользователь $email уже зарегистрирован!");
        }
    }


    if (ErrorHandler::GetErrorsCount() == 0) {
        $user = new User();
        $user->Name = $name;
        $user->Email = $email;
        $user->Password = User::HashPassword($_POST['password']);
        $user->YearOfBirth = $_POST['year'];
        $user->Gender = $_POST['gender'];

        $user->RegisterUser($db);

        include "login.php";
        exit();
    } else {
        // Output all the errors
        require_once "classes/MVC/Controller.php";

        unset($_POST['submit']);
        Controller::View("main.php");
        exit;
    }
}

include $_SERVER["DOCUMENT_ROOT"] . "/view/register.php";