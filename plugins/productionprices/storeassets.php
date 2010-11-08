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

$marketid = $core->GetSetting("MarketLocation");
$markethanger = $core->GetSetting("MarketHanger");
$CorpStorePrice = $core->GetSetting("CorpStorePrice");
$AllyStorePrice = $core->GetSetting("AllyStorePrice");

$result1 = $core->SQL("SELECT DISTINCT nameid, SUM(qty) as 'Totalqty' FROM info_assets WHERE itemidm = ".$marketid." AND flag = ".$markethanger." GROUP BY nameid");
//echo "SELECT DISTINCT nameid FROM info_assets WHERE itemidm = ".$marketid."<br>";
while($row = mysql_fetch_assoc($result1))
{
    $nameid = $row['nameid'];
    $stock = $row['Totalqty'];

    //Production Items Database Update.
    $registered = $core->SQL("SELECT * FROM `production_items` WHERE EveTypeID = ".$nameid." AND Type = 2");
    $total_records = mysql_num_rows($registered);
    if ($total_records == 0)
    {
        $result2 = $core->EVESQL("SELECT * FROM invTypes_expanded WHERE typeID = ".$nameid);
        while($row2 = mysql_fetch_assoc($result2))
        {
            $typeID = $row2['typeID'];
            $groupID = $row2['groupID'];
            $typeName = $core->SQLEscape($row2['typeName']);
            $graphicID = $row2['graphicID'];
            $marketGroupID = $row2['marketGroupID'];
        }

        if($marketGroupID != "")
        {
        //echo "SELECT t2.marketGroupName AS Race, t3.marketGroupName AS GroupName FROM invMarketGroups AS t2 INNER JOIN invMarketGroups AS t3 ON t2.marketGroupID=".$marketGroupID." AND t2.parentGroupID=t3.marketGroupID ORDER BY GroupName ASC, Race ASC<br>";
        $result3 = $core->EVESQL("SELECT t2.marketGroupName AS Race, t3.marketGroupName AS GroupName FROM invMarketGroups AS t2 INNER JOIN invMarketGroups AS t3 ON t2.marketGroupID=".$marketGroupID." AND t2.parentGroupID=t3.marketGroupID ORDER BY GroupName ASC, Race ASC");
        $total_records = mysql_num_rows($result3);
        if ($total_records != 0)
        {
        while($row3 = mysql_fetch_assoc($result3))
        {
            $Race = $core->SQLEscape($row3['Race']);
            $GroupName = $core->SQLEscape($row3['GroupName']);
        }

        if($Race == "")
        {
            $query_postinfo = "INSERT INTO `production_items` (EveTypeID, EveGraphicID, Type, Name, GroupName, Stock)
               VALUES ($typeID, $graphicID, 2, '$typeName', '$GroupName', $stock)";
            //echo $query_postinfo."<br>";
            $core->SQL($query_postinfo);
        }
        elseif($graphicID == "")
        {
            $query_postinfo = "INSERT INTO `production_items` (EveTypeID, Type, Name, GroupName, Stock)
               VALUES ($typeID, 2, '$typeName', '$GroupName', $stock)";
            //echo $query_postinfo."<br>";
            $core->SQL($query_postinfo);
        }
        else
        {
            $query_postinfo = "INSERT INTO `production_items` (EveTypeID, EveGraphicID, Type, Name, GroupName, Race, Stock)
               VALUES ($typeID, $graphicID, 2, '$typeName', '$GroupName', '$Race', $stock)";
            //echo $query_postinfo."<br>";
            $core->SQL($query_postinfo);
        }
        }
        }
    }
    else
    {
     //Update Current Stockpile.
     $core->SQL("UPDATE production_items SET Stock=".$stock." WHERE EveTypeID = ".$nameid." AND Type = 2 LIMIT 1");

     //Eve Central Items Import for Produciton Prices (Items Exist so may as well update the prices)
     $result2 = $core->SQL("SELECT Smedian FROM `operations_marketdata` WHERE EveTypeID = ".$nameid);
     $total_records = mysql_num_rows($result2);
      if ($total_records <> 0)
      {
        while($row2 = mysql_fetch_assoc($result2))
        {
            $Smedian = $row2['Smedian'];
        }

        //Figures the Mark up based on Jita Sell Average Prices.  If there is no Market Price Keep prices at Zero (Which will hide it in the list later)
        if ($Smedian != "" || $Smedian == 0)
        {
         $total = $Smedian;
         $totaldifference_Corp = ($CorpStorePrice / 100) * $total;
         $totaldifference_Ally = ($AllyStorePrice / 100) * $total;
         $total_Corp = roundPrecision($total + $totaldifference_Corp);
         $total_Ally = roundPrecision($total + $totaldifference_Ally);

         //echo $nameid."--".$total."--".$total_Corp."--".$total_Ally."**".$totaldifference_Corp."--".$totaldifference_Ally."<br>";
         $core->SQL("UPDATE production_items SET Price=".$total_Corp." WHERE EveTypeID = ".$nameid." AND Type = 2 LIMIT 1");
         $core->SQL("UPDATE production_items SET AlliancePrice=".$total_Ally." WHERE EveTypeID = ".$nameid." AND Type = 2 LIMIT 1");
        }
      }
    }
}

function roundPrecision($value, $precision=3 )
{
    $round = $precision - floor(log10(abs($value))) - 1;
    return round($value, $round);
}

function roundDigits( $value, $precision=0 )
{
    $precisionFactor = ($precision == 0) ? 1 : pow( 10, $precision );
    return round( $value * $precisionFactor ) / $precisionFactor;
}

?>