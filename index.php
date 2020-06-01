<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css"
          integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ"
          crossorigin="anonymous">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="../js/script.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <title>AskingU - Опросы и статистика</title>
</head>
<body>
<?php

// Import classes
require_once "classes/DbAccess.php";
require_once "classes/User.php";
require_once "classes/Component.php";
require_once "classes/ErrorHandler.php";
require_once "classes/PageRouter.php";

// Create db connection
$db = new DbAccess();

// Identify user from cookies value
if (isset($db)) {
    $user = User::FromCookies($db);
}

// If the user is not authorised, create dialog windows
if (!isset($user)) {
    include "content/register.php";
    include "content/login.php";
}

// Try to get errors from the session (when redirected)
if (end(Routing\PageRouter::$PathParams) === 'error')
{
    session_start();

    // If there are some save error messages
    if (isset($_SESSION["errorMessages"])){

        // Add new errors to the current list
        ErrorHandler::$Errors = array_merge(ErrorHandler::$Errors, $_SESSION["errorMessages"]);
    }

    // Delete save errors as they are being displayed now
    unset($_SESSION["errorMessages"]);
}


?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="/main">AskingU</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse"
            data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <?php

            // If user is authorised

            if (isset($user)) {
                echo <<<HTML
                    <li class="nav-item active">
                        <a class="nav-link" href="/createPoll">Создать опрос</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user">{$user->Name}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">Выйти</a>
                    </li>
                HTML;
            } else {
                // If user is not authorised

                echo <<<HTML
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="modal" 
                        data-target="#loginModal">Войти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="modal" 
                        data-target="#registerModal">Создать аккаунт</a>
                    </li>
                HTML;

            }
            ?>
        </ul>
    </div>
</nav>

<?php
    if (isset($content)){
        include $content;
    }
?>


<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>

<?php
    Component::InitializeJS();

    // If there are some errors
    if (ErrorHandler::GetErrorsCount() > 0){
        // Display them
        echo ErrorHandler::GetHTML();
        echo <<<HTML
<script>
        $('#errorModal').modal('show');
</script>
HTML;
    }
?>

</body>
</html>