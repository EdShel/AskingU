<?php

class Controller
{
    public static $Model = NULL;

    public static function View(string $view, $model = NULL): void
    {
        self::$Model = $model;
        $content = $_SERVER['DOCUMENT_ROOT'] . '/view/' . $view;
        include $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    }

    public static function RedirectTo(string $action){
        header('Location: ' . $action);
    }

    public static function RedirectBack(): void
    {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}