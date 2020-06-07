<?php
include "server.php";

$server = new Server('127.0.0.1', 1123, true);
$server->listen();
