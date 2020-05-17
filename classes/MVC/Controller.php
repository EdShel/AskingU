<?php


class Controller
{
    public static string $TemplateContent;

    public static function View(string $view): void
    {
        $view = $_SERVER["DOCUMENT_ROOT"] . '/content/' . $view;
        $content = file_get_contents($view);

        // If this view is without template
        if (preg_match("/<html>/", $content)) {
            include $view;
        } else {
            self::$TemplateContent = $view;
            include $_SERVER["DOCUMENT_ROOT"] . "/index.php";
        }
    }
}