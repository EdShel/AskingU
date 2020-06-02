<?php
require_once "classes/ErrorHandler.php";
require_once "classes/Poll.php";
require_once "classes/Variant.php";

if (isset($_POST['pollXML'])) {
    $xml = $_POST['pollXML'];
    try{
        $poll = Poll::FromXML($xml);
        $_POST['question'] = $poll->Question;
        foreach ($poll->Variants as $i => $variant){
            $_POST["variant{$i}"] = $variant->Value;
        }
    }
    catch (Exception $e){
        ErrorHandler::AddError($e->getMessage() . PHP_EOL . $xml);
    }
} else {
    ErrorHandler::AddError("Необходимо указать XML");
}

include "createPoll.php";