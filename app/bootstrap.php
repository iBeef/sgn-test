<?php

// Load Config
require_once "config/credentials.php";
require_once "config/config.php";

// Autoload core libraries
spl_autoload_register(function($className) {
    require_once 'libraries/' . $className . '.php';
});

// Autoload vendor files
// include_once "../vendor/autoload.php";

// Load helper files
require_once "helpers/apiHelper.php";
require_once "helpers/debugHelper.php";