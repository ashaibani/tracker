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
    private $maxClients;
    private $clientIndex = 0;

    // Class Constructor
    // addr = address to listen on
    // port = port to bind to
    // debug = wether or not to print debug info
    // delay = delay to which you wait inbetween clients
    function __construct($addr, $port, $debug = false, $maxClients = 64, $delay = 100)
    {
        $this->addr = $addr;
        $this->port = $port;
        $this->debug = $debug;

        // Convert to milliseconds
        $this->delay = $delay * 100;

        $this->maxClients = $maxClients;

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
        $this->socket = stream_socket_server("tcp://$this->addr:$this->port", $errno, $errstr);
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
                $smartWatch = new Smartwatch($client, 1, $peername, $this->debug);

                // Add to array in case of future use
                $this->clients[$this->clientIndex] = $smartWatch;
                $this->clientIndex++;

                echo "[Smartwatch Tracker] Connection received from: $peername\n";

                // Read info from socket
                $data = $smartWatch->read($maxLength);

                // If command is valid, extract and process the output.
                if ($data !== "INVALID") {
                    $data = $smartWatch->extractCommand($data);
                }

                $smartWatch->write($data);

                $smartWatch->destroy();
                unset($this->clients[$this->clientIndex]);

                usleep($this->delay);
            }
        }
    }
}
