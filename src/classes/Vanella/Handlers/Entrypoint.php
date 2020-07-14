<?php
namespace Vanella\Handlers;

use Vanella\Core\Url;

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
        'run_child_method',
        'validateUserCredentials',
        'validateClientApp',
        'getCurrentEndpointUrl',
    ];
    protected $dbConfig = [];
    protected $mainConfig = [];
    protected $restConfig = [];
    protected $request = [];
    protected $validators = [];
    protected $currentEndpointUrl;

    /**
     * The construct of this class
     */
    public function __construct($args = [])
    {
        // If config files are missing
        if (empty($args['config'])) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'Looks like your config files are missing. Please run command [php vanella initialize] in your project root folder.',
            ]);
        }

        // Set the config
        $this->_setConfig($args);

        // Load request data
        $this->requestData();

        // If endpoint does not exist
        if (!in_array($this->currentEndpointUrl, Helpers::getEndpointsUrl($this->declaredPredefinedMethods))) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'This endpoint does not exist.',
            ], 403);
        }
    }

    /**
     * Set the config
     *
     * @param array $args
     *
     * @return void
     */
    protected function _setConfig($args = [])
    {
        $this->endpointGroup = $args['endpointGroup'];
        $this->endpoint = $args['endpoint'];
        $this->mainConfig = $args['config']['main'];
        $this->dbConfig = $args['config']['database'][$this->mainConfig['active_env']];
        $this->restConfig = $args['config']['restful'];
        $this->validators = $args['config']['validators'];
        $this->currentEndpointUrl = $this->getCurrentEndpointUrl();
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

    /**
     * Returns the endpoint list of the child class
     *
     * @return array
     */
    protected function _endpointList($childClass)
    {
        $data = [];
        $class = new \ReflectionClass($childClass);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $ctr = 0;
        foreach ($methods as $value) {
            if (!in_array($value->name, $this->declaredPredefinedMethods)) { // Only include those public
                $data[$ctr]['name'] = $value->name;
                $data[$ctr]['url'] = Url::baseUrl() . strtolower($this->endpointGroup) . '/' . $value->name;
                $data[$ctr]['endpointgroup'] = $this->endpointGroup;
                $data[$ctr]['endpoint'] = $value->name;
                $data[$ctr]['endpointUrl'] = strtolower($this->endpointGroup) . '/' . $value->name;
                $ctr++;
            }
        }

        return $data;
    }

    /**
     * Gets the current endpoint
     *
     * @return string
     */
    protected function getCurrentEndpointUrl()
    {
        return strtolower($this->endpointGroup) . '/' . $this->endpoint;
    }
}
