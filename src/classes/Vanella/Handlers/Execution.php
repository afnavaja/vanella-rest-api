<?php

namespace Vanella\Handlers;

use Vanella\Core\Controller;
use Vanella\Core\Url;

class Execution
{

    protected $_args;

    public function __construct($args = [])
    {   
        $this->_args = $args;
        $this->execute();
    }

    /**
     * Executes the current api route
     */
    public function execute()
    {
        try {
            $url = new Url(); // Get the current route for the api
            $class = ucwords($url->segment(1)); // Set class name as the first url segment
            $function = $url->segment(2); // Set function name as the second url segment
            $segment3 = !empty($url->segment(3)) ? $url->segment(3) : null; // Either this could be an Id or a Page
            $segment4 = !empty($url->segment(4)) ? $url->segment(4) : null; // This would be the page id

            $id = null;
            $pageNumber = null;

            if (!empty($segment3)) {
                $id = is_numeric($segment3) ? $segment3 : null; // Set the id
                $pageNumber = $segment3 == "page" && is_numeric($segment4) ? $segment4 : null; // Set the page number
            }

            if (!empty($class)) {

                if (class_exists($class)) {

                    // Dynamically instantiate a class
                    $object = new $class([
                        'id' => $id,
                        'pageNumber' => $pageNumber,
                        'endpointGroup' => $class,
                        'endpoint' => $function,
                        'isMethodExecuted' => !empty($function) ? true : false,
                        'config' => $this->_args['config']
                    ]);

                    // Dynamically execute the function
                    if (!empty($function)) {
                        $object->$function();
                    }
                } else {
                    Controller::render(__DIR__ . '/views/endpoint.notfound');
                }

            } else {
                new Documentation($this->_args);
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
