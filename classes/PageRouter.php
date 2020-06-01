<?php

namespace Routing;

class PageRouter
{
    public static array $PathParams = array();

    function __construct()
    {
        // Retrieving query params from request URL
        $pathMatches = array();
        $requestReg = "~/(\w+)~";
        $foundPath = preg_match_all(
            $requestReg,
            $_SERVER["REQUEST_URI"],
            $pathMatches
        );

        foreach ($pathMatches[1] as $indx => $match) {
            self::$PathParams[] = $match;
        }
    }

    /***
     * @return string Path to the file-controller of the request
     */
    public function GetContentFile($dirToSearch, $pathIfNoParams = NULL)
    {
        // Check how many query parameters is there
        $paths = count(self::$PathParams);

        // If there are no parameters, use default content
        if ($paths === 0 && $pathIfNoParams != NULL){
            return $dirToSearch . "\\" . $pathIfNoParams . ".php";
        }

        // Go through all the path pieces
        for ($i = 0; $i < $paths; ++$i) {
            // If there is controller with .php extension
            $nextPathPiece = self::$PathParams[$i];
            $desiredFile = $dirToSearch . "\\" . $nextPathPiece . ".php";
            if (file_exists($desiredFile)) {
                // Run the controller
                return $desiredFile;
            }
            // There is no such controller, so try more
            $dirToSearch .= "\\" . $nextPathPiece;
        }

        return NULL;
    }
}