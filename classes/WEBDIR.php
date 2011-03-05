<?php

/**
 * Defines the constant WEBDIR. Normally it consists of the server name and
 * directory. Executed locally, it will be a local file path.
 */
function define_webdir() {
    if (isset($_SERVER['SERVER_NAME'])) {
        $protocol = 'http://';
        $uri = $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
    } else {
        $protocol = 'file://';
        $uri = $_ENV['PWD'] . '/';
    }
    define('WEBDIR', $protocol . str_replace('//', '/', $uri));
}

define_webdir();
?>
