<?php
include '../../vendor/autoload.php';
include 'autoloader.inc.php';
use Vanella\Handlers\Helpers;

// The config file names
$configNames = Helpers::loadConfig('../config/configList.php');

// Load all config files
$config = [];
foreach ($configNames as $item) {
    $config = array_merge($config, Helpers::loadConfig('../config/'.$item.'.php', $item));
}  

// Run the entrypoint
new Vanella\Handlers\Execution(['config' => $config]);
