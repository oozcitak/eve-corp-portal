<?php
require_once('../../core/core.class.php');
$core = new Core();

$item = $_GET["item"];

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
$query .= "FROM TL2MaterialsForTypeWithActivity AS materials ";
$query .= "INNER JOIN invTypes AS typeReq ON materials.requiredtypeID = typeReq.typeID ";
$query .= "INNER JOIN invGroups AS typeGroup ON typeReq.groupID = typeGroup.groupID ";
$query .= "INNER JOIN invBlueprintTypes AS bluePrint ON materials.typeID = bluePrint.blueprintTypeID ";
$query .= "INNER JOIN eveGraphics AS graphics ON typeReq.graphicID = graphics.graphicID ";
$query .= "WHERE bluePrint.productTypeID=".$item." AND Quantity>0 AND materials.activity = 1 AND typeGroup.categoryID NOT IN (6, 7, 16) ORDER BY typeReq.marketGroupID, Name ASC";
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
$total = number_format($total, 2);

// Write the XML
header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";

echo "\n<Item>";
echo "\n\t<EveTypeID>".$item."</EveTypeID>";
echo "\n\t<TotalPrice>".$total."</TotalPrice>";
foreach($eveprices as $price)
{
  echo "\n\t<Material>";
  echo "\n\t\t<Name>".$price["Name"]."</Name>";
  echo "\n\t\t<Icon>".$price["Icon"]."</Icon>";
  echo "\n\t\t<Quantity>".$price["Quantity"]."</Quantity>";
  echo "\n\t\t<UnitPrice>".$price["UnitPrice"]."</UnitPrice>";
  echo "\n\t\t<TotalPrice>".$price["Cost"]."</TotalPrice>";
  echo "\n\t</Material>";
}
echo "\n</Item>";

?>
