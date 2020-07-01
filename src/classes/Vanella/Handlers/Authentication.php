<?php

namespace Vanella\Handlers;

use Firebase\JWT\JWT;
use Vanella\Core\Url;
use Vanella\Handlers\Entrypoint;

class Authentication extends Entrypoint
{
    protected $activeAuthClientEndpointGroup = 'Auth';
    protected $isAuthActivated;
    protected $authEndpointList;
    protected $authConfig = ['default' => []];
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
        $this->authConfig['default'] = !empty($args['config']['authentication']) ? $args['config']['authentication'] : [];

        // Declare the access_rule as empty at first. This line here will determine
        // if the certain resource or endpoint can be access
        $this->authConfig['access_rule'] = [];
        Helpers::run_child_method($this, 'accessRule');

        // Turn this on if you want authentication on this endpoint group.
        $this->isAuthActivated = isset($this->authConfig['default']['isAuthActivated']) ? $this->authConfig['default']['isAuthActivated'] : null;

        // Set to true if you want more detailed json response for the authentication handler
        $this->isAuthInDebugMode = isset($this->authConfig['default']['isAuthInDebugMode']) ? $this->authConfig['default']['isAuthInDebugMode'] : null;

        // Turn this on to enable custom authentication
        $this->enableCustomAuthentication = isset($this->authConfig['default']['enableCustomAuthentication']) ? $this->authConfig['default']['enableCustomAuthentication'] : null;

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
        $responseCode = 200;

        // If auth config is missing
        if (!$this->authConfig['access_rule']) {
            $authStatusResponse = array_merge([
                'message' => 'Please specify endpoint names to be authenticated. You set the isAuthactivated to true, therefore this API framework requires you to specify the access rule for each endpoints. See the endpoint list below.',
            ], $authStatusResponse);
            $this->isAuthenticationSuccessful = true;
            $responseCode = 400; // Bad Request
        }

        // If AuthConfig is set
        if ($this->authConfig) {
            // If the current endpoint access rule is not specified
            if (!isset($this->authConfig['access_rule'][$this->endpoint])) {

                $baseUrl = Url::baseUrl();
                $endpointGroup = $this->endpointGroup;
                $endpointUrl = $baseUrl . strtolower($endpointGroup) . '/' . $this->endpoint;

                $authStatusResponse = array_merge([
                    'message' => 'The ' . $endpointUrl . ' endpoint does not have an access rule. Please add the access rule on this endpoint.',
                ], $authStatusResponse);

                $this->isAuthenticationSuccessful = false;
                $responseCode = 400; // Bad Request
            } else { // If the curent enpoint access rule is set

                // If the isAccessPageViaAccessToken is false
                if (isset($this->authConfig['access_rule'][$this->endpoint]['isAccessPageViaAccessToken'])) {

                    // If the endpoint can be access without having the need of an Access Token
                    if (!$this->authConfig['access_rule'][$this->endpoint]['isAccessPageViaAccessToken']) {
                        $authStatusResponse = array_merge([
                            'message' => 'The ' . $this->endpoint . ' endpoint is allowed to be access without an access token. Do not pass an access token.',
                        ], $authStatusResponse);
                        $this->isAuthenticationSuccessful = true;
                        $responseCode = 200;

                    } else { //
                        $authStatusResponse = array_merge([
                            'message' => 'You are not allowed to access this resource',
                        ], $authStatusResponse);
                        $this->isAuthenticationSuccessful = false;
                        $responseCode = 401; // Unauthorized
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
            Helpers::renderAsJson($authStatusResponse, $responseCode);
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

                    if (isset($token[0]) && $token[0] == 'Bearer' && isset($token[1])) {
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
                    } elseif (isset($token[0]) && $token[0] == 'Basic' && isset($token[1])) {
                        $this->allowAccess('POST');
                        Helpers::renderAsJson([
                            'tokens' => $token,
                            'tok' => $this->accessToken,
                        ]);
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
            if (isset($this->authConfig['access_rule'])) {
                $this->authConfig['access_rule'] = array_merge($this->authConfig['access_rule'], [
                    $endpointName => $args,
                ]);
            }
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
            isset($this->authConfig['default']['activeAuthName']) ? $this->authConfig['default']['activeAuthName'] : null,
            isset($this->authConfig['default']['authList']) ? $this->authConfig['default']['authList'] : [],
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
