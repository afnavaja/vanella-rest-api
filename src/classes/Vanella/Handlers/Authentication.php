<?php

namespace Vanella\Handlers;

use Firebase\JWT\JWT;
use Vanella\Core\Url;
use Vanella\Database\QueryBuilder as Database;
use Vanella\Handlers\Entrypoint;

class Authentication extends Entrypoint
{
    const AUTH_ENTITY_TYPE_CLIENT = 'client';
    const AUTH_ENTITY_TYPE_USER = 'user';

    protected $tableName = "";
    protected $tableColumns = [];
    protected $activeAuthClientEndpointGroup = 'Auth';
    protected $isRefreshTokenActivated;
    protected $authEndpointList;
    protected $authConfig = ['default' => []];
    protected $isAuthenticationSuccessful;
    protected $customAuthenticationMethod;
    protected $enableCustomAuthentication = false;
    protected $authStatusResponse = [];
    protected $isAuthStatusResponseDisplayed = false;
    protected $isAuthInDebugMode = false;
    protected $accessToken;
    protected $extractedAppConfig;
    protected $clientId;
    protected $clientSecret;
    protected $username;
    protected $password;
    protected $validatedUser;

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
     * Connect to the database
     */
    protected function dbConn()
    {
        return new Database(
            $this->dbConfig['db_host'],
            $this->dbConfig['db_username'],
            $this->dbConfig['db_password'],
            $this->dbConfig['db_name'],
            $this->dbConfig['db_driver']
        );
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

        // Turn this on if you want refresh token to persist in every request
        $this->isRefreshTokenActivated = isset($this->authConfig['default']['isRefreshTokenActivated']) ? $this->authConfig['default']['isRefreshTokenActivated'] : null;

        // Set to true if you want more detailed json response for the authentication handler
        $this->isAuthInDebugMode = isset($this->authConfig['default']['isAuthInDebugMode']) ? $this->authConfig['default']['isAuthInDebugMode'] : null;

        // Turn this on to enable custom authentication
        $this->enableCustomAuthentication = isset($this->authConfig['default']['enableCustomAuthentication']) ? $this->authConfig['default']['enableCustomAuthentication'] : null;

        // This is to override Authentication config class from the child class
        Helpers::run_child_method($this, 'defaultConfig');

        // Load the access token rule from config
        $this->_loadAccessTokenRuleFromConfig();

        if ($this->isAuthActivated()) {
            // Need to get the current Class and Function
            $this->authenticate();

            if ($this->endpointGroup == 'Auth') {
                $this->_getRequestClientApp();
                $this->_getRequestUser();
            }
        }
    }

    /**
     * Checks if Auth is Activated
     * 
     * @return boolean
     */
    protected function isAuthActivated()
    {
        return isset($this->authConfig['default']['isAuthActivated']) && $this->authConfig['default']['isAuthActivated'] ? true :false;
    }

    /**
     * Loads the access token rule from config
     * located in /src/restful/accessTokenRules
     * directory
     *
     * @return void
     */
    protected function _loadAccessTokenRuleFromConfig()
    {
        $rules = Helpers::loadConfig('../restful/AccessTokenRules/' . $this->endpointGroup . '.php');
        if (!empty($rules)) {
            foreach ($rules as $endpoint => $accessTokenRule) {
                $this->_registerEndpointToAccessRule($endpoint, [
                    'isAccessPageViaAccessToken' => $accessTokenRule,
                ]);
            }
        }
    }

    /**
     * Get the clientApp from the request headers
     */
    protected function _getRequestClientApp()
    {
        // The clientId from the request header
        $this->clientId = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;

        // The clientSecret from the request header
        $this->clientSecret = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
    }

