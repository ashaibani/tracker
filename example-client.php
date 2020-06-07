<?php
$addr = '127.0.0.1';
$port = 1123;

$fp = fsockopen($addr, $port, $errno, $errstr, 30);

$payload = "[3G*8800000015*0002*LK]";
if(isset($argv[1])) {
    $payload = $argv[1];
}

if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, $payload);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
}
