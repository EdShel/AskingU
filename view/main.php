<div class="jumbotron jumbotron-fluid logo">
    <h1>AskingU</h1>
    <p>спроси любого, ответь другому</p>
</div>


<div class="container">

    <div class="row">
        <?php

        require_once "./classes/Poll.php";
        require_once "./classes/Variant.php";
        require_once "./classes/PollComponent.php";
        require_once "./classes/ErrorHandler.php";

        // Find user's id
        if (isset($user)) {
            $userId = $user->Id;
        } else {
            $userId = User::GetUserIdFromCookies();
        }

        //        $db->SQLTransaction(<<<SQL
        //        INSERT INTO polls(name) VALUES('Error');
        //        INSERT INTO var(name) VALUES('Error');
        //
        //SQL);

        try {
            // Get polls to display
            $pollsIds = $db->SQLMultiple("SELECT * FROM polls");

            // If found none
            if ($pollsIds === NULL){
                throw new Exception();
            }

            // Go through all the polls
            while ($pollArray = $pollsIds->fetch(SQLITE3_ASSOC)) {
                if ($pollArray == NULL){
                    ErrorHandler::AddError("Невозможно получить опрос!");
                    break;
                }
                // And display polls
                echo new PollComponent(Poll::FromDb($db, $pollArray, $userId, true));
            }
        } catch (Exception $ex) {
            ErrorHandler::AddError("Не получилось отобразить опросы!");
        }
        ?>

    </div>

</div>