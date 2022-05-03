<?php

require_once '../app/bootstrap.php';

// Init Router class
$router = new Router();

// API Routes

// API - Get users that aren't assigned to a specific company GET route.
$router->get('/api/employees', function($response) {
    $response->loadController('api', 'getEmployee');
});

// Error 404 route
$router->get('/404', function($response) {
    $response->loadController('api', 'error404');
});

// Run the app
$router->run();