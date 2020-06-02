<?php

namespace Routing;
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else {
    require "classes/PageRouter.php";
    $router = new PageRouter();
    $contentSearchDir = $_SERVER['DOCUMENT_ROOT'] . "\content";
    $controller = $router->GetContentFile($contentSearchDir, "main");

    if ($controller == NULL) {
        require_once "classes/ErrorHandler.php";

        \ErrorHandler::AddError("Ошибка 404! Проверьте правильность запроса! " . $_SERVER["REQUEST_URI"]);
        $controller = $_SERVER['DOCUMENT_ROOT'] . "/content/error.php";
    }

    include $controller;
}