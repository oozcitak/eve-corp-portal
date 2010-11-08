<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');

// Variable of Current User & Misc
$portalid = $_GET["portalid"];
$templatepost = $_POST["template"];
$template = $_GET["template"];
$return = $_GET["return"];
$action = @$_GET["action"];

// Data Verification Checks and redirects
if(empty($action)) $action = "home";
if($portalid == "" || empty($portalid) || $portalid < 0 || $core->CharacterIDExists($portalid) == "FALSE") $portalid = $core->CurrentUser()->ID;
if($template == "" || empty($template) || $template < 0 ) $template = 0;
if($templatepost == "" || empty($templatepost) || $templatepost < 0 ) $templatepost = 0;
if($return == "" || empty($return) || $return < 0 ) $return = 0;


if($action == "home")
{
  $result = $core->SQL("SELECT * FROM operations_params");
  while($row=mysql_fetch_assoc($result))
  {
    if($row["Name"] == "IndexDate")
      $core->assign("indexdate", $row["Value"]);
    if($row["Name"] == "IndexTime")
      $core->assign("indextime", $row["Value"]);
  }  
}
elseif($action == "qtc7" || $action == "qtc30")
{
  $timeperiod = 7;
  if($action == "qtc30") $timeperiod = 30;
  $raw = file_get_contents("http://www.starvingpoet.net/feeds/mmi.xml");
  if($raw !== FALSE)
  {
    $xml = new SimpleXMLElement($raw);
    $date = $xml["date"];
    $date = substr($date, 0, 4)."-".substr($date, 4, 2)."-".substr($date, 6, 2);
    $prices = array();
    foreach($xml->children() as $item)
    {
      if((int)$item["timeperiod"] == $timeperiod)
      {
        foreach($item->children() as $price)
        {
          if(substr($price->getName(), 0, 9) != "Datacore-")
            $prices[] = array("Name" => $price->getName(), "Price" => $price->price);
        }
        break;
      }
    }
    if(!empty($prices))
    {
      // Update database
      $core->SQL("UPDATE operations_params SET `Value`='".$date."' WHERE `Name`='IndexDate'");
      $core->SQL("UPDATE operations_params SET `Value`='".$timeperiod."' WHERE `Name`='IndexTime'");
      foreach($prices as $price)
      {
        $core->SQL("UPDATE operations_items SET `Price`=".$price["Price"]." WHERE REPLACE(`Name`, ' ', '')='".$core->SQLEscape($price["Name"])."'");
      }
      
      // Item prices
      UpdatePrices();
    }
  }
  $core->Goto("index.php?result=1");
}
elseif($action == "edit" || $action == "payout")
{
  if($action == "edit")
    $result = $core->SQL("SELECT * FROM operations_items WHERE GroupID <= 1 ORDER BY GroupID ASC, `Name` ASC");
  else
    $result = $core->SQL("SELECT * FROM operations_items WHERE GroupID > 1 ORDER BY GroupID ASC, `Name` ASC");
    
  $dbprices = array();
  while($row = mysql_fetch_assoc($result))
  {
    $dbprices[] = array("ID" => $row["id"], "Group" => $row["GroupID"], "Name" => $core->SQLUnEscape($row["Name"]), "Price" => number_format(exp_to_dec($row["Price"]), 2, '.', ''), "Auto" => $row["Auto"]);
  }
  mysql_free_result($result);
  $core->assign("dbprices", $dbprices);
}
elseif($action == "editdone" || $action == "payoutdone")
{
$columns = array(0 => "","","avg","median","","","","Bavg","Bmedian","","","","Savg","Smedian");

  if(@$_POST["submit"] != "Save")
    $core->Goto("index.php");

  foreach($_POST as $key => $value)
  {
    if(substr($key, 0, 4) == "item")
    {
      $id = substr($key, 4);
      $core->SQL("UPDATE operations_items SET `Price`='".str_replace(",", "", $value)."' WHERE id=".$id." LIMIT 1");
    }

    //Eve-Central Market Settings Data.
    if(substr($key, 0, 9) == "automacro")
    {
      $id = substr($key, 9);
      foreach($_POST as $key2 => $value2)
      {
          if(substr($key2, 0, 8) == "autotype")
          {
            $id2 = substr($key2, 8);
            if($id == $id2)
            {
              if($value == 100)
              {
                $core->SQL("UPDATE operations_items SET `Auto`=1 WHERE id=".$id." LIMIT 1");
              }
              else
              {
                $autovalue = $value+$value2;
                $core->SQL("UPDATE operations_items SET `Auto`=".$autovalue." WHERE id=".$id." LIMIT 1");

                $itemresult1 = $core->SQL("SELECT id, EveTypeID FROM operations_items WHERE id=".$id." LIMIT 1");
                while($row1 = mysql_fetch_assoc($itemresult1))
                {
                    $evetypeid = $row1['EveTypeID'];
                }

                $itemresult2 = $core->SQL("SELECT ".$columns[$autovalue]." FROM operations_marketdata WHERE EveTypeID=".$evetypeid." LIMIT 1");
                while($row2 = mysql_fetch_assoc($itemresult2))
                {
                    $select = $columns[$autovalue];
                    $newprice = round($row2[$select], 2);
                    $core->SQL("UPDATE operations_items SET `Price`=".$newprice." WHERE id=".$id." LIMIT 1");
                }
              }
            }
          }
      }
    }
  }

  if($action == "editdone")
  {
    $core->SQL("UPDATE operations_params SET `Value`='".gmdate("Y-m-d")."' WHERE `Name`='IndexDate'");
    $core->SQL("UPDATE operations_params SET `Value`='0' WHERE `Name`='IndexTime'");
  }
  // Item prices
  UpdatePrices();
  $core->Goto("index.php?result=1");
}
elseif($action == "refine")
{
  $result = $core->SQL("SELECT * FROM operations_params");
  while($row = mysql_fetch_assoc($result))
  {
    $core->assign($row["Name"], $row["Value"]);
  }
  mysql_free_result($result);
}
elseif($action == "refinedone")
{
  if(@$_POST["submit"] != "Save Parameters")
    $core->Goto("index.php");
    
  $refining = @$_POST["refining"];
  $refinery_efficiency = @$_POST["refinery_efficiency"];
  $ore_skills = @$_POST["ore_skills"];
  $ice_skill = @$_POST["ice_skill"];
  $refining_equipment = @$_POST["refining_equipment"];
  $station_standing = @$_POST["station_standing"];
  $station_tax = @$_POST["station_tax"];
  
  if(!is_numeric($refining_equipment)) $refining_equipment = 0;
  if(!is_numeric($station_standing)) $station_standing = 0;
  if(!is_numeric($station_tax)) $station_tax = '-1';
  
  $core->SQL("UPDATE operations_params SET `Value`=".$refining." WHERE `Name`='refining'");
  $core->SQL("UPDATE operations_params SET `Value`=".$refinery_efficiency." WHERE `Name`='refinery_efficiency'");
  $core->SQL("UPDATE operations_params SET `Value`=".$ore_skills." WHERE `Name`='ore_skills'");
  $core->SQL("UPDATE operations_params SET `Value`=".$ice_skill." WHERE `Name`='ice_skill'");
  $core->SQL("UPDATE operations_params SET `Value`=".$refining_equipment." WHERE `Name`='refining_equipment'");
  $core->SQL("UPDATE operations_params SET `Value`=".$station_standing." WHERE `Name`='station_standing'");
  $core->SQL("UPDATE operations_params SET `Value`=".$station_tax." WHERE `Name`='station_tax'");
  
  UpdatePrices();

  $core->Goto("index.php?result=2");
}
elseif($action == "operationalitems")
{
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');
    $result2 = $core->SQL("SELECT * FROM operations_items ORDER BY GroupID, Name ASC");
// id  EveTypeID  Name  GroupID   Price  DisplayOrder
    while($row = mysql_fetch_assoc($result2))
    {
        $manage_id[$row['id']] = $row['id'];
        $manage_EveTypeID[$row['id']] = $row['EveTypeID'];
        $manage_Name[$row['id']] = $row['Name'];
        $manage_GroupID[$row['id']] = $row['GroupID'];
        $manage_Price[$row['id']] = number_format(exp_to_dec($row['Price']), 2, '.', ',');
        $manage_DisplayOrder[$row['id']] = $row['DisplayOrder'];
    }
$core->assign('manage_id', $manage_id);
$core->assign('manage_EveTypeID', $manage_EveTypeID);
$core->assign('manage_Name', $manage_Name);
$core->assign('manage_GroupID', $manage_GroupID);
$core->assign('manage_Price', $manage_Price);
$core->assign('manage_DisplayOrder', $manage_DisplayOrder);
}
elseif($action == "newitem")
{
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');
  $result = $core->EveSQL("SELECT DISTINCT *  FROM `invMarketGroups` WHERE `graphicID` != 2703 AND `hasTypes` = 0 ORDER BY marketGroupName");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
      $items[] = array("EveTypeID" => $row["marketGroupID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["marketGroupName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]));
  }
  mysql_free_result($result);

