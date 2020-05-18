<?php

require_once "classes/ErrorHandler.php";

define("maxVariants", 8);
define("minVariants", 2);


if (isset($_POST['submit'])) {
    $question = $_POST['question'];

    if (trim($question) === "") {
        ErrorHandler::AddError("Вопрос не может быть пустым!");
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
        ErrorHandler::AddError(
            "Для создания опроса необходимо как минимум " . minVariants . " вариантов");
    } else {

        require_once "classes/DbAccess.php";
        require_once "classes/Poll.php";
        require_once "classes/Variant.php";

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

        $poll->ToDB($db);

        // Create variants
        for ($i = 0, $c = count($variants); $i < $c; $i++) {
            $variant = new Variant($poll->Id, $i, $variants[$i]);
            $variant->ToDB($db);
        }
    }

    // Go to the main page

    if (ErrorHandler::GetErrorsCount() === 0) {
        header("Location: main");
    } else {
        session_start();
        $_SESSION["errorMessages"] = ErrorHandler::$Errors;
        header("Location: main/error");
    }
    exit;
}

include $_SERVER["DOCUMENT_ROOT"] . "/view/createPoll.php";
