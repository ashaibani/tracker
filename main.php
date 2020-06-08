<?php
include "server.php";

$server = new Server('0.0.0.0', 1123, true);
$server->listen();
