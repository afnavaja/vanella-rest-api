<?php

// Autoload classes
spl_autoload_register(function ($classname) {
    $path = '../classes/' . $classname . '.php';

    if (!file_exists($path)) {
        return false;
    }

    require_once ($path);
});

// Autoload restful classes
spl_autoload_register(function ($classname) {
    $path = '../restful/' . $classname . '.php';

    if (!file_exists($path)) {
        return false;
    }

    require_once ($path);
});
