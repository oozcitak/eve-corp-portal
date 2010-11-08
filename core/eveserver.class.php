<?php

class EVEServer
{
  public $Status;
  public $MOTD;
  public $Pilots;
  public $Countdown;
  public $EVETime;

  // Tranquility connection parameters
  const Server_Address = "87.237.38.200";
  const Server_Port = 26000;
  
  // Tranquility status
  const Unknown = 0;
  const Offline = 1;
  const Online = 2;
  const Starting = 3;

  function __construct() 
  {
    $this->Status = EVEServer::Offline;
    $this->MOTD = "";
    $this->Pilots = 0;
    $this->Countdown = 0;
    
    // Connect to server
    if(($sock = @fsockopen(EVEServer::Server_Address, EVEServer::Server_Port, $errno, $errstr, 1)) === false) return;
    stream_set_timeout($sock, 1);
    $this->Status = EVEServer::Unknown;
    // Send header
    $data = pack("C*", 0x23, 0x00, 0x00, 0x00, 0x7E, 0x00, 0x00, 0x00,
        0x00, 0x14, 0x06, 0x04, 0xE8, 0x99, 0x02, 0x00,
        0x05, 0x8B, 0x00, 0x08, 0x0A, 0xCD, 0xCC, 0xCC,
        0xCC, 0xCC, 0xCC, 0x00, 0x40, 0x05, 0x49, 0x0F,
        0x10, 0x05, 0x42, 0x6C, 0x6F, 0x6F, 0x64);
    fwrite($sock, $data);
    $response = fread($sock, 256);
    $data = unpack("C*", $response);
    $bytes = count($data);
    
    $this->Status = EVEServer::Online;
    $this->Pilots = 0;
    if ($bytes > 21)
    {
        $usersBytes = 0;
        // Amended usercount checks, info from clef on iRC
        // [16:01] <clef> BradStone: for the moment, take that byte[19] ... if it is 1, 8 or 9, the usercount is 0.
        // [16:01] <clef> BradStone: if it is 4, the next 32bit are the usercount. 5 -> 16bit. 6 -> 8bit.
        switch ($data[20])
        {
            case 4:
                $usersBytes = 4;
                break;
            case 5:
                $usersBytes = 2;
                break;
            case 6:
                $usersBytes= 1;
                break;
        }
        if ($usersBytes > 0 && $usersBytes < 5)
        {
            $multiplyer = 1;
            for ($i = 0; $i < $usersBytes; $i++)
            {
                $this->Pilots += $data[21 + $i] * $multiplyer;
                $multiplyer = $multiplyer * 256;
            }
        }
    }
    
    if (preg_match("/Starting\ up\.\.\.\((\d+)\ sec\.\)/", $response, $matches) == 1)
    {
      $this->Status = EVEServer::Starting;
      $this->Countdown = $matches[1] - 1;
    }
    
    fclose($sock);
    
    $this->MOTD = file_get_contents("http://www.eve.is/motd.asp?server=".EVEServer::Server_Address);
    if(substr($this->MOTD, 0, 12) == "<p>MOTD </p>")
      $this->MOTD = substr($this->MOTD, 12);
  }
  
}

?>
