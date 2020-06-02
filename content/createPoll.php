<?php

require_once "classes/ErrorHandler.php";
require_once "classes/DbAccess.php";
require_once "classes/Poll.php";
require_once "classes/Variant.php";

define("maxVariants", 8);
define("minVariants", 2);

echo ("Checking for submit");
if (isset($_POST['submit'])) {

    $question = trim($_POST['question']);

    echo ("submit is found, question: $question");

    if (!Poll::IsQuestionCorrect($question)) {
        echo ("Вопрос не может быть пустым или содержать телефонный номер!");
    }

    $variants = array();

    // Go though all the variants
    for ($i = 0; $i < maxVariants; ++$i) {

        // Get its value
        $rawVariant = $_POST["variant{$i}"];
        $fixedVariant = preg_replace("/\s+/", " ", $rawVariant, -1);

        // If it's not an empty string
        if ($fixedVariant !== "") {
            // Add it to the array of strings
            $variants[] = $fixedVariant;
        }
    }

    if (count($variants) < minVariants) {
        echo "Not enough vars \n";

        ErrorHandler::AddError(
            "Для создания опроса необходимо как минимум " . minVariants . " вариантов");
    }

    if (ErrorHandler::GetErrorsCount() == 0) {

        // Open db connection
        if (!isset($db)) {
            $db = new DbAccess();
        }

        $user = User::FromCookies($db);

        // Get blocking date
        $blockingTime = new DateTime('now');
        switch ($_POST["blockingTime"]) {
            case "hour":
                $blockingTime->add(new DateInterval("PT1H"));
                break;
            case "day":
                $blockingTime->add(new DateInterval("P1D"));
                break;
            case "week":
                $blockingTime->add(new DateInterval("P1W"));
                break;
            case "month":
                $blockingTime->add(new DateInterval("P1M"));
                break;
            default:
                $blockingTime = NULL;
        }

        // Create a poll
        $poll = new Poll(-1,
            $user->Id,
            $question,
            isset($_POST["isPublic"]),
            NULL,
            $blockingTime,
            0,
            isset($_POST["shuffle"]));


        try {
            // Start transaction
            $db->BeginTransaction();

            // Insert Poll to the DB
            $poll->ToDB($db);

            // Prepare statement for inserting poll's variants
            $stmt = $db->PrepareStatement(
                "INSERT INTO variants(pollId, id, value) VALUES(:PollId, :Id, :Value);");

            $stmt->bindParam(":PollId", $poll->Id, PDO::PARAM_INT);
            $stmt->bindParam(":Id", $i, PDO::PARAM_INT);
            $stmt->bindParam(":Value", $text, PDO::PARAM_STR);

            // Create and insert variants
            for ($i = 0, $c = count($variants); $i < $c; $i++) {
                $text = $variants[$i];
                $stmt->execute();
            }

            // Commit changes
            $db->Commit();
        } catch (Exception $ex) {
            // If an error, rollback
            $db->Rollback();
        }
    }
    else{
        print_r(ErrorHandler::$Errors);
    }

    // Go to the main page

    if (ErrorHandler::GetErrorsCount() === 0) {
        ErrorHandler::AddError("redirecting to main");
        header("Location: main");
    } else {
        session_start();
        ErrorHandler::AddError("redirecting to main/error");
        $_SESSION["errorMessages"] = ErrorHandler::$Errors;
        header("Location: main/error");
    }
    exit;
}

require_once "classes/MVC/Controller.php";

Controller::View("createPoll.php");
