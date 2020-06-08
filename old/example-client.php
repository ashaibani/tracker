<?php
$addr = '127.0.0.1';
$port = 1123;

$fp = fsockopen($addr, $port, $errno, $errstr, 30);
$positionPayload = "[3G*9403094122*00CD*UD2,180916,025723,A,22.570733,N,113.8626083,E,0.00,249.5,0.0 ,6,100,60,0,0,00000010,7,255,460,1,9529,21809,158,9529,63555,133,9529,63554,129 ,9529,21405,126,9529,21242,124,9529,21151,120,9529,63556,119,0,40.7]";
$payload = "[3G*8800000015*0002*LK]";
if (isset($argv[1])) {
    $payload = $argv[1];
}

if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, $positionPayload);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
}
