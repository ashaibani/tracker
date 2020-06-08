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
        socket_close($this->socket);
    }

    // Creates socket and checks if it can bind to port.
    private function createSocket()
    {
        if (!$this->socket = socket_create(AF_INET, SOCK_STREAM, 0)) {
            echo "failed to create socket: " . socket_strerror($this->socket) . "\n";
            exit();
        }

        if (!$ret = socket_bind($this->socket, $this->addr, $this->port)) {
            echo "failed to bind socket: " . socket_strerror($ret) . "\n";
            exit();
        }

        if (!$ret = socket_listen($this->socket, 0)) {
            echo "failed to listen to socket: " . socket_strerror($ret) . "\n";
            exit();
        }

        socket_set_nonblock($this->socket);
    }

    // Begins listening to the socket
    // maxLength = Maximum length of message to read from client
    function listen($maxLength = 2000)
    {
        include "smartwatch.php";
        while (true) {
            $client = @socket_accept($this->socket);
            if ($client === false) {
                usleep($this->delay);
            } elseif ($client > 0) {
                socket_getpeername($client, $address);
                $smartWatch = new Smartwatch($client, 1, $address, $this->debug);

                // Add to array in case of future use
                $this->clients[$this->clientIndex] = $smartWatch;
                $this->clientIndex++;

                echo "[Smartwatch Tracker] Connection received from: $address\n";

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
