<?php

namespace Vanella\Handlers;

use Vanella\Core\Controller;
use Vanella\Core\Url;

class Execution
{

    protected $_args;
    protected $url;

    /**
     * Default constructor
     */
    public function __construct($args = [])
    {   
        $this->_args = $args;
        $this->url = new Url();
        $this->execute();
    }

    /**
     * Executes the current api route
     */
    public function execute()
    {
        try {          
            $class = ucwords($this->url->segment(1)); // Set class name as the first url segment
            $function = $this->url->segment(2); // Set function name as the second url segment
            $segment3 = !empty($this->url->segment(3)) ? $this->url->segment(3) : null; // Either this could be an Id or a Page
            $segment4 = !empty($this->url->segment(4)) ? $this->url->segment(4) : null; // This would be the page id

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
                        'config' => $this->_args['config'],
                        'url' => $this->url
                    ]);                     
                    // Dynamically execute the function
                    if (!empty($function) && method_exists($class, $function)) {
                        $object->$function();
                    }
                    else {
                        Helpers::renderAsJson([
                            'success' => false,
                            'message' => 'The endpoint does not exist!'
                        ], 400);
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
