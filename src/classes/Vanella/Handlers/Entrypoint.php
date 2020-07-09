<?php
namespace Vanella\Handlers;

/**
 * This class serves as the entry point of your application.
 */
class Entrypoint
{

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
        'run_child_method',
    ];
    protected $dbConfig = [];
    protected $mainConfig = [];
    protected $restConfig = [];
    protected $request = [];

    public function __construct($args = [])
    {

        if (empty($args['config'])) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'Looks like your config files are missing. Please run command [php vanella create:config all] in your project root folder.',
            ]);
        }

        $this->endpointGroup = $args['endpointGroup'];
        $this->endpoint = $args['endpoint'];
        $this->mainConfig = $args['config']['main'];
        $this->dbConfig = $args['config']['database'][$this->mainConfig['active_env']];
        $this->restConfig = $args['config']['restful'];

        // Load request data
        $this->requestData();
    }

    /**
     * Get the request data
     */
    protected function requestData()
    {
        parse_str(file_get_contents('php://input'), $this->request);
    }

    /**
     * Must pass either GET,POST,PUT,PATCH,DELETE,HEAD
     * in the $accessType methods
     *
     * @param mixed $accessType
     *
     * @return void
     */
    protected function allowAccess($accessType)
    {
        if (is_array($accessType)) {
            if (!in_array($_SERVER['REQUEST_METHOD'], $accessType)) {
                Helpers::renderAsJson([
                    'success' => false,
                    'message' => 'Only [' . implode(' | ', $accessType) . '] methods are allowed!',
                ]);
            }
        } else {
            if ($_SERVER['REQUEST_METHOD'] != $accessType) {
                Helpers::renderAsJson([
                    'success' => false,
                    'message' => 'Only ' . $accessType . ' methods are allowed!',
                ]);
            }
        }
    }
}
