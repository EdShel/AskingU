<?php
// router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else {
    require "classes/PageRouter.php";
    $router = new PageRouter();
    $contentSearchDir = __DIR__ . "\content";
    $content = $router->GetContentFile($contentSearchDir, "main");

    if ($content == NULL) {
        $content = __DIR__ . "/content/error.php";
        $errorMsg = "Ошибка 404! Проверьте правильность запроса!";
    }

    // Load main.php
    $controller = $content;

    if (!isset($_POST['submit'])) {
        include __DIR__ . "/index.php";
    } else {
        include $controller;
    }
}