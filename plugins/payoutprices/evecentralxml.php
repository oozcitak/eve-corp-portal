<?php

// Find Core Pathing
if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."core.class.php"); // ./../../core.class.php
elseif(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php"); // ./../core.class.php
elseif(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php"); // ./core/core.class.php
elseif(file_exists("core.class.php"))
  require_once("core.class.php"); // ./core.class.php
else
{

}

  $core = new Core();
  $GLOBALS["core"]=$core;

// Prepares URL DATA to include price infomation for all items listed in the operational_items database.
$tempcount = 0;
$result = $core->SQL("SELECT EveTypeID FROM `operations_items`");
while($row = mysql_fetch_assoc($result))
{
    ProcessItem($row['EveTypeID']);
}
$core->Log("CRON(Eve-Central Market Data) Done. All operational items were process successfully.");

function ProcessItem($itemid)
{
$core = $GLOBALS["core"];
$evecentralsite = "http://api.eve-central.com/api/marketstat?typeid=".$itemid."&regionlimit=10000002";
$raw = file_get_contents($evecentralsite);

if($raw !== FALSE)
{
    $xml = new SimpleXMLElement($raw);
    foreach($xml->marketstat->children() as $item)
    {
      $eveitemid = (int)$item["id"];
      $registered = $core->SQL("SELECT COUNT(*) FROM `operations_marketdata` WHERE EveTypeID =".$eveitemid);
      $total_records = mysql_num_rows($registered);
      if ($total_records <> 0) $core->SQL("DELETE FROM `operations_marketdata` WHERE EveTypeID =".$eveitemid);

      $countall = 1;
      foreach($item->all->children() as $itemall)
      {
        if($countall == 1) $volume =  $itemall;
        if($countall == 2) $avg = $itemall;
        if($countall == 3) $max = $itemall;
        if($countall == 4) $min = $itemall;
        if($countall == 5) $stddev = $itemall;
        if($countall == 6) $median = $itemall;
        $countall = $countall + 1;
      }

      $countall = 1;
      foreach($item->buy->children() as $itemall)
      {
        if($countall == 1) $Bvolume = $itemall;
        if($countall == 2) $Bavg = $itemall;
        if($countall == 3) $Bmax = $itemall;
        if($countall == 4) $Bmin = $itemall;
        if($countall == 5) $Bstddev = $itemall;
        if($countall == 6) $Bmedian = $itemall;
        $countall = $countall + 1;
      }

      $countall = 1;
      foreach($item->sell->children() as $itemall)
      {
        if($countall == 1) $Svolume = $itemall;
        if($countall == 2) $Savg = $itemall;
        if($countall == 3) $Smax = $itemall;
        if($countall == 4) $Smin = $itemall;
        if($countall == 5) $Sstddev = $itemall;
        if($countall == 6) $Smedian = $itemall;
        $countall = $countall + 1;
      }

      $query_postinfo = "INSERT INTO `operations_marketdata` (EveTypeID, volume, avg, max, min, stddev, median, Bvolume, Bavg, Bmax, Bmin, Bstddev, Bmedian, Svolume, Savg, Smax, Smin, Sstddev, Smedian)
          VALUES ($eveitemid, $volume, $avg, $max, $min, $stddev, $median, $Bvolume, $Bavg, $Bmax, $Bmin, $Bstddev, $Bmedian, $Svolume, $Savg, $Smax, $Smin, $Sstddev, $Smedian)";
      $core->SQL($query_postinfo);
    }
}
}


?>