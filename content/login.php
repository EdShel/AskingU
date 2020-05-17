<?php

if (isset($_POST['submit'])) {

    $errors = array();

    require_once "classes/DbAccess.php";

    if (!isset($db)) {
        $db = new DbAccess('polls.db');
    }

    $email = trim($_POST['email']);

    if (!User::IsEmailCorrect($email)) {
        $errors[] = "Неверная запись электронной почты!";
    }
    if (count($errors) == 0) {
        $password = $_POST['password'];

        $userQuery = $db->SQLSingle(<<<SQL
            SELECT * FROM users WHERE email = "{$email}" LIMIT 1;
        SQL, true);

        if (count($userQuery) == 0) {
            $errors[] = "Пользователь не зарегистрирован!";
        } else if (User::HashPassword($_POST['password']) !== $userQuery['password']) {
            $errors[] = "Пароль неверный!";
        }

        if (count($errors) == 0) {

            // Write into db access token
            $userId = $userQuery['id'];
            $accessToken = User::GenerateAccessToken();
            $db->SQLSingle(<<<SQL
                UPDATE users SET accessToken = "{$accessToken}" WHERE id = {$userId};
            SQL, false
            );

            $authDuration = time() + 60 * 60 * 24 * 30;

            // Put it into cookies user's Id
            setcookie("id", $userId, $authDuration);
            // And access token (as httpOnly)
            setcookie("accessToken", $accessToken,
                $authDuration, '/', null, null, true);

            // Go to the main page
            $redirected = true;
            header("Location: main");
            exit();
        }
    }

    if (count($errors) != 0) {
        echo '<div>';

        foreach ($errors as $i => $error) {
            echo "<div>$error</div>";
        }

        echo '</div>';
    }
}


?>

<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Вход</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="login" method="post">

                    <!-- User's email -->

                    <div class="form-group">
                        <label>Электронная почта</label>
                        <input type="email" name="email" placeholder="example@mail.com"
                               class="form-control" required>
                    </div>


                    <!-- User's password -->

                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" placeholder="******"
                               class="form-control" required>
                    </div>

                    <!-- Confirmation/cancellation buttons -->

                    <div>
                        <input type="submit" name="submit" value="Войти" class="btn btn-primary">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>