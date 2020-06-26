<?php
namespace Vanella\Handlers;

/**
 * This class serves as the entry point of your application. 
 */
class Entrypoint {
   
    protected $endpointGroup;
    protected $endpoint;
    protected $declaredPredefinedMethods = [
        '__construct',
        'accessRule',
        'endpoints',
        'customAuthentication',
        'defaultConfig',
        'debug',
        'renderAsJson',
        'run_child_method'
    ];
    protected $dbConfig = [];
    protected $mainConfig = [];
    protected $restConfig = [];

    public function __construct($args = [])
    {        
        $this->endpointGroup = $args['endpointGroup'];
        $this->endpoint =  $args['endpoint'];
        $this->mainConfig = $args['config']['main'];
        $this->dbConfig = $args['config']['database'][$this->mainConfig['active_env']];
        $this->restConfig = $args['config']['restful'];

    }
}