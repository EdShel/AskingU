<?php

namespace Routing;
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else {
    require "classes/PageRouter.php";
    $router = new PageRouter();
    $contentSearchDir = __DIR__ . "\content";
    $controller = $router->GetContentFile($contentSearchDir, "main");

    if ($controller == NULL) {
        $controller = __DIR__ . "/content/error.php";
        $errorMsg = "Ошибка 404! Проверьте правильность запроса!";
    }

    include $controller;
}