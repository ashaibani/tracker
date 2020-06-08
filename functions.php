<?php
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