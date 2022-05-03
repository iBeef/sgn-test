<?php

// Load .ini file
$config = parse_ini_file('../appConfig.ini', true);

// DB Params
define("DB_HOST", $config['db']['host']);
define("DB_USER", $config['db']['username']);
define("DB_PASS", $config['db']['password']);
define("DB_NAME", $config['db']['name']);