$core->assign("items", $items);
$core->assign('action', $action);
}
elseif($action == "newitemp")
{
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');
$items = array();
  foreach($_POST as $key => $value)
  {
        if((substr($key, 0, 4) == "item") && ($value == "on"))
        {
            $pid = substr($key, 4);
//            echo $pid;
            $result = $core->EveSQL("SELECT t1.typeID, t1.typeName, t1.graphicID, t2.marketGroupName AS Race, t2.marketGroupName AS GroupName FROM invTypes AS t1 INNER JOIN invMarketGroups AS t2 ON t2.parentGroupID=$pid AND t2.marketGroupID=t1.marketGroupID ORDER BY GroupName ASC, Race ASC, typeName ASC");
            while($row = mysql_fetch_assoc($result))
            {
                $items[] = array("EveTypeID" => $row["typeID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["typeName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]));
            }
        }
  }
$core->assign("items", $items);
$core->assign('action', $action);
}
elseif($action == "newitempp")
{
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');
$items = array();
  foreach($_POST as $key => $value)
  {
        if((substr($key, 0, 4) == "item") && ($value == "on"))
        {
            $pid = substr($key, 4);
//            echo $pid;
//            echo "<br>";
            $result = $core->EveSQL("SELECT * FROM invTypes WHERE typeID = $pid");
            while($row = mysql_fetch_assoc($result))
            {
                $items[] = array("EveTypeID" => $row["typeID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["typeName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]));
            }
        }
  }
