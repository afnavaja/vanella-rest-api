<?php

use Vanella\Handlers\Authentication;
use Vanella\Handlers\Helpers;
use \Firebase\JWT\JWT;

/**
 * This is a built in class for Vanella.
 */
class Auth extends Authentication
{
    protected $tableName = "users";

    public function __construct($args = [])
    {
        $this->childClass = $this;
        parent::__construct($args);
    }

    /**
     * The default config of this api endpoint class
     *
     * @return void
     */
    public function defaultConfig()
    {
        $this->_registerEndpointToAccessRule('client', [
            'isAccessPageViaAccessToken' => false,
        ]);
    }

    /**
     * This is the auto generated built in Authentication
     * for your api. You can set custom authentication
     * by referring the implementation on this class
     * Note: We are using OAuth2 for Authorization
     * and JWT for handling the access tokens.
     *
     * @return void
     */
    public function client()
    {
        try {
            $success = true;

            // The clientId from the request header
            $requestClientId = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;

            // The clientSecret from the request header
            $requestClientSecret = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;

            // Extract the authconfig from config/authentication.php
            $extractedAuthConfig = $this->_extractAuthConfig($requestClientId);

            // Set success to false if given wrong credentials
            if (!isset($requestClientId)
                || !isset($requestClientSecret)
                || !$this->_validateEquality($requestClientId, $extractedAuthConfig['app']['clientId'])
                || !$this->_validateEquality($requestClientSecret, $extractedAuthConfig['app']['clientSecret'])
            ) {
                $success = false;
            }
            if (!$success) {
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                $responseCode = 401;
                $data = [
                    'success' => false,
                    'message' => "Wrong credentials!",
                ];
            }
            // Run this code if successfully validated
            else {

                // This is needed for the jwt authentication
                $key = $extractedAuthConfig['authConfig']['secretKey'];

                // Unset the config that are not necessarilly needed for the payload
                unset($extractedAuthConfig['authConfig']['name']);
                unset($extractedAuthConfig['authConfig']['secretKey']);
                unset($extractedAuthConfig['authConfig']['algo']);

                $payload = array_merge($extractedAuthConfig['authConfig'], [
                    'clientId' => $extractedAuthConfig['app']['clientId'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'requestMethod' => $_SERVER['REQUEST_METHOD'],
                    'remoteAddrress' => $_SERVER['REMOTE_ADDR'],
                ]);

                // Generate JWT(Json Wet Token)
                $jwt = JWT::encode($payload, $key);

                $data['access_token'] = $jwt;
                $data['issued_at'] = date('Y-m-d g:i:s A', $extractedAuthConfig['authConfig']['iat']);
                $data['available_at'] = date('Y-m-d g:i:s A', $extractedAuthConfig['authConfig']['nbf']);
                $data['expiration'] = date('Y-m-d g:i:s A', $extractedAuthConfig['authConfig']['exp']);
                $responseCode = 200;
            }

            Helpers::renderAsJson($data, $responseCode, 'POST');
            exit();
        } catch (\Exception $e) {
            Helpers::renderAsJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
