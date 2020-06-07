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
        return $data;
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

        // $data[0] == VENDOR
        // $data[1] == device id
        // $data[2] == content length
        // $data[3] == content
        // TO-DO - convert content length to ascii values and parse length from content.
        echo "Vendor: $data[0], Device ID: $data[1], Expected Data: $data[2] & $data[3]\n";

        return $data;
    }
}
