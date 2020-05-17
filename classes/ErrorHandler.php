<?php


class ErrorHandler
{
    public static bool $Initialized = false;

    // All the saved errors
    public static array $Errors = array();

    /*
     * Add new error to the list
     */
    public static function AddError(string $msg) : void {
        // If there is no such error
        if (!in_array($msg, self::$Errors)){
            // Add it to the array
            self::$Errors[] = $msg;
        }
    }

    /*
     * Simply returns number of errors in the list
     */
    public static function GetErrorsCount() : int {
        return count(self::$Errors);
    }

    /*
     * Returns HTML code of the element to display errors
     * (it looks like a dialog window).
     */
    public static function GetHTML() : string {
        $res = <<<HTML
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Отчёт об ошибках</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
HTML;

        // Print error messages
        foreach (self::$Errors as $i => $msg){
            $msg = self::StringToHTML($msg);
            $res .= <<<HTML
<div class="alert alert-danger" role="alert">
  {$msg}
</div>
HTML;

        }

        $res .= <<<HTML
            </div>
        </div>
    </div>
</div>
HTML;

        return $res;
    }

    private static function StringToHTML(string $source){

        // Get all the symbols of the line, e.g.
        // "this is \n an example"
        // will match "this is " & " an example"
        $regEx = "/^(.+)$/m";

        // Put each line into a paragraph <p> ... </p>
        return preg_replace($regEx, "<p>\\1</p>", $source);
    }
}