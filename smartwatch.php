<?php

class Smartwatch
{
    public $socket;
    private $peername;
    private $debug;

    function __construct($socket, $peername, $debug)
    {
        $this->socket = $socket;
        $this->peername = $peername;
        $this->debug = $debug;
    }

    // Writes a message to the smart watch
    // message = Message to write to smartwatch
    // newLine = Weither or not too append a newLine to the reply.
    function write($message, $newLine = false)
    {
        if ($newLine) {
            $message = "$message\n";
        }
        fwrite($this->socket, $message);
    }

    // Reads and parses data from socket
    // maxLength = Maximum length of data to read
    // returns data
    function read($maxLength)
    {
        $packet = stream_socket_recvfrom($this->socket, $maxLength);
        $data = '';
        if (false === empty($packet)) {
            if ($this->debug) {
                echo "[Smartwatch Tracker - debug] Packet recieved: $packet\n";
            }
            $data = $this->parseResponse($packet);
        }
        $commandData = $this->extractCommand($data);
        return $commandData;
    }

    // Parses data from recieved packet
    // packet = raw packet data
    // returns: data parsed and formatted to json
    // Example packet: 
    function parseResponse($packet)
    {
        // Check if valid format
        if (!(strpos($packet, "[") === 0 && substr($packet, -1) == ']')) {
            return "INVALID";
        }

        // Remove first and last character (the enclosing characters) from packet
        $packet = substr($packet, 1, -1);

        $data = explode("*", $packet);

        return $data;
    }

    // Extracts Command from provided Data
    // Data[] = data to parse info from
    function extractCommand($data)
    {
        $commandData = $data;

        // We only want the value of the first 4 bytes in ascii
        $contentLength = 0;
        for ($pos = 0; $pos < 4; $pos++) {
            $byte = $commandData[2][$pos];
            if (is_numeric($contentLength)) {
                $contentLength += intval($byte);
            } else {
                $contentLength += ord($byte);
            }
        }

        // $commandData[0] == VENDOR
        // $commandData[1] == device id
        // $commandData[2] == content length
        // $commandData[3] == content
        $response = "[]";


        // Link Command
        if (strpos($commandData[3], "LK") == 0) {
            // TO-DO: implement content length check
            $response = "[$commandData[0]*$commandData[1]*0002*LK]";
        }

        // Position data report 
        if (strpos($commandData[3], "UD") == 0) {
            $positionData = explode(", ", $commandData[3]);
            $response = "";
        }

        // Blind spot Data Supplements   
        if (strpos($commandData[3], "UD2") == 0) {
            $positionData = explode(", ", $commandData[3]);
            $response = "No";
        }

        // Alarm data report 
        if (strpos($commandData[3], "AL") == 0) {
            $positionData = explode(", ", $commandData[3]);
            $response = "[$commandData[0]*$commandData[1]*0002*AL]";
        }

        echo "Vendor: $commandData[0], Device ID: $commandData[1], Expected Data Length: $contentLength & Command: $commandData[3]\n";

        return $response;
    }
}
