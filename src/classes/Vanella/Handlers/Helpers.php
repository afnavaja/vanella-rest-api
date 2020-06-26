<?php

namespace Vanella\Handlers;

class Helpers
{  
    /**
     * Used for dumping stuff
     *
     */
    final public static function debug($data = [], $asJson = false)
    {
        if ($asJson) {
            header('Content-Type: application/json');      
            echo json_encode($data, JSON_PRETTY_PRINT);
        } else  {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
        
    }

    /**
     * Render page as json
     */
    final public static function renderAsJson($response, $responseCode = null, $allowedMethods='*', $allowedOrigin='*')
    {   
        if (!empty($responseCode)) {
            http_response_code($responseCode);
        }

        header('Access-Control-Allow-Origin: '.$allowedOrigin);
        header('Access-Control-Allow-Methods: '.$allowedMethods);
        header('Content-Type: application/json');      
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Call a child method from a parent class
     */
    final public static function run_child_method($class, $nameOfChildMethod, $args = []){
        if(method_exists($class, $nameOfChildMethod)){
            $class->$nameOfChildMethod($args);
        }
    }
}
