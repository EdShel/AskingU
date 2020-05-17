<?php

// Basic class for self-constructing objects
// that can be translated into an HTML & JS code.
abstract class Component
{
    // An array of JS code to be written in the document
    private static array $jsInitialization = array();

    // Basic constructor with the ability to add required JavaScript code
    public function __construct(string $js = NULL)
    {
        // If a child wants to add its JS
        if (isset($js)) {
            // Add it if it hasn't been inserted yet
            if (!in_array($js, self::$jsInitialization)) {
                self::$jsInitialization[] = $js;
            }
        }
    }

    // Writes down all the JS code needed by the children
    public static function InitializeJS(): void
    {
        foreach (self::$jsInitialization as $i => $js) {
            echo "<script lang='js'>$js</script>";
        }
    }

    // This method must return HTML code of the element
    public abstract function GetHTML(): string;

    // Convert object to string (simply returns HTML code)
    public function __toString()
    {
        return $this->GetHTML();
    }

}



