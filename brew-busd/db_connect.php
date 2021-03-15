<?php
require 'vendor/autoload.php';

use Dibi\Connection;


$db = new Dibi\Connection([
    'driver'   => 'mysqli',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'defi_cafe',
    'profiler' => [
        'file' => 'file.log',
    ],
]);

