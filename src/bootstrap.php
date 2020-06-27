<?php
include '../../vendor/autoload.php';
include 'autoloader.inc.php';
$config = [];

// load database main config
if (file_exists('../config/main.php')) {
    $config['main'] = require_once 'config/main.php';
}

// load database config
if (file_exists('../config/database.php')) {
    $config['database'] = require_once 'config/database.php';
}

// load restful config
if (file_exists('../config/restful.php')) {
    $config['restful'] = require_once 'config/restful.php';
}

// load authentication
if (file_exists('../config/authentication.php')) {
    $config['authentication'] = require_once 'config/authentication.php';  
}

new Vanella\Handlers\Execution(['config' => $config]);
