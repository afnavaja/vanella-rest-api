<?php

namespace Vanella\Handlers;

use Firebase\JWT\JWT;
use Vanella\Core\Url;
use Vanella\Handlers\Entrypoint;

class Authentication extends Entrypoint
{
    protected $isAuthActivated;
    protected $authEndpointList;
    protected $authConfig = [];
    protected $isAuthenticationSuccessful;
    protected $customAuthenticationMethod;
    protected $enableCustomAuthentication = false;
    protected $authStatusResponse;
    protected $isAuthStatusResponseDisplayed = false;
    protected $isAuthInDebugMode = false;
    protected $accessToken;

    /**
     * Constructor
     *
     * @param string $args
     */
    public function __construct($args = [])
    {
        parent::__construct($args);
        $this->_loadDefaultAuthConfig($args);
    }

    /**
     * Load all config for the authentication
     *
     * @param string $args
     *
     * @return void
     */
    private function _loadDefaultAuthConfig($args = [])
    {

        // Initally load all all auth configs found in config/authentication.php
        $this->authConfig['default'] = isset($args['config']['authentication']) ? $args['config']['authentication'] : [];

        // Declare the access_rule as empty at first. This line here will determine
        // if the certain resource or endpoint can be access
        $this->authConfig['access_rule'] = [];
        Helpers::run_child_method($this, 'accessRule');

        // Turn this on if you want authentication on this endpoint group.
        $this->isAuthActivated = $this->authConfig['default']['isAuthActivated'];

        // Set to true if you want more detailed json response for the authentication handler
        $this->isAuthInDebugMode = $this->authConfig['default']['isAuthInDebugMode'];

        // Turn this on to enable custom authentication
        $this->enableCustomAuthentication = $this->authConfig['default']['enableCustomAuthentication'];

        // This is to override Authentication config class from the child class
        Helpers::run_child_method($this, 'defaultConfig');

        if ($this->isAuthActivated) {
            // Need to get the current Class and Function
            $this->authenticate();
        }
    }

    /**
     * Run this default authentication handler
     * to check if the authentication is applied.
     * This is only the default authentication.
     *
     * @return void
     */
    protected function authenticate()
    {

        $authStatusResponse = [];

        // If auth config is missing
        if (!$this->authConfig['access_rule']) {
            $authStatusResponse = array_merge([
                'message' => 'Please specify endpoint names to be authenticated. You set the isAuthactivated to true, therefore this API framework requires you to specify the access rule for each endpoints. See the endpoint list below.',
            ], $authStatusResponse);
            $this->isAuthenticationSuccessful = true;
        }

        // If AuthConfig is set
        if ($this->authConfig) {
            // If the current endpoint access rule is not specified
            if (!isset($this->authConfig['access_rule'][$this->endpoint])) {

                $baseUrl = Url::baseUrl();
                $endpointGroup = $this->endpointGroup;
                $endpointUrl = $baseUrl . $endpointGroup . '/' . $this->endpoint;

                $authStatusResponse = array_merge([
                    'message' => 'The ' . $endpointUrl . ' endpoint does not have an access rule. Please add the access rule on this endpoint.',
                ], $authStatusResponse);

                $this->isAuthenticationSuccessful = false;
            } else { // If the curent enpoint access rule is set

                // If the isAccessPageViaAccessToken is false
                if (isset($this->authConfig['access_rule'][$this->endpoint]['isAccessPageViaAccessToken'])) {

                    // If the endpoint can be access without having the need of an Access Token
                    if (!$this->authConfig['access_rule'][$this->endpoint]['isAccessPageViaAccessToken']) {
                        $authStatusResponse = array_merge([
                            'message' => 'The ' . $this->endpoint . ' endpoint is allowed to be access without an access token.',
                        ], $authStatusResponse);
                        $this->isAuthenticationSuccessful = true;
                    } else { //
                        $authStatusResponse = array_merge([
                            'message' => 'You are not allowed to access this resource',
                        ], $authStatusResponse);
                        $this->isAuthenticationSuccessful = false;
                    }

                }

            }
        }

        // Built in process for the access_token.
        // Read the access_token when not in Auth endpoint group
        if ($this->endpointGroup !== 'Auth') {
            $authStatusResponse = array_merge($this->_processHeaderAuthorization(), $authStatusResponse);
        }

        // Run your custom authentication here
        if ($this->enableCustomAuthentication) {
            Helpers::run_child_method($this, 'customAuthentication');
        }

        // Assign the auth response status
        $authStatusResponse = $this->isAuthInDebugMode ? array_merge($authStatusResponse, $this->_setAuthenticationStatus()) : $authStatusResponse;

        // If authentication failed. Display this set of message.
        if (!$this->isAuthenticationSuccessful) {
            Helpers::renderAsJson($authStatusResponse);
        }

        // Set the authStatusResponse for this parent class
        $this->authStatusResponse = $authStatusResponse;
    }

