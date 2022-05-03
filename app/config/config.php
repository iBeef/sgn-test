<?php

// App Root
define('APPROOT', dirname(dirname(__FILE__)));

// URL Root
$url = (!empty($_SERVER['HTTPS'])) ? "https://" : "http://";
$url .= $_SERVER['HTTP_HOST'];
define('URLROOT', $url); // Change to suit your route

// Site Name
define('SITENAME', 'Stop Go Networks Test');

// Enable errors
ini_set('display_errors', 1);
// ini_set('display_errors', 0); // Disable errors to mimic production
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
