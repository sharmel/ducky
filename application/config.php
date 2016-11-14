<?php

date_default_timezone_set('Europe/Brussels');
define('BASE_URI','/');

$settings = array(
    'RECAPTCHA_KEY' => '',
    'DEFAULT_LANG' => 'en',
);

$db_config = array(
    'driver' => 'pgsql',
    'username' => 'db_user',
    'password' => 'db_pass',
    'schema' => 'public',

    'dsn' => array(
        'host' => 'db',
        'dbname' => 'db_name',
        'port' => 'db_port',
    )
);