    protected function _processHeaderAuthorization($type = 'jwt')
    {
        // Get all headers that are sent
        $header = apache_request_headers();
        $response = [];

        switch ($type) {
            case 'jwt':
            default:
                if (isset($header['Authorization'])) {
                    $token = explode(' ', $header['Authorization']);

                    if (isset($token[0]) == 'Bearer' && isset($token[1])) {
                        $this->accessToken = $token[1];
                        $extractedAuthConfig = $this->_extractAuthConfig();

                        try {
                            $jwtDecoded = JWT::decode($this->accessToken, $extractedAuthConfig['authConfig']['secretKey'], [$extractedAuthConfig['authConfig']['algo']]);
                            $response = [
                                'success' => true,
                                'jwtDecoded' => $jwtDecoded,
                            ];

                            $this->isAuthenticationSuccessful = true;

                        } catch (\Exception $e) {
                            $response = [
                                'success' => false,
                                'error' => $e->getMessage(),
                            ];

                            $this->isAuthenticationSuccessful = false;
                        }
                    }
                }
                break;
        }

        return $response;
    }

    /**
     * Register endpoint to access rule.
     * You only do this when you set the isAuthActivated to true.
     * This will determine if you can access the resource/endpoint.
     *
     * @param string $endpointName
     * @param string $args
     *
     * @return $this
     */
    protected function _registerEndpointToAccessRule($endpointName, $args = [])
    {
        if (isset($endpointName) && $args) {
            $this->authConfig['access_rule'] = array_merge($this->authConfig['access_rule'], [
                $endpointName => $args,
            ]);
        }

        return $this;
    }

    /**
     * Extract the Auth config
     *
     * @param $requestClientId
     *
     * @return array
     */
    protected function _extractAuthConfig($requestClientId = null)
    {
        $app = [];
        $authConfig = [];

        if (isset($requestClientId)) {
            // Load the app client info from config/authentication.php
            $app = $this->_getActiveConfig(
                $requestClientId,
                $this->authConfig['default']['authenticatedApps'],
                'clientId'
            );
        }

        // Load the auth config from config/authentication.php
        $authConfig = $this->_getActiveConfig(
            $this->authConfig['default']['activeAuthName'],
            $this->authConfig['default']['authList'],
            'name'
        );

        return [
            'app' => $app,
            'authConfig' => $authConfig,
        ];
    }

    /**
     * Validates the equality of the two strings
     *
     * @param string $var1
     * @param string @var2
     *
     * @return boolean
     */
    protected function _validateEquality($var1, $var2)
    {
        return $var1 === $var2 ? true : false;
    }

    /**
     * Loads the configuration array
     * based on the specified keyIdentifier
     *
     * @param string $clientId
     * @param array $authenticatedApps
     * @param string $keyIdentifier
     *
     * @return array
     */
    protected function _getActiveConfig($clientId, $arrayList = [], $keyIdentifier)
    {
        if (!empty($arrayList)) {
            foreach ($arrayList as $items) {
                return $items[$keyIdentifier] == $clientId ? $items : false;
            }
        }

        return false;
    }

    /**
     * Set the current authentication status
     *
     * @return array
     */
    private function _setAuthenticationStatus()
    {
        return [
            'isAuthActivated' => $this->isAuthActivated,
            'authConfig' => $this->authConfig,
            'endpointGroup' => $this->endpointGroup,
            'endpoint' => $this->endpoint,
            'isAuthenticationSuccessful' => $this->isAuthenticationSuccessful,
        ];
    }
}
