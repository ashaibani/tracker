<?php

class Smartwatch
{
    public $socket;
    private $peername;
    private $index;
    private $debug;

    function __construct($socket, $index, $peername, $debug)
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
        socket_write($this->socket, $message, strlen($message));
    }

    // Reads and parses data from socket
    // maxLength = Maximum length of data to read
    // returns data
    function read($maxLength)
    {
        socket_recv($this->socket, $packet, $maxLength, 0);
        $data = '';
        if (false === empty($packet)) {
            // Remove new line characters
            $packet = preg_replace('~[\r\n]+~', '', $packet);
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
    // Example packet: [3G*8800000015*0002*LK]
    function parseResponse($packet)
    {
        // Check if valid format
        if (!(substr($packet, 0, 1) === '[' && substr($packet, -1) == ']')) {
            return "INVALID";
        }

        // Remove first and last character (the enclosing characters) from packet
        $packet = substr($packet, 1, -1);

        $data = explode("*", $packet);

        return $data;
    }

    // Extracts Command from provided Data
    // Data[] = data to parse info from
    // returns response
    function extractCommand($data)
    {
        // $commandData[0] == VENDOR
        // $commandData[1] == device id
        // $commandData[2] == content length
        // $commandData[3] == content
        $commandData = $data;
        $response = "[]";
        $command = "";


        // Check if packet contains atleast 4 parameters (vendor, device id, content length and content)
        if (count($commandData) < 4) {
            return $response;
        }

        // We only want the value of the first 4 bytes in ascii
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
            $command = "LK";
            $response = "[$commandData[0]*$commandData[1]*0002*LK]";
            $deviceData = "";
            if ($contentLength > 2) {
                $deviceData = explode(",", $commandData[3]);
                $step = $deviceData[1];
                $tumblingNumber = $deviceData[2];
                $batteryStatus = $deviceData[3];
            }
        }

        // Blind spot Data Supplements   
        if (substr($commandData[3], 0, 3) === "UD2") {
            $command = "UD2";
            $positionData = new PositionData(explode(",", $commandData[3]));
            $response = "No";
            return $response;
        }

        // Position data report 
        if (substr($commandData[3], 0, 2) === "UD") {
            $command = "UD";
            $response = "";
            $positionData = new PositionData(explode(",", $commandData[3]));
            return $response;
        }

        // Alarm data report 
        if (substr($commandData[3], 0, 2) === "AL") {
            $command = "AL";
            $positionData = new PositionData(explode(",", $commandData[3]));
            $response = "[$commandData[0]*$commandData[1]*0002*AL]";
            return $response;
        }

        return $response;
    }

    function destroy()
    {
        socket_close($this->socket);
    }
}

class PositionData
{
    public $date;
    public $time;
    public $positioning;
    public $latitude;
    public $latitudeCharacter;
    public $longitude;
    public $longitudeCharactor;
    public $speed;
    public $direction;
    public $altitude;
    public $satiliteNumber;
    public $gsmSignalStrength;
    public $batteryStatus;
    public $pedometer;
    public $tumblingTimes;
    public $terminalStatus;
    public $baseStation;
    public $wifiNumber;
    public $positioningAccuracy;

    function __construct($positionData)
    {
        $this->parse($positionData);
    }

    // Parses position data from packet
    // positionData = raw content split by "," 
    function parse($positionData)
    {
        $this->date = $positionData[1];
        $this->time = $positionData[2];
        $this->positioning = $positionData[3];
        $this->latitude = $positionData[4];
        $this->latitudeCharacter = $positionData[5];
        $this->longitude = $positionData[6];
        $this->longitudeCharactor = $positionData[7];
        $this->speed = $positionData[8];
        $this->direction = $positionData[9];
        $this->altitude = $positionData[10];
        $this->satiliteNumber = $positionData[11];
        $this->gsmSignalStrength = $positionData[12];
        $this->batteryStatus = $positionData[13];
        $this->pedometer = $positionData[14];
        $this->tumblingTimes = $positionData[15];

        // Parse from hexadecimel into binary, first 4 bytes are status, last 4 bytes are alarm?
        $this->terminalStatus = hex2bin($positionData[16]);

        // parse all values inbetween the next and last 2 as base station info
        for ($i = 17; $i < 17 + count($positionData) - 19; $i++) {
            $this->baseStation[] = $positionData[$i];
        }

        $this->wifiNumber = $positionData[count($positionData) - 2];
        $this->positioningAccuracy = $positionData[count($positionData) - 1];
    }
}
