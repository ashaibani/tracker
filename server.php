<?php
// Author: Mohamed Ashaibani <mohamed@ashaibani.com>
// Simple TCP Server Class.
class Server
{
    private $addr;
    private $port;
    private $socket;
    private $clients;
    private $delay;

    // Class Constructor
    // addr = address to listen on
    // port = port to bind to
    // debug = wether or not to print debug info
    // delay = delay to which you wait inbetween clients
    function __construct($addr, $port, $debug = false, $delay = 100)
    {
        $this->addr = $addr;
        $this->port = $port;
        $this->debug = $debug;

        // Convert to milliseconds
        $this->delay = $delay * 100;

        // Initialise socket
        $this->createSocket();
    }

    // Close socket on Object destruction
    function __destruct()
    {
        fclose($this->socket);
    }

    // Creates socket and checks if it can bind to port.
    private function createSocket()
    {
        $this->socket = stream_socket_server("tcp://" . $this->addr . ":" . $this->port, $errno, $errstr);
        if (!$this->socket) {
            echo "$errstr ($errno)\n";
            die('[Smartwatch Tracker] Could not create socket, is port in use?');
        }
        if ($this->debug) {
            echo "[Smartwatch Tracker - DEBUG] Running on: $this->addr:$this->port\n";
        }
    }

    // Begins listening to the socket
    // maxLength = Maximum length of message to read from client
    function listen($maxLength = 2000)
    {
        include "smartwatch.php";
        while (true) {
            while ($client = stream_socket_accept($this->socket, -1, $peername)) {
                $smartWatch = new Smartwatch($client, $peername, $this->debug);

                // Add to array in case of future use
                $this->clients[] = $smartWatch;

                echo "[Smartwatch Tracker] Connection received from: $peername\n";
                $data = $smartWatch->read($maxLength);

                // TO-DO: do something with data

                // Right now this just sends the data back to smart watch.
                $smartWatch->write(json_encode($data));

                fclose($smartWatch->socket);
                usleep($this->delay);
            }
        }
    }
}
