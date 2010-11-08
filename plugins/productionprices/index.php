<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 3) $core->Goto('../../php/access.php');

$action = @$_GET["action"];
if(empty($action)) $action = "home";
if(isset($_GET["delete"])) $action = "delete";
if(isset($_GET["edit"])) $action = "edit";

if($action == "home" || $action == "homeships" || $action == "homerigs")
{
  $itemids = array();
  // Read ship prices
  if($action == "homeships" || $action == "home")
  {
      $result = $core->SQL("SELECT * FROM production_items WHERE Type=0 ORDER BY GroupName ASC, Race ASC, `Name` ASC");
      $dbprices = array();
      while($row = mysql_fetch_assoc($result))
      {
        $dbprices[] = array("ID" => $row["id"], "EveTypeID" => $row["EveTypeID"], "EveGraphicID" => $row["EveGraphicID"], "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]), "Name" => $core->SQLUnEscape($row["Name"]), "Price" => number_format($row["Price"], 0), "AlliancePrice" => number_format($row["AlliancePrice"], 0));
        $itemids[] = $row["EveTypeID"];
      }
      mysql_free_result($result);
      $core->assign("shipprices", $dbprices);
  }
  // Read rig prices
  if($action == "homerigs" || $action == "home")
  {
      $result = $core->SQL("SELECT * FROM production_items WHERE Type=1 ORDER BY GroupName ASC, `Name` ASC");
      $dbprices = array();
      while($row = mysql_fetch_assoc($result))
      {
        $dbprices[] = array("ID" => $row["id"], "EveTypeID" => $row["EveTypeID"], "EveGraphicID" => $row["EveGraphicID"], "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]), "Name" => $core->SQLUnEscape($row["Name"]), "Price" => number_format($row["Price"], 0), "AlliancePrice" => number_format($row["AlliancePrice"], 0));
        $itemids[] = $row["EveTypeID"];
      }
      mysql_free_result($result);
      $core->assign("rigprices", $dbprices);
  }
  // Read material prices
  $result = $core->SQL("SELECT EveTypeID,Price FROM operations_items");
  $itemprices = array();
  while($row = mysql_fetch_assoc($result))
  {
    $itemprices[$row["EveTypeID"]] = (float)$row["Price"];
  }
  mysql_free_result($result);
  // Read material quantites from EVE DB
  $query = "SELECT bluePrint.productTypeID AS EveItemID, typeReq.typeID AS EveTypeID, IF(typeReq.groupID = 332, materials.quantity, CEIL(materials.quantity*(1+bluePrint.wasteFactor/100))) AS Quantity ";
  $query .= "FROM typeActivityMaterials AS materials ";
  $query .= "INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID ";
  $query .= "INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID ";
  $query .= "INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID ";
  $query .= "WHERE FIND_IN_SET(bluePrint.productTypeID,'".implode(",", $itemids)."') AND Quantity>0 AND materials.activityID = 1 AND typeGroup.categoryID NOT IN (6, 7, 16) ORDER BY EveItemID";
  $result = $core->EveSQL($query);
  $eveprices = array();
  while($row = mysql_fetch_assoc($result))
  {
    if(isset($itemprices[$row["EveTypeID"]]))
      $price = $itemprices[$row["EveTypeID"]] * (float)$row["Quantity"];
    else
      $price = 0;
    if(isset($eveprices[$row["EveItemID"]]))
      $eveprices[$row["EveItemID"]] += $price;
    else
      $eveprices[$row["EveItemID"]] = $price;
  }
  mysql_free_result($result);
  foreach($eveprices as &$eveprice)
  {
    $eveprice = number_format($eveprice, 0);
  }
  $core->assign("eveprices", $eveprices);
}
elseif($action == "addship")
{
  $result = $core->SQL("SELECT EveTypeID FROM production_items");
  $existing = array();
  while($row = mysql_fetch_assoc($result))
  {
    $existing[] = $row["EveTypeID"];
  }
  mysql_free_result($result);
  $result = $core->EveSQL("SELECT t1.typeID, t1.typeName, t1.graphicID, t2.marketGroupName AS Race, t3.marketGroupName AS GroupName FROM invTypes AS t1 INNER JOIN invMarketGroups AS t2 ON t1.marketGroupID=t2.marketGroupID INNER JOIN invMarketGroups AS t3 ON t2.parentGroupID=t3.marketGroupID WHERE t3.parentGroupID=4 ORDER BY GroupName ASC, Race ASC, typeName ASC");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
    if(!in_array($row["typeID"], $existing))
      $items[] = array("EveTypeID" => $row["typeID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["typeName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]));
  }
  mysql_free_result($result);
  $core->assign("items", $items);
}
elseif($action == "addrig")
{
  $result = $core->SQL("SELECT EveTypeID FROM production_items");
  $existing = array();
  while($row = mysql_fetch_assoc($result))
  {
    $existing[] = $row["EveTypeID"];
  }
  mysql_free_result($result);
  $result = $core->EveSQL("SELECT t1.typeID, t1.typeName, t1.graphicID, t2.marketGroupName AS Race, t3.marketGroupName AS GroupName FROM invTypes AS t1 INNER JOIN invMarketGroups AS t2 ON t1.marketGroupID=t2.marketGroupID INNER JOIN invMarketGroups AS t3 ON t2.parentGroupID=t3.marketGroupID WHERE t3.parentGroupID=1111 ORDER BY GroupName ASC, Race ASC, typeName ASC");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
    if(!in_array($row["typeID"], $existing))
      $items[] = array("EveTypeID" => $row["typeID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["typeName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => "");
  }
  mysql_free_result($result);
  $core->assign("items", $items);
}
elseif($action == "addrigbackend")
{
  $result = $core->SQL("SELECT EveTypeID FROM production_items");
  $existing = array();
  while($row = mysql_fetch_assoc($result))
  {
    $existing[] = $row["EveTypeID"];
  }
  mysql_free_result($result);
  $result = $core->EveSQL("SELECT t1.typeID, t1.typeName, t1.graphicID, t2.marketGroupName AS Race, t3.marketGroupName AS GroupName FROM invTypes AS t1 INNER JOIN invMarketGroups AS t2 ON t1.marketGroupID=t2.marketGroupID INNER JOIN invMarketGroups AS t3 ON t2.parentGroupID=t3.marketGroupID WHERE t3.parentGroupID=1111 ORDER BY GroupName ASC, Race ASC, typeName ASC");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
   $query = "INSERT INTO production_items (EveTypeID,EveGraphicID,Name,Price,AlliancePrice,GroupName,Type) VALUES (";
   $query .= $row["typeID"].",";
   $query .= $row["graphicID"].",";
   $query .= "'".$core->SQLEscape($row["typeName"])."',";
   $query .= "0,";
   $query .= "0,";
   $query .= "'".$core->SQLEscape($row["GroupName"])."',1)";
   $core->SQL($query);
  }
  $core->Goto('index.php');
}
elseif($action == "addshipdone")
{
  $id = $_POST["item"];
  $price = $_POST["price"];
  $allyprice = $_POST["allyprice"];
  $result = $core->EveSQL("SELECT t1.typeID, t1.typeName, t1.graphicID, t2.marketGroupName AS Race, t3.marketGroupName AS GroupName FROM invTypes AS t1 INNER JOIN invMarketGroups AS t2 ON t1.marketGroupID=t2.marketGroupID INNER JOIN invMarketGroups AS t3 ON t2.parentGroupID=t3.marketGroupID WHERE t1.typeID=".$id);
  $row = mysql_fetch_assoc($result);
  $item = array("EveTypeID" => $row["typeID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["typeName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]));
  mysql_free_result($result);
  $query = "INSERT INTO production_items (EveTypeID,EveGraphicID,Name,Price,AlliancePrice,GroupName,Race,Type) VALUES (";
  $query .= $item["EveTypeID"].",";
  $query .= $item["EveGraphicID"].",";
  $query .= "'".$core->SQLEscape($item["Name"])."',";
  $query .= $price.",";
  $query .= $allyprice.",";
  $query .= "'".$core->SQLEscape($item["GroupName"])."',";
  $query .= "'".$core->SQLEscape($item["Race"])."',0)";
  $core->SQL($query);
  $core->Goto("index.php");
}
elseif($action == "addrigdone")
{
  $id = $_POST["item"];
  $price = $_POST["price"];
  $allyprice = $_POST["allyprice"];
  $result = $core->EveSQL("SELECT t1.typeID, t1.typeName, t1.graphicID, t2.marketGroupName AS GroupName FROM invTypes AS t1 INNER JOIN invMarketGroups AS t2 ON t1.marketGroupID=t2.marketGroupID WHERE t1.typeID=".$id);
  $row = mysql_fetch_assoc($result);
  $item = array("EveTypeID" => $row["typeID"], "EveGraphicID" => $row["graphicID"], "Name" => $core->SQLUnEscape($row["typeName"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => "");
  mysql_free_result($result);
  $query = "INSERT INTO production_items (EveTypeID,EveGraphicID,Name,Price,AlliancePrice,GroupName,Type) VALUES (";
  $query .= $item["EveTypeID"].",";
  $query .= $item["EveGraphicID"].",";
  $query .= "'".$core->SQLEscape($item["Name"])."',";
  $query .= $price.",";
  $query .= $allyprice.",";
  $query .= "'".$core->SQLEscape($item["GroupName"])."',1)";
  $core->SQL($query);
  $core->Goto("index.php");
}
elseif($action == "changebypercent")
{
    $corppercent = (int) $_POST["input_corppercent"];
    $allypercent = (int) $_POST["input_allypercent"];

    if(gettype($corppercent) == "integer" and $corppercent <> 0)
    {
        foreach($_POST as $key => $value)
        {
            if((substr($key, 0, 4) == "item") && ($value == "on"))
            {
                $pid = substr($key, 4);
                changeprice($pid, $corppercent, 0);
            }
        }
    }

    if(gettype($allypercent) == "integer" and $allypercent <> 0)
    {
        foreach($_POST as $key => $value)
        {
            if((substr($key, 0, 4) == "item") && ($value == "on"))
            {
                $pid = substr($key, 4);
                changeprice($pid, $allypercent, 1);
            }
        }
    }


}
elseif($action == "delete")
{
  $id = $_GET["delete"];
  $core->SQL("DELETE FROM production_items WHERE id=".$id." LIMIT 1");
  $core->Goto("index.php");
}
elseif($action == "edit")
{
  $id = $_GET["edit"];
  $result = $core->SQL("SELECT EveTypeID,Name,Price,AlliancePrice,GroupName FROM production_items WHERE id=".$id);
  $row = mysql_fetch_assoc($result);
	$eveid = $row["EveTypeID"];
  $core->assign("id", $id);
  $core->assign("name", $core->SQLUnEscape($row["Name"]));
  $core->assign("groupname", $core->SQLUnEscape($row["GroupName"]));
  $core->assign("price", number_format($row["Price"], 0, '.', ''));
  $core->assign("allyprice", number_format($row["AlliancePrice"], 0, '.', ''));

  // Read material prices
  $result = $core->SQL("SELECT EveTypeID,Price FROM operations_items");
  $itemprices = array();
  while($row = mysql_fetch_assoc($result))
  {
    $itemprices[$row["EveTypeID"]] = (float)$row["Price"];
  }
  mysql_free_result($result);
  // Read material quantites from EVE DB
  $query = "SELECT graphics.icon AS Icon, typeReq.typeName as Name, typeReq.typeID AS EveTypeID, IF(typeReq.groupID = 332, materials.quantity, CEIL(materials.quantity*(1+bluePrint.wasteFactor/100))) AS Quantity ";
  $query .= "FROM typeActivityMaterials AS materials ";
  $query .= "INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID ";
  $query .= "INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID ";
  $query .= "INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID ";
	$query .= "INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID ";
  $query .= "WHERE bluePrint.productTypeID = ".$eveid." AND Quantity>0 AND materials.activityID = 1 AND typeGroup.categoryID NOT IN (6, 7, 16) ORDER BY typeReq.marketGroupID, Name ASC";
  $result = $core->EveSQL($query);
  $eveprices = array();
	$total = 0;
  while($row = mysql_fetch_assoc($result))
  {
    if(isset($itemprices[$row["EveTypeID"]]))
		{
			$unit = $itemprices[$row["EveTypeID"]];
      $price = $itemprices[$row["EveTypeID"]] * (float)$row["Quantity"];
	  }
    else
		{
			$unit = 0;
      $price = 0;
	  }
		$total += $price;
    $eveprices[] = array("UnitPrice" => number_format($unit, 2), "Quantity" => number_format($row["Quantity"], 0), "Cost" => number_format($price, 0), "EveTypeID" => $row["EveTypeID"], "Name" => $core->SQLUnEscape($row["Name"]), "Icon" => $row["Icon"]);
  }
  mysql_free_result($result);
  $core->assign("eveprices", $eveprices);
  $core->assign("totaleveprice", number_format($total));
}
elseif($action == "editdone")
{
  if($_POST["submit"] == "Save")
  {
    $id = $_POST["id"];
    $price = $_POST["price"];
    $allyprice = $_POST["allyprice"];
    $core->SQL("UPDATE production_items SET Price=".$price.",AlliancePrice=".$allyprice." WHERE id=".$id." LIMIT 1");
  }
  $core->Goto("index.php");
}

$core->assign('action', $action);
$core->display($core->PlugInPath."productionprices/productionprices.tpl");



 // *************************************************************************
 // Update's the Production Item's Price in the "production_items" database
 // *************************************************************************
function changeprice($id, $percent, $group)
{
global $core;

//  $id = $_GET["edit"];
  $result = $core->SQL("SELECT EveTypeID,Name,Price,AlliancePrice,GroupName FROM production_items WHERE id=".$id);
  $row = mysql_fetch_assoc($result);
	$eveid = $row["EveTypeID"];
  $core->assign("id", $id);
  $core->assign("name", $core->SQLUnEscape($row["Name"]));
  $core->assign("groupname", $core->SQLUnEscape($row["GroupName"]));
  $core->assign("price", number_format($row["Price"], 0, '.', ''));
  $core->assign("allyprice", number_format($row["AlliancePrice"], 0, '.', ''));

  // Read material prices
  $result = $core->SQL("SELECT EveTypeID,Price FROM operations_items");
  $itemprices = array();
  while($row = mysql_fetch_assoc($result))
  {
    $itemprices[$row["EveTypeID"]] = (float)$row["Price"];
  }
  mysql_free_result($result);
  // Read material quantites from EVE DB
  $query = "SELECT graphics.icon AS Icon, typeReq.typeName as Name, typeReq.typeID AS EveTypeID, IF(typeReq.groupID = 332, materials.quantity, CEIL(materials.quantity*(1+bluePrint.wasteFactor/100))) AS Quantity ";
  $query .= "FROM typeActivityMaterials AS materials ";
  $query .= "INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID ";
  $query .= "INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID ";
  $query .= "INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID ";
	$query .= "INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID ";
  $query .= "WHERE bluePrint.productTypeID = ".$eveid." AND Quantity>0 AND materials.activityID = 1 AND typeGroup.categoryID NOT IN (6, 7, 16) ORDER BY typeReq.marketGroupID, Name ASC";
  $result = $core->EveSQL($query);
  $eveprices = array();
	$total = 0;
  while($row = mysql_fetch_assoc($result))
  {
    if(isset($itemprices[$row["EveTypeID"]]))
		{
			$unit = $itemprices[$row["EveTypeID"]];
      $price = $itemprices[$row["EveTypeID"]] * (float)$row["Quantity"];
	  }
    else
		{
			$unit = 0;
      $price = 0;
	  }
		$total += $price;
    $eveprices[] = array("UnitPrice" => number_format($unit, 2), "Quantity" => number_format($row["Quantity"], 0), "Cost" => number_format($price, 0), "EveTypeID" => $row["EveTypeID"], "Name" => $core->SQLUnEscape($row["Name"]), "Icon" => $row["Icon"]);
  }
  mysql_free_result($result);
  $totaldifference = ($percent / 100) * $total;
  $total = round($total + $totaldifference, -5);

  if($group == 0)
  {
    $core->SQL("UPDATE production_items SET Price=".$total." WHERE id=".$id." LIMIT 1");
  }
  elseif($group == 1)
  {
    $core->SQL("UPDATE production_items SET AlliancePrice=".$total." WHERE id=".$id." LIMIT 1");
  }
  else{ }
}
?>