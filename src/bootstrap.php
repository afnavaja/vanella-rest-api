<?php
include '../../vendor/autoload.php';
include 'autoloader.inc.php';

$config['main'] = require_once 'config/main.php';
$config['database'] = require_once 'config/database.php';
$config['restful'] = require_once 'config/restful.php';
$config['authentication'] = require_once 'config/authentication.php';

new Vanella\Handlers\Execution(['config' => $config]);
