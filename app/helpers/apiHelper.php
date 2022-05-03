<?php

/**
 * Sets the header to 'application/json'
 *
 * @return void
 */
function setJsonHeader() {
    $jsonHeader = 'Content-Type: application/json';
    header($jsonHeader);
}

/**
 * Returns JSON data
 *
 * @param assoc $json
 * @return void
 */
function sendJson($json, $statusCode=200) {
    setJsonHeader();
    http_response_code($statusCode);
    echo json_encode($json);
    die();
}