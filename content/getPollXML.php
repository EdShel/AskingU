<?php

require_once "classes/DbAccess.php";
require_once "classes/Poll.php";
require_once "classes/User.php";

// No id
if (!isset($_GET['id'])){
    // Bad request
    echo "No id parameter is given.";
    die;
}

$pollId = $_GET['id'];

// Open db connection if none
if (!isset($db)){
    $db = new DbAccess();
}

// Get this poll
$stmt = $db->PrepareStatement("SELECT * FROM polls WHERE Id = :id");
$stmt->bindParam(":id", $pollId);
$stmt->execute();
$poll = Poll::FromDb($db, $stmt->fetch(PDO::FETCH_ASSOC), User::GetUserIdFromCookies(), true);

// Create space-decorated DOM
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

// Create the root element (poll) with attribute "question"
$pollElement = $doc->createElement("poll");
$pollElement = $doc->appendChild($pollElement);
$doc->documentElement->setAttribute("question", $poll->Question);

// Append to it its children (variants).
foreach ($poll->Variants as $i => $variant) {

    $variantEl = $doc->createElement("variant");
    $variantEl->nodeValue = $variant->Value;
    $pollElement->appendChild($variantEl);
}

// Output XML string
echo $doc->saveXML();