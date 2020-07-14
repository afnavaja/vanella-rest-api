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
        } else {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }

    }

    /**
     * Render page as json
     */
    final public static function renderAsJson($response, $responseCode = null, $allowedMethods = '*', $allowedOrigin = '*')
    {
        if (!empty($responseCode)) {
            http_response_code($responseCode);
        }

        header('Access-Control-Allow-Origin: ' . $allowedOrigin);
        header('Access-Control-Allow-Methods: ' . $allowedMethods);
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Call a child method from a parent class
     */
    final public static function run_child_method($class, $nameOfChildMethod, $args = [])
    {
        if (method_exists($class, $nameOfChildMethod)) {
            $class->$nameOfChildMethod($args);
        }
    }

    /**
     * Loads the config file.
     * Config file must only return something.
     *
     * @param string $filePath
     * @param string $name
     *
     * @return array
     */
    final public static function loadConfig($filePath, $name = null)
    {
        if (file_exists($filePath)) {
            return !empty($name) ? [$name => require_once $filePath] : require_once $filePath;
        }

        return [];
    }

    /**
     * Gets all the endpoint URL in you rest api
     * 
     * @return array
     */
    final public static function getEndpointsUrl($predefinedMethods)
    {
        $endpointUrl = [];
        $endpoints = Helpers::allEndpoints($predefinedMethods);
        if (!empty($endpoints)) {
            foreach ($endpoints as $item) {
                $endpointUrl[] = $item['endpointUrl'];
            }
        }

        return $endpointUrl;
    }

    /**
     * Lists all endpoints in your rest api
     *
     * @param array $predefinedMethods
     *
     * @return array
     */
    final public static function allEndpoints($predefinedMethods = [])
    {
        try {
            $newData = [];
            $allEndpointGroup = self::getAllEndpointGroup();
            if (!empty($allEndpointGroup)) {
                foreach ($allEndpointGroup as $className) {
                    $endpoints = self::getEndPoints($className, $predefinedMethods);
                    $newData = array_merge($newData, $endpoints);
                }
            }
            return $newData;
        } catch (\Exception $e) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gets all the endpoint class names
     *
     * @return array
     */
    final public static function getAllEndpointGroup()
    {
        try {
            $path = '../restful/';
            $allFiles = scandir($path); // The path for your endpointgroup
            $fields = array_diff($allFiles, array('.', '..'));
            $classFiles = array_filter($fields, function ($element) {
                return strpos($element, '.php');
            });
            $newData = [];

            if (!empty($classFiles)) {
                foreach ($classFiles as $className) {
                    $class = substr($className, 0, -4); // We will be removing the .php extension
                    $newData[] = $class;
                }
            }

            return $newData;
        } catch (\Exception $e) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gets all the endpoints
     *
     * @param string $childClass
     * @param array $predefinedMethods
     *
     * @return array
     */
    final public static function getEndPoints($childClass, $predefinedMethods = [])
    {
        try {
            $data = [];
            $class = new \ReflectionClass($childClass);
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

            $ctr = 0;
            foreach ($methods as $value) {
                if (!in_array($value->name, $predefinedMethods)) { // Only include those public
                    $data[$ctr]['endpointgroup'] = $childClass;
                    $data[$ctr]['endpoint'] = $value->name;
                    $data[$ctr]['endpointUrl'] = strtolower($childClass) . '/' . $value->name;
                    $ctr++;
                }
            }

            return $data;
        } catch (\Exception $e) {
            //throw $th;
        }
    }
}
