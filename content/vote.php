<?php
if (isset($_POST['pollId']) && isset($_POST['variantId'])) {
    require_once "./classes/User.php";
    require_once "./classes/DbAccess.php";

    if (!isset($db)) {
        $db = new DbAccess();
    }
    $user = User::FromCookies($db);
    if ($user != NULL) {
        $pollId = $_POST['pollId'];
        $variantId = $_POST['variantId'];

        // Try to find user's previous answer

        $prevAnswer = $db->SQLSingle(<<<SQL
            SELECT variantId FROM votes 
            WHERE voterId = {$user->Id} AND pollId = {$pollId}
            LIMIT 1;
        SQL, false);

        // If the user hasn't answered yet
        if ($prevAnswer == NULL) {
            $db->SQLSingle(<<<SQL
            INSERT INTO votes(voterId, pollId, variantId)
            VALUES({$user->Id}, {$pollId}, {$variantId});
            SQL, false);

        } else {

            // Edit vote entry
            $db->SQLSingle(<<<SQL
            UPDATE votes SET variantId = {$variantId}
            WHERE voterId = {$user->Id} AND pollId = {$pollId};
            SQL, false);
            echo "var id $variantId user $user->Id  poll $pollId;";

        }
    }
}

if (ErrorHandler::GetErrorsCount() === 0){
    header("Location: main");
}
else{
    session_start();
    $_SESSION["errorMessages"] = ErrorHandler::$Errors;
    header("Location: main/error");
}
exit;