    /**
     * Get the user credentials from the request body
     */
    protected function _getRequestUser()
    {
        // The username from the request body
        $this->username = isset($this->request['username']) ? $this->request['username'] : null;

        // The password from the request body
        $this->password = isset($this->request['password']) ? $this->request['password'] : null;
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
            
        // Set the access token if we could grab one
        $this->_setAccessToken();

        $responseCode = 200;

        // If auth config is missing
        if (!$this->authConfig['access_rule']) {
            $this->authStatusResponse = array_merge([
                'message' => 'Please specify endpoint names to be authenticated. You set the isAuthActivated to true, therefore this API framework requires you to specify the access rule for each endpoints. See the endpoint list below.',
            ], $this->authStatusResponse);
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

                $this->authStatusResponse = array_merge([
                    'message' => 'The ' . $endpointUrl . ' endpoint does not have an access rule. Please add the access rule on this endpoint.',
                ], $this->authStatusResponse);

                $this->isAuthenticationSuccessful = false;
                $responseCode = 400; // Bad Request
            }

            // If the endpoint can be access without having the need of an Access Token
            if (!$this->_isPageAccessibleViaAccessToken()) {
                $this->authStatusResponse = array_merge([
                    'message' => 'The ' . $this->endpoint . ' endpoint is allowed to be access without an access token. Do not pass an access token.',
                ], $this->authStatusResponse);
                $this->isAuthenticationSuccessful = true;
                $responseCode = 200;

            } else {
                if (!$this->accessToken) {
                    $this->authStatusResponse = array_merge([
                        'message' => 'You are not allowed to access this resource',
                    ], $this->authStatusResponse);
                    $this->isAuthenticationSuccessful = false;
                    $responseCode = 401; // Unauthorized
                }
            }

            // Built in process for the access_token.
            // Read the access_token when not in Auth endpoint group
            if ($this->endpointGroup !== 'Auth' && $this->_isPageAccessibleViaAccessToken()) {             
                $this->authStatusResponse = array_merge($this->_processHeaderAuthorization(), $this->authStatusResponse);
            }
        }

        // Run your custom authentication here
        if ($this->enableCustomAuthentication) {
            Helpers::run_child_method($this, 'customAuthentication');
        }

        // Assign the auth response status
        $this->authStatusResponse = $this->isAuthInDebugMode ? array_merge($this->authStatusResponse, $this->_setAuthenticationStatus()) : $this->authStatusResponse;

