<?php

class Controller
{
    public static function View(string $view, $model = NULL): void
    {
        $content = $_SERVER['DOCUMENT_ROOT'] . '/view/' . $view;
        include $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    }

    public static function RedirectBack(): void{
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}