$core->assign("items", $items);
$core->assign('action', $action);
}
elseif($action == "newitemsave")
{
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');
$items = array();

  foreach($_POST as $key => $value)
  {
        if((substr($key, 0, 4) == "item"))
        {
            $pid = substr($key, 4);

            if($_POST["groupid".$pid] == "" || empty($_POST["groupid".$pid]) || $_POST["groupid".$pid] < 0) $_POST["groupid".$pid] = 0;
            if($_POST["displayorder".$pid] == "" || empty($_POST["displayorder".$pid]) || $_POST["displayorder".$pid] < 0) $_POST["displayorder".$pid] = 0;

                $itemname = $core->SQLEscape($_POST["iname".$pid]);
                $groupid = round($_POST["groupid".$pid]);
                $displayorder = round($_POST["displayorder".$pid]);

                $registered = $core->SQL("SELECT COUNT(*) FROM `operations_items` WHERE EveTypeID =".$pid);
                $total_records = mysql_num_rows($registered);

                if ($total_records <> 0)
                {
                    $core->SQL("DELETE FROM `operations_items` WHERE EveTypeID =".$pid);
                }
                // id  EveTypeID  Name  GroupID   Price  DisplayOrder
                $query_postinfo = "INSERT INTO `operations_items` (EveTypeID, Name, GroupID, DisplayOrder)
                    VALUES ($pid, '$itemname', $groupid, $displayorder)";
                $core->SQL($query_postinfo);
                $core->assign("items", $items);
                $core->assign('action', $action);
        }
  }
$url = "index.php?action=operationalitems";
$core->Goto($url);
}
elseif($action == "removeitem")
{
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');
if($template == 0) $core->Goto('index.php');
$registered = $core->SQL("SELECT COUNT(*) FROM `operations_items` WHERE id =".$template);
$total_records = mysql_num_rows($registered);

if ($total_records <> 0)
    {
    $core->SQL("DELETE FROM `operations_items` WHERE id =".$template);
    }

$url = "index.php?action=operationalitems&result=3";
$core->Goto($url);
}

$result = $core->SQL("SELECT * FROM `operations_groups` ORDER BY `DisplayOrder`, `GroupID`");
while($row = mysql_fetch_assoc($result))
 {
  $ogroupid[$row['id']] = $row['id'];
  $ogroupName[$row['id']] = $core->SQLUnEscape($row['Name']);
  $ogroupSubtext[$row['id']] = $core->SQLUnEscape($row['Subtext']);
  $ogroupGroupID[$row['id']] = $row['GroupID'];
  $ogroupDisplayOrder[$row['id']] = $row['DisplayOrder'];
  $ogroupCheckbox[$row['id']] = $row['Checkbox'];
 }
$core->assign("ogroupid", $ogroupid);
$core->assign("ogroupName", $ogroupName);
$core->assign("ogroupSubtext", $ogroupSubtext);
$core->assign("ogroupGroupID", $ogroupGroupID);
$core->assign("ogroupDisplayOrder", $ogroupDisplayOrder);
$core->assign("ogroupCheckbox", $ogroupCheckbox);

$core->assign("action", $action);
$core->assign("result", @$_GET["result"]);
$core->display($core->PlugInPath."payoutprices/unitprices.tpl");

function UpdatePrices()
{
  global $core;

  // Read refining parameters
  $result = $core->SQL("SELECT * FROM operations_params");
  $params = array();
  while($row = mysql_fetch_assoc($result))
  {
    $params[$row["Name"]] = $row["Value"];
  }
  mysql_free_result($result);
  
  $refining = (int)$params["refining"];
  $refinery_efficiency = (int)$params["refinery_efficiency"];
  $ore_skills = (int)$params["ore_skills"];
  $ice_skill = (int)$params["ice_skill"];
  $refining_equipment = (float)$params["refining_equipment"];
  $station_standing = (float)$params["station_standing"];
  $station_tax = (float)$params["station_tax"];

  // Tax
  if($station_tax == -1)
    $tax = max(0, 5 - $station_standing * 0.75);
  else
    $tax = $station_tax;
  $tax = min(100, $tax);

  // Read unit prices of building block materials
  $result = $core->SQL("SELECT Price FROM operations_items WHERE GroupID=0 ORDER BY id ASC");
  $base = array();
  while($row = mysql_fetch_assoc($result))
  {
    $base[] = $row["Price"];
  }
  mysql_free_result($result);
  $result = $core->SQL("SELECT Price FROM operations_items WHERE GroupID=1 ORDER BY id ASC");
  $icebase = array();
  while($row = mysql_fetch_assoc($result))
  {
    $icebase[] = $row["Price"];
  }
  mysql_free_result($result);
  
  // Ores
  $yield = ($refining_equipment / 100 + 0.375 * (1 + $refining * 0.02) * (1 + $refinery_efficiency * 0.04) * (1 + $ore_skills * 0.05)) * 100;
  $yield = min(100, $yield);
  $oreyield = ($yield / 100) * (100 - $tax) / 100;
  
  $rawores[] = array( 'Veldspar', 3.003, 0, 0, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Concentrated Veldspar', 3.15315, 0, 0, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Dense Veldspar', 3.3033, 0, 0, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Scordite', 2.5015, 1.24925, 0, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Condensed Scordite', 2.62658, 1.31171, 0, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Massive Scordite', 2.75165, 1.37417, 0, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Pyroxeres', 2.53453, 0.177177, 0.36036, 0, 0.033033, 0, 0, 0);
  $rawores[] = array( 'Solid Pyroxeres', 2.66126, 0.186036, 0.378378, 0, 0.0346847, 0, 0, 0);
  $rawores[] = array( 'Viscous Pyroxeres', 2.78799, 0.194895, 0.396396, 0, 0.0363363, 0, 0, 0);
  $rawores[] = array( 'Plagioclase', 0.768769, 1.53754, 0.768769, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Azure Plagioclase', 0.807207, 1.61441, 0.807207, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Rich Plagioclase', 0.845646, 1.69129, 0.845646, 0, 0, 0, 0, 0);
  $rawores[] = array( 'Omber', 0.614, 0.246, 0, 0.614, 0, 0, 0, 0);
  $rawores[] = array( 'Silvery Omber', 0.6447, 0.2583, 0, 0.6447, 0, 0, 0, 0);
  $rawores[] = array( 'Golden Omber', 0.6754, 0.2706, 0, 0.6754, 0, 0, 0, 0);
  $rawores[] = array( 'Kernite', 0.965, 0, 1.9325, 0.965, 0, 0, 0, 0);
  $rawores[] = array( 'Luminous Kernite', 1.01325, 0, 2.02912, 1.01325, 0, 0, 0, 0);
  $rawores[] = array( 'Fiery Kernite', 1.0615, 0, 2.12575, 1.0615, 0, 0, 0, 0);
  $rawores[] = array( 'Jaspet', 0.518, 0.518, 1.036, 0, 0.518, 0.016, 0, 0);
  $rawores[] = array( 'Pure Jaspet', 0.5439, 0.5439, 1.0878, 0, 0.5439, 0.0168, 0, 0);
  $rawores[] = array( 'Pristine Jaspet', 0.5698, 0.5698, 1.1396, 0, 0.5698, 0.0176, 0, 0);
  $rawores[] = array( 'Hemorphite', 0.424, 0, 0, 0.424, 0.848, 0.056, 0, 0);
  $rawores[] = array( 'Vivid Hemorphite', 0.4452, 0, 0, 0.4452, 0.8904, 0.0588, 0, 0);
  $rawores[] = array( 'Radiant Hemorphite', 0.4664, 0, 0, 0.4664, 0.9328, 0.0616, 0, 0);
  $rawores[] = array( 'Hedbergite', 0, 0, 0, 1.416, 0.708, 0.064, 0, 0);
  $rawores[] = array( 'Vitric Hedbergite', 0, 0, 0, 1.4868, 0.7434, 0.0672, 0, 0);
  $rawores[] = array( 'Glazed Hedbergite', 0, 0, 0, 1.5576, 0.7788, 0.0704, 0, 0);
  $rawores[] = array( 'Gneiss', 0.4275, 0, 0.4275, 0.8575, 0, 0.4275, 0, 0);
  $rawores[] = array( 'Iridescent Gneiss', 0.448875, 0, 0.448875, 0.900375, 0, 0.448875, 0, 0);
  $rawores[] = array( 'Prismatic Gneiss', 0.47025, 0, 0.47025, 0.94325, 0, 0.47025, 0, 0);
  $rawores[] = array( 'Dark Ochre', 0.625, 0, 0, 0, 1.25, 0.625, 0, 0);
  $rawores[] = array( 'Onyx Ochre', 0.65625, 0, 0, 0, 1.3125, 0.65625, 0, 0);
  $rawores[] = array( 'Obsidian Ochre', 0.6875, 0, 0, 0, 1.375, 0.6875, 0, 0);
  $rawores[] = array( 'Crokite', 1.324, 0, 0, 0, 1.324, 2.652, 0, 0);
  $rawores[] = array( 'Sharp Crokite', 1.3902, 0, 0, 0, 1.3902, 2.7846, 0, 0);
  $rawores[] = array( 'Crystalline Crokite', 1.4564, 0, 0, 0, 1.4564, 2.9172, 0, 0);
  $rawores[] = array( 'Spodumain', 2.8, 0.56, 0, 0, 0, 0, 0.56, 0);
  $rawores[] = array( 'Bright Spodumain', 2.94, 0.588, 0, 0, 0, 0, 0.588, 0);
  $rawores[] = array( 'Gleaming Spodumain', 3.08, 0.616, 0, 0, 0, 0, 0.616, 0);
  $rawores[] = array( 'Bistot', 0, 0.85, 0, 0, 0, 1.705, 0.85, 0);
  $rawores[] = array( 'Triclinic Bistot', 0, 0.8925, 0, 0, 0, 1.79025, 0.8925, 0);
  $rawores[] = array( 'Monoclinic Bistot', 0, 0.935, 0, 0, 0, 1.8755, 0.935, 0);
  $rawores[] = array( 'Arkonor', 1.2, 0, 0, 0, 0, 0.664, 1.332, 0);
  $rawores[] = array( 'Crimson Arkonor', 1.26, 0, 0, 0, 0, 0.6972, 1.3986, 0);
  $rawores[] = array( 'Prime Arkonor', 1.32, 0, 0, 0, 0, 0.7304, 1.4652, 0);
  $rawores[] = array( 'Mercoxit', 0, 0, 0, 0, 0, 0, 0, 2.12);
  $rawores[] = array( 'Vitreous Mercoxit', 0, 0, 0, 0, 0, 0, 0, 2.226);
  $rawores[] = array( 'Magma Mercoxit', 0, 0, 0, 0, 0, 0, 0, 2.332);
  
  foreach($rawores as $item)
  {
    $price = $oreyield * ($item[1] * $base[0] +
                          $item[2] * $base[1] +
                          $item[3] * $base[2] +
                          $item[4] * $base[3] +
                          $item[5] * $base[4] +
                          $item[6] * $base[5] +
                          $item[7] * $base[6] +
                          $item[8] * $base[7]);
    $core->SQL("UPDATE operations_items SET `Price`=".$price." WHERE `Name`='".$core->SQLEscape($item[0])."' LIMIT 1");
  }
  
  // Ice products
  $yield = ($refining_equipment / 100 + 0.375 * (1 + $refining * 0.02) * (1 + $refinery_efficiency * 0.04) * (1 + $ice_skill * 0.05)) * 100;
  $yield = min(100, $yield);
  $iceyield = ($yield / 100) * (100 - $tax) / 100;

  $iceores[] = array( 'Blue Ice', 50, 25, 1, 300, 0, 0, 0);
  $iceores[] = array( 'Thick Blue Ice', 75, 40, 1, 350, 0, 0, 0);
  $iceores[] = array( 'Krystallos', 100, 250, 100, 0, 0, 0, 0);
  $iceores[] = array( 'Glare Crust', 1000, 500, 25, 0, 0, 0, 0);
  $iceores[] = array( 'Gelidus', 250, 500, 75, 0, 0, 0, 0);
  $iceores[] = array( 'Dark Glitter', 500, 1000, 50, 0, 0, 0, 0);
  $iceores[] = array( 'Smooth Glacial Mass', 75, 40, 1, 0, 0, 350, 0);
  $iceores[] = array( 'Glacial Mass', 50, 25, 1, 0, 0, 300, 0);
  $iceores[] = array( 'Enriched Clear Icicle', 75, 40, 1, 0, 0, 0, 350);
  $iceores[] = array( 'Clear Icicle', 50, 25, 1, 0, 0, 0, 300);
  $iceores[] = array( 'Pristine White Glaze', 75, 40, 1, 0, 350, 0, 0);
  $iceores[] = array( 'White Glaze', 50, 25, 1, 0, 300, 0, 0);
  
  foreach($iceores as $item)
  {
    $price = $iceyield * ($item[1] * $icebase[0] +
                          $item[2] * $icebase[1] +
                          $item[3] * $icebase[2] +
                          $item[4] * $icebase[3] +
                          $item[5] * $icebase[4] +
                          $item[6] * $icebase[5] +
                          $item[7] * $icebase[6]);
    $core->SQL("UPDATE operations_items SET `Price`=".$price." WHERE `Name`='".$core->SQLEscape($item[0])."' LIMIT 1");
  }
  
  // Alloys and compouns
  $alloys[] = array( 'Condensed Alloy', 77, 38, 10, 0, 0, 0, 0, 0);
  $alloys[] = array( 'Crystal Compound', 0, 0, 34, 2, 0, 0, 0, 0);
  $alloys[] = array( 'Dark Compound', 0, 0, 0, 20, 9, 0, 0, 0);
  $alloys[] = array( 'Gleaming Alloy', 261, 0, 0, 0, 4, 0, 0, 0);
  $alloys[] = array( 'Glossy Compound', 0, 0, 0, 0, 3, 0, 5, 0);
  $alloys[] = array( 'Lucent Compound', 0, 152, 2, 10, 4, 0, 0, 0);
  $alloys[] = array( 'Lustering Alloy', 0, 0, 77, 9, 31, 1, 0, 0);
  $alloys[] = array( 'Motley Compound', 0, 0, 0, 24, 11, 0, 0, 0);
  $alloys[] = array( 'Opulent Compound', 0, 0, 0, 0, 0, 0, 0, 2);
  $alloys[] = array( 'Plush Compound', 2790, 698, 17, 0, 0, 8, 0, 0);
  $alloys[] = array( 'Precious Alloy', 0, 6, 0, 16, 0, 0, 0, 0);
  $alloys[] = array( 'Sheen Compound', 108, 38, 0, 20, 1, 0, 0, 0);
  
  foreach($alloys as $item)
  {
    $price = ($item[1] * $base[0] +
              $item[2] * $base[1] +
              $item[3] * $base[2] +
              $item[4] * $base[3] +
              $item[5] * $base[4] +
              $item[6] * $base[5] +
              $item[7] * $base[6] +
              $item[8] * $base[7]);
    $core->SQL("UPDATE operations_items SET `Price`=".$price." WHERE `Name`='".$core->SQLEscape($item[0])."' LIMIT 1");
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

function exp_to_dec($float_str)
// formats a floating point number string in decimal notation, supports signed floats, also supports non-standard formatting e.g. 0.2e+2 for 20
// e.g. '1.6E+6' to '1600000', '-4.566e-12' to '-0.000000000004566', '+34e+10' to '340000000000'
// Author: Bob
{
    // make sure its a standard php float string (i.e. change 0.2e+2 to 20)
    // php will automatically format floats decimally if they are within a certain range
    $float_str = (string)((float)($float_str));

    // if there is an E in the float string
    if(($pos = strpos(strtolower($float_str), 'e')) !== false)
    {
        // get either side of the E, e.g. 1.6E+6 => exp E+6, num 1.6
        $exp = substr($float_str, $pos+1);
        $num = substr($float_str, 0, $pos);

        // strip off num sign, if there is one, and leave it off if its + (not required)
        if((($num_sign = $num[0]) === '+') || ($num_sign === '-')) $num = substr($num, 1);
        else $num_sign = '';
        if($num_sign === '+') $num_sign = '';

        // strip off exponential sign ('+' or '-' as in 'E+6') if there is one, otherwise throw error, e.g. E+6 => '+'
        if((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) $exp = substr($exp, 1);
        else trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);

        // get the number of decimal places to the right of the decimal point (or 0 if there is no dec point), e.g., 1.6 => 1
        $right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos+1));
        // get the number of decimal places to the left of the decimal point (or the length of the entire num if there is no dec point), e.g. 1.6 => 1
        $left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));

        // work out number of zeros from exp, exp sign and dec places, e.g. exp 6, exp sign +, dec places 1 => num zeros 5
        if($exp_sign === '+') $num_zeros = $exp - $right_dec_places;
        else $num_zeros = $exp - $left_dec_places;

        // build a string with $num_zeros zeros, e.g. '0' 5 times => '00000'
        $zeros = str_pad('', $num_zeros, '0');

        // strip decimal from num, e.g. 1.6 => 16
        if($dec_pos !== false) $num = str_replace('.', '', $num);

        // if positive exponent, return like 1600000
        if($exp_sign === '+') return $num_sign.$num.$zeros;
        // if negative exponent, return like 0.0000016
        else return $num_sign.'0.'.$zeros.$num;
    }
    // otherwise, assume already in decimal notation and return
    else return $float_str;
}

?>