<?php

/**
 * Prints an array in a easy to view format
 *
 * @param array $arr
 * @return void
 */
function prettyPrint($arr) {
    print("<pre>".print_r($arr, true)."</pre>");
    die();
}