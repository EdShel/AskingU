<?php

class Controller
{
    /**
     * @var null Model passed to the view.
     */
    public static $Model = NULL;

    /**
     * Displays view file placed inside
     * index.php template.
     *
     * @param string $view View file inside view folder
     * @param null $model Model to pass to the view
     */
    public static function View(string $view, $model = NULL): void
    {
        self::$Model = $model;
        $content = $_SERVER['DOCUMENT_ROOT'] . '/view/' . $view;
        include $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    }

    /**
     * Redirects browser to another controller
     *
     * @param string $action Controller to redirect to.
     */
    public static function RedirectTo(string $action){
        header('Location: ' . $action);
    }

    /**
     * Redirects the user to the previous page
     */
    public static function RedirectBack(): void
    {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}