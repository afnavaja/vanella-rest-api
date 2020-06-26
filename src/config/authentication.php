<?php
use Vanella\Core\Url;

return [       
    'isAuthActivated' => true, // Set to true if you want authentication to be activated     
    'enableCustomAuthentication' => false, // Set to true if you want to enable custom authentication (See class Handlers\Authentication).
    'isAuthInDebugMode' => false, // Set to true if you want more detailed json response for the authentication handler. This is used in debugging mode. (See class Handlers\Authentication).
    'activeAuthName' => 'jwt', // This is the active authentication for generating access_token
    'authenticatedApps' => [
        [
            'appName' => 'vanellaApp', // Just the app name
            'clientId' => 'vanellaAppId', // Put your client id here.
            'clientSecret' => 'ul3kjsdlifslfkwjerlkj', // Put your client secret key here
        ],
        [            
            'appName' => 'vanellaAppMobile', // Just the app name
            'clientId' => 'vanellaAppMobileId', // Put your client id here.
            'clientSecret' => 'sdfjk23l4kldfksjlkfsdfy', // Put your client secret key here
        ],        
    ],
    'authList' => [
        [
            'name' => 'jwt',
            'secretKey' => 'oeipxqaa42fs42g1iaiw910sskjsoeixjha6ah72j', // Just specify your secret key here for generating access_token
            'iss' => Url::baseUrl(), // From what domain.
            'aud' => Url::baseUrl(), // From which users.
            'iat' => time(), // From what time this token is created.
            'nbf' => time(), // From what time this token is available for usage.
            'exp' => time() + 600, // From what time this token would be expired.
            'algo' => 'HS256' // What algorithm the jwt is using
        ],
    ]
];