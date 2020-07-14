<?php

namespace Vanella\Handlers;

class Authorization
{
    protected $args = [];

    public function __construct($args)
    {
        $this->args = $args;
        $this->handle();
    }

    /**
     * Handles the Authorization process
     *
     * @param array $args
     *
     */
    protected function handle()
    {
        try {
            if (empty($this->args['validatedUser'])) {
                Helpers::renderAsJson([
                    'success' => false,
                    'error' => 'Request token must be expired since we cannot validate the user',
                ], 500);
            }
            $currenEndpoint = $this->_currentEndpointUrl();

            $path = '../restful/Authorization/authorization.php';

            if (!file_exists($path)) {
                Helpers::renderAsJson([
                    'success' => false,
                    'error' => 'Looks like you did not set an authorization rule for each "' . $this->args['validatedUser']['role'] . '" role in your rest api. Please run [php vanella add:authorization]',
                ], 500);
            }

            $authorizedResourcesEndpoints = require_once $path;
            
            if (!in_array($currenEndpoint, $authorizedResourcesEndpoints[$this->args['validatedUser']['role']])) {
                Helpers::renderAsJson([
                    'success' => false,
                    'message' => 'You are not allowed to access this resource.',
                ], 403);
            }

            return true;
        } catch (\Exception $e) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Gets the current endpoint
     *
     * @return string
     */
    private function _currentEndpointUrl()
    {
        $currenEndpointGroup = $this->args['endpointGroup'];
        $currentEndpoint = $this->args['endpoint'];

        return strtolower($currenEndpointGroup) . '/' . $currentEndpoint;
    }

}
