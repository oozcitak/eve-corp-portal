<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 3) $core->Goto('../../php/access.php');

$action = @$_GET["action"];
$core->assign('result', 0);
if(empty($action)) $action = "home";
if($action == "summary") $action = "summary";
if(isset($_GET["delete"])) $action = "delete";
if(isset($_GET["result"])) $core->assign('result', $_GET["result"]);

if($action == "home")
{
  $names = $core->GetAllUserNames();
  $names[0] = "-";
  $result = $core->SQL("SELECT t1.id,t1.Notes,t1.Owner,t1.Date,t1.Priority,t1.Count,t1.IsAlly,t2.Price,t2.AlliancePrice,t1.Manager,t1.Status,t2.EveGraphicID,t2.GroupName,t2.Race,t2.Name FROM production_orders AS t1 INNER JOIN production_items AS t2 ON t1.Item=t2.id WHERE t1.IsDeleted=0 ORDER BY t1.Priority DESC, t1.Date ASC");
  $orders = array();
  $total = 0;
  while($row = mysql_fetch_assoc($result))
  {
    $price = ($row["IsAlly"] ? $row["AlliancePrice"] : $row["Price"]);
    $orders[] = array("ID" => $row["id"], "Priority" => PriorityName($row["Priority"]), "Notes" => $core->SQLUnEscape($row["Notes"]), "Owner" => $names[$row["Owner"]], "IsAlly" => $row["IsAlly"], "Manager" => $names[$row["Manager"]], "Status" => StatusName($row["Status"]), "EveGraphicID" => $row["EveGraphicID"], "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]), "Name" => $core->SQLUnEscape($row["Name"]), "Count" => $row["Count"], "Price" => number_format($price, 0), "Cost" => number_format($row["Count"] * $price, 0), "Date" => $core->GMTToLocal($row["Date"]));
    $total += $row["Count"] * $price;
  }
  mysql_free_result($result);
  $core->assign("orders", $orders);
  $core->assign("total", number_format($total, 0));
  // Misc orders
  $result = $core->SQL("SELECT id,Owner,Date,Notes FROM production_orders WHERE IsDeleted=0 AND Item=0 ORDER BY Date DESC");
  $misc = array();
  $total = 0;
  while($row = mysql_fetch_assoc($result))
  {
    $misc[] = array("ID" => $row["id"], "Notes" => $core->SQLUnEscape($row["Notes"]), "Owner" => $names[$row["Owner"]], "Date" => $core->GMTToLocal($row["Date"]));
  }
  mysql_free_result($result);
  $core->assign("misc", $misc);
}
elseif($action == "summary")
{
  $result = $core->SQL("SELECT t1.id,t1.Notes,t1.Owner,t1.Date,t1.Priority,t1.Count,t1.IsAlly,t2.Price,t2.AlliancePrice,t1.Manager,t1.Status,t2.EveGraphicID,t2.GroupName,t2.Race,t2.Name FROM production_orders AS t1 INNER JOIN production_items AS t2 ON t1.Item=t2.id WHERE t1.IsDeleted=0 AND t1.Status=0 ORDER BY t1.Priority DESC, t1.Date ASC");
  while($row = mysql_fetch_assoc($result))
  {
    $glance[$row["Name"]] = $glance[$row["Name"]] + $row["Count"];
  }
  mysql_free_result($result);
  $core->assign("glance", $glance);
}
elseif($action == "change")
{
  $ids = array();
  foreach($_POST as $key => $value)
  {
    if((substr($key, 0, 4) == "item") && ($value == "on"))
      $ids[] = substr($key, 4);
  }
  $status = $_POST["status"];
  $core->SQL("UPDATE production_orders SET Status=".$status.",Manager=".$core->CurrentUser()->ID." WHERE FIND_IN_SET(id, '".implode(",", $ids)."')");
  $core->Goto("index.php");
}
elseif($action == "delete")
{
  $id = $_GET["delete"];
  $result = $core->SQL("SELECT id,Status,Count FROM production_orders WHERE id=$id");
  while($row = mysql_fetch_assoc($result))
  {
    $confirmstatus = $row["Status"];
    $confirmcount = $row["Count"];
  }
  mysql_free_result($result);
  if ($confirmstatus == 4 || $confirmstatus == 6 || $confirmcount == 0)
  {
    $core->SQL("UPDATE production_orders SET IsDeleted=1 WHERE id=".$id." LIMIT 1");
    $core->Goto("index.php");
  }
  else
  {
    $core->Goto("index.php?result=1");
  }
}

$core->assign('action', $action);
$core->display($core->PlugInPath."productionmanagement/productionmanagement.tpl");

function StatusName($status)
{
  // Status
  // 0 - New Order
  // 1 - Need BPC
  // 2 - Need Materials
  // 3 - Producing
  // 4 - Contracted
  // 5 - Paid
  // 6 - Rescinded
  // 7 - Producing < 7 Days
  // 8 - Producing < 14 Days
  // 9 - Producing < 21 Days
  // 10 - Queued Unk Eta

  $names = array("New Order", "Need BPC", "Need Materials", "Producing", "Contracted", "Paid", "Rescinded", "Producing < 7 Days", "Producing < 14 Days", "Producing < 21 Days", "Queued Unk Eta");
  return $names[$status];
}

function PriorityName($priority)
{
  // Status
  // 0 - Low
  // 5 - Normal
  // 10 - High
  if($priority == 0)
    return "Low";
  elseif($priority == 10)
    return "High";
  else
    return "Normal";
}

?>