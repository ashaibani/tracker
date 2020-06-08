<?php

use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';
include 'functions.php';

// Creae A Worker and listen 1123 port，not specified protocol
$tcp_worker = new Worker("tcp://0.0.0.0:1123");

// 4 processes
$tcp_worker->count = 4;

// Emitted when new connection come
$tcp_worker->onConnect = function ($connection) {
    echo "[Smartwatch tracker] New connection\n";
};

// Emitted when data is received
$tcp_worker->onMessage = function ($connection, $data) {
    // Send hello $data
    echo $data;
    $commandData = explode("*", substr($data, 1, -1));

    if (!(substr($data, 0, 1) === '[' && substr($data, -1) == ']')) {
        $connection->send('INVALID');
        return;
    }

    if (count($commandData) < 4) {
        $connection->send('INVALID');
        return;
    }

    $contentLength = 0;
    for ($pos = 0; $pos < 4; $pos++) {
        $byte = $commandData[2][$pos];
        // If its already a number, take it for face value
        if (is_numeric($contentLength)) {
            $contentLength += intval($byte);
        } else {
            // If not, get ascii value
            $contentLength += ord($byte);
        }
    }

    // Link Command
    if (substr($commandData[3], 0, 2) === "LK") {
        $deviceData = "";
        if ($contentLength > 2) {
            $deviceData = explode(",", $commandData[3]);
            $step = $deviceData[1];
            $tumblingNumber = $deviceData[2];
            $batteryStatus = $deviceData[3];
        }
        $connection->send('[' . $commandData[0] . '*' . $commandData[1] . '*0002*LK]', true);
        return;
    }

    // Blind spot Data Supplements   
    if (substr($commandData[3], 0, 3) === "UD2") {
        $positionData = new PositionData(explode(",", $commandData[3]));
        $response = "No";
        $connection->send('No', true);
        return;
    }

    // Position data report 
    if (substr($commandData[3], 0, 2) === "UD") {
        $connection->send('', true);
        $positionData = new PositionData(explode(",", $commandData[3]));
        return;
    }

    // Alarm data report 
    if (substr($commandData[3], 0, 2) === "AL") {
        $positionData = new PositionData(explode(",", $commandData[3]));
        $connection->send('[' . $commandData[0] . '*' . $commandData[1] . '*0002*AL]', true);
        return;
    }

    $connection->send($data);
};

// Emitted when connection closed
$tcp_worker->onClose = function ($connection) {
    echo "[Smartwatch tracker] Connection closed\n";
};

// Run worker
Worker::runAll();
