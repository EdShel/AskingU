<?php
require_once "classes/ErrorHandler.php";
require_once "model/Poll.php";
require_once "model/Variant.php";

if (isset($_POST['pollXML'])) {
    // Get xml string of poll
    $xml = $_POST['pollXML'];
    try{
        // Try to create the poll
        $poll = Poll::FromXML($xml);
        // Put question into new POST request
        $_POST['question'] = $poll->Question;
        // Put here its variants
        foreach ($poll->Variants as $i => $variant){
            $_POST["variant{$i}"] = $variant->Value;
        }
    }
    catch (Exception $e){
        // If can't create the poll,
        // add an error
        ErrorHandler::AddError($e->getMessage() . PHP_EOL . $xml);
    }
} else {
    ErrorHandler::AddError("Необходимо указать XML");
}
// Redirect to the default poll creator
include "createPoll.php";