        // If authentication failed. Display this set of message.
        if (!$this->isAuthenticationSuccessful) {
            Helpers::renderAsJson(array_merge($this->authStatusResponse, $this->_addRefreshTokenToResponse()), $responseCode);
        }
    }

    /**
     * Process Header authorization
     *
     * @param string $type
     *
     * @return array
     */
    protected function _processHeaderAuthorization($type = 'jwt')
    {
        // Get all headers that are sent
        $response = [];

        switch ($type) {
            case 'jwt':
            default:
                $jwtValidation = $this->_validateJWTAccessToken($this->accessToken);
                $this->isAuthenticationSuccessful = $jwtValidation['success'];
                break;
        }

        return $response;
    }

    /**
     * Set access token if there is any
     */
    protected function _setAccessToken()
    {
        if ($this->_isPageAccessibleViaAccessToken()) {
            $header = apache_request_headers();
            if (isset($header['Authorization'])) {
                $token = explode(' ', $header['Authorization']);
                if (isset($token[0]) && $token[0] == 'Bearer' && isset($token[1])) {
                    $this->accessToken = $token[1];
                }
            }           
        }
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
    protected function _extractAppConfig($requestClientId = null)
    {
        $app = [];

        if (isset($requestClientId)) {
            // Load the app client info from config/authentication.php
            $app = $this->_getActiveConfig(
                $requestClientId,
                $this->authConfig['default']['authenticatedApps'],
                'clientId'
            );
        } else {
            $app = $this->authConfig['default']['jwtGlobalConfig'];
        }

        return $app;
    }

    /**
     * Is page accessible via access token
     */
    protected function _isPageAccessibleViaAccessToken()
    {
        if (isset($this->authConfig['access_rule'][$this->endpoint]['isAccessPageViaAccessToken'])
            && $this->authConfig['access_rule'][$this->endpoint]['isAccessPageViaAccessToken']) {
            return true;
        } else {
            return false;
        }
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
     * Validates the user password in the users table
     *
     * @param string $var1
     * @param string $var2
     *
     * @return boolean
     */
    protected function _validatePassword($username, $password)
    {
        $db = $this->dbConn()
            ->select($this->mainConfig['defaultTableForUsers'], 'password,role,username,email')
            ->where('username', $username)->one();

        // Do not pass the password
        $dbPassword = isset($db['password']) ? $db['password']:null;
        unset($db['password']);

        // Pass the info the validated user
        $this->validatedUser = $db;

        if (!empty($db)) {
            return password_verify($password, $dbPassword) ? true : false;
        } else {
            return false;
        }
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
            'isAuthActivated' => isset($this->authConfig['default']['isAuthActivated']) ? $this->authConfig['default']['isAuthActivated']:false,
            'authConfig' => $this->authConfig,
            'endpointGroup' => $this->endpointGroup,
            'endpoint' => $this->endpoint,
            'isAuthenticationSuccessful' => $this->isAuthenticationSuccessful,
        ];
    }

    /**
     * Checks if the request is empty
     */
    protected function _checkRequestEmpty()
    {
        // Block the execution if the request is empty
        if (empty($this->request)) {
            Helpers::renderAsJson(array_merge([
                'success' => false,
                'message' => 'No data has been passed.',
            ], $this->_addRefreshTokenToResponse()), 400); // Bad request
        }
    }

    /**
     * Validate the user credentials
     *
     * @return void
     */
    public function validateUserCredentials($username, $password)
    {
        try {

            $isPasswordOk = $this->_validatePassword($username, $password);

            if (!$isPasswordOk) {
                $this->_wrongCredentials([
                    'success' => false,
                    'message' => 'Wrong username or password!',
                ]);
            }

            return true;
        } catch (\Exception $e) {
            $this->_wrongCredentials([
                'success' => false,
                'message' => $e->getMessage() . ' | Error in validating user credentials.',
            ]);
        }
    }

    /**
     * Validate the clientApp
     *
     * @return void
     */
    public function validateClientApp($clientId, $clientSecret)
    {
        if (empty($clientId)) {
            $this->_wrongCredentials([
                'success' => false,
                'message' => "Missing clientId!",
            ]);
        }

        $extractedAppConfig = $this->_extractAppConfig($clientId);
        // If unsuccessful validation do not run the rest of the code.
        if (!isset($clientId)
            || !isset($clientSecret)
            || !$this->_validateEquality($clientId, $extractedAppConfig['clientId'])
            || !$this->_validateEquality($clientSecret, $extractedAppConfig['clientSecret'])
        ) {
            $this->_wrongCredentials([
                'success' => false,
                'message' => "Wrong client app credentials!",
            ]);
        }

        return true;
    }

    /**
     * Set the json web token
     *
     * @return array
     */
    protected function _setJSONWebToken($clientId, $additionalPayload = [])
    {
        $specificAppConfig = $this->_extractAppConfig($clientId);
        $globalAppConfig = $this->_extractAppConfig();

        // Pass the key in another variable
        $key = $globalAppConfig['secretKey'];

        // Combine the global config and specific config
        $jwtPayload = array_merge($globalAppConfig, $specificAppConfig);

        // Unset the config that are not necessarilly needed for the payload
        unset($globalAppConfig['name']);
        unset($globalAppConfig['secretKey']);
        unset($globalAppConfig['algo']);

        // Prepare the payload
        $payload = array_merge($jwtPayload, [
            'serverName' => $_SERVER['SERVER_NAME'],
            'requestMethod' => $_SERVER['REQUEST_METHOD'],
            'remoteAddrress' => $_SERVER['REMOTE_ADDR'],
        ]);

        // Add the additional payload
        $payload = array_merge($payload, $additionalPayload);

        // Generate JWT(Json Web Token)
        $jwt = JWT::encode($payload, $key);

        $data['access_token'] = $jwt;
        $data['issued_at'] = date('Y-m-d g:i:s A', $specificAppConfig['jwt']['iat']);
        $data['available_at'] = date('Y-m-d g:i:s A', $specificAppConfig['jwt']['nbf']);
        $data['expiration'] = date('Y-m-d g:i:s A', $specificAppConfig['jwt']['exp']);

        return $data;
    }

    /**
     * Validates the jwt
     *
     * @param string $accessToken
     *
     * @return boolean
     */
    protected function _validateJWTAccessToken()
    {
        try {
            if ($this->_isPageAccessibleViaAccessToken()) {
                $jwtDecoded = $this->_getJWTDecoded($this->accessToken);

                return [
                    'success' => true,
                    'jwtDecoded' => $jwtDecoded,
                ];
            }
        } catch (\Exception $e) {
            Helpers::renderAsJson(array_merge([
                'success' => false,
                'message' => $e->getMessage() . ' | The access token might me missing or invalid or expired.',
            ], $this->_addRefreshTokenToResponse()), 401);
        }

        return [
            'success' => true,
            'jwtDecoded' => null,
        ];
    }

    /**
     * Will throw an error when page needs an access token
     *
     * @param $accessToken
     *
     * @return void
     */
    protected function _pageNeedsAccessToken($accessToken)
    {
        if ($this->isAuthActivated()
            && $this->_isPageAccessibleViaAccessToken()
            && $accessToken == '') {
            Helpers::renderAsJson([
                'access_token' => $this->accessToken,
                'success' => false,
                'message' => 'This resource needs an access token!',
            ], 401);
        }
    }

    /**
     * Decodes the JWT
     *
     * @param string $accessToken
     *
     * @return array
     */
    protected function _getJWTDecoded($accessToken)
    {

        if ($this->isAuthActivated() && $this->_isPageAccessibleViaAccessToken()) {
            try {
                $extractedAppConfig = $this->_extractAppConfig();
                $jwtDecoded = JWT::decode(
                    $accessToken,
                    $extractedAppConfig['secretKey'],
                    [$extractedAppConfig['algo']]);

                return $jwtDecoded;
            } catch (\Exception $e) {
                Helpers::renderAsJson(array_merge([
                    'success' => false,
                    'message' => $e->getMessage() . ' | The access token experienced an error in decoding.',
                ]), 401);
            }
        }
    }

    /**
     * Gets a new refresh token for jwt
     *
     * @param string $accessToken
     *
     */
    protected function _getJWTRefreshToken($oldAccessToken)
    {
        try {
            if ($this->_isPageAccessibleViaAccessToken()) {
                $success = false;

                // Validate access token
                $jwtValidation = $this->_validateJWTAccessToken($oldAccessToken);

                // Ensure the authentication is still okay
                $this->isAuthenticationSuccessful = $jwtValidation['success'];

                // Decoded data from the previous authentication with all the payloads
                $data = $jwtValidation['jwtDecoded'];

                if ($this->isAuthenticationSuccessful && !empty($data)) {
                    // Unset all of this since we are requesting a new one
                    unset($data->jwt);
                    unset($data->algo);
                    unset($data->name);
                    unset($data->secretKey);
                    unset($data->requestMethod);
                    unset($data->remoteAddrress);
                    unset($data->serverName);
                    unset($data->appName);

                    // Run the validations first
                    switch ($data->type) {
                        case self::AUTH_ENTITY_TYPE_CLIENT:
                            // Validate client app
                            $clientAppValidation = $this->validateClientApp($data->clientId, $data->clientSecret);
                            $success = $clientAppValidation;
                            break;
                        case self::AUTH_ENTITY_TYPE_USER:
                            // Validate client app
                            $clientAppValidation = $this->validateClientApp($data->clientId, $data->clientSecret);

                            // Validate user credentials
                            $userCredentialsValidation = $this->validateUserCredentials($data->username, $data->password);

                            // If all validations are true then return true otherwise false.
                            $success = $clientAppValidation && $userCredentialsValidation ? true : false;
                            break;
                    }

                    // If successfully validated, return the refresh token.
                    if ($success) {
                        // Set another jwt
                        $jwt = $this->_setJSONWebToken($data->clientId, (array) $data);
                        $refreshToken = $jwt['access_token'];
                        $this->accessToken = null;

                        return !empty($refreshToken) ? ['refresh_token' => $refreshToken] : [];
                    }
                }
            }
        } catch (\Exception $e) {
            Helpers::renderAsJson(array_merge([
                'success' => false,
                'message' => $e->getMessage() . ' | Error in refreshing access token.',
            ], $this->_addRefreshTokenToResponse()), 400);
        }

        return [];
    }

    /**
     * Adds a refresh token in the response
     *
     * @return array
     */
    protected function _addRefreshTokenToResponse()
    {
        // Add the refresh token to persist in each request
        if ($this->isRefreshTokenActivated && $this->_isPageAccessibleViaAccessToken()) {
            return $this->_getJWTRefreshToken($this->accessToken);
        }

        return $this->isAuthActivated() ? [
            'warning' => [
                'isAccessPageViaAccessToken' => false,
                'message' => 'Please register this endpoint to access rule and set it to true to apply authentication to this endpoint.',
            ],
        ] : [];
    }

    /**
     * Adds an additional messages in the request body used in debugging
     *
     * @return array
     */
    protected function _addAuthStatusResponse()
    {

        if ($this->isAuthInDebugMode) {
            return ['authStatusResponse' => $this->authStatusResponse];
        }

        return [];
    }

    /**
     * Response wrong credentials
     *
     * @param array $data
     *
     * @return void
     */
    protected function _wrongCredentials($data = [])
    {
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        Helpers::renderAsJson(array_merge($data, $this->_addRefreshTokenToResponse()), 401);
    }

    /**
     * Checks if the request is
     * POST,PUT,PATCH
     * 
     * @return boolean
     */
    protected function isPostPutPatchServerRequest() 
    {
        return in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT','PATCH']) ? true:false;
    }

}
