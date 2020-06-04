<?php

namespace Routing;
// If the user requests a resource (an image, JS script or stylesheet)
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    require "classes/PageRouter.php";
    // Create router
    $router = new PageRouter();
    // Specify in which folder all the controllers are located
    $contentSearchDir = $_SERVER['DOCUMENT_ROOT'] . "/controllers";
    // Try to find the controller file
    // (if the url is empty, go to the main page controller)
    $controller = $router->GetContentFile($contentSearchDir, "main");

    // No controller is found
    if ($controller == NULL) {
        require_once "classes/ErrorHandler.php";

        // Error 404
        \ErrorHandler::AddError("Ошибка 404! Проверьте правильность запроса! " . $_SERVER["REQUEST_URI"]);
        $controller = $_SERVER['DOCUMENT_ROOT'] . "/controllers/error.php";
    }

    // Now the controller rules
    include $controller;
}