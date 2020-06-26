<?php

namespace Vanella\Core;

class Controller
{
    protected $_defaultViewPath;
    
    /**
     * Renders the view
     *
     * @param string $filename
     * @param array $vars
     * @param boolean $isPartial
     * @return mixed
     */
    public static function render($filename, array $vars = array(), $isPartial = false)
    {
        ob_start();
        extract($vars);

        $templateFile = $filename . '.php';
        require_once $templateFile;

        // Get contents with the necessary files
        $content = ob_get_clean();
        ob_flush();

        if ($isPartial) {
            return $content;
        } else {
            echo $content;
        }
    }
}
