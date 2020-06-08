<?php
include "server.php";
$addr = '0.0.0.0';
$port = 1123;

if (isset($argv[1])) {
    $addr = $argv[1];
}

if (isset($argv[2])) {
    $port = intval($argv[2]);
}

$server = new Server($addr, $port, true);
$server->listen();
