<?php
require_once "./classes/User.php";
if (isset($_POST['submit'])) {

    $errors = array();

    require_once "classes/DbAccess.php";

    if (!isset($db)) {
        $db = new DbAccess();
    }

    if (count($errors) == 0) {

        $email = trim($_POST['email']);
        if (!User::IsEmailCorrect($email)) {
            $errors[] = "Неверный адрес электронной почты!";
        } else if (User::IsUserRegistered($db, $email)) {
            $errors[] = "Пользователь уже зарегистрирован!";
        }
    }


    if (count($errors) == 0) {
        $user = new User();
        $user->Name = trim($_POST['name']);
        $user->Email = $email;
        $user->Password = User::HashPassword($_POST['password']);
        $user->YearOfBirth = $_POST['year'];
        $user->Gender = $_POST['gender'];

        $user->RegisterUser($db);

        include "login.php";
        exit();
    } else {
        // Output all the errors

        echo "<div>";

        foreach ($errors as $i => $error) {
            echo <<<HTML
                <div>
                    {$error}
                </div>
            HTML;
        }

        echo "</div>";
    }
}

include $_SERVER["DOCUMENT_ROOT"] . "/view/register.php";