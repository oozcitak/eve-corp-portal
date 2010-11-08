<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 1) $core->Goto('../../php/access.php');

$action = @$_GET["action"];
if(empty($action)) $action = "home";
if(isset($_GET["cancel"])) $action = "cancel";
if(isset($_GET["resubmit"])) $action = "resubmit";

if($action == "home")
{
  $names = $core->GetAllUserNames();
  $names[0] = "-";
  if($core->CurrentUser()->IsAlly)
    $result = $core->SQL("SELECT t1.id,t1.Date,t1.Count,t2.AlliancePrice AS Price,t1.Manager,t1.Status,t2.EveGraphicID,t2.GroupName,t2.Race,t2.Name FROM production_orders AS t1 INNER JOIN production_items AS t2 ON t1.Item=t2.id WHERE t1.Owner=".$core->CurrentUser()->ID." AND t1.IsDeleted=0 AND t1.Item!=0 AND t2.AlliancePrice!=0 ORDER BY t1.Date DESC LIMIT 50");
  else
    $result = $core->SQL("SELECT t1.id,t1.Date,t1.Count,t2.Price,t1.Manager,t1.Status,t2.EveGraphicID,t2.GroupName,t2.Race,t2.Name FROM production_orders AS t1 INNER JOIN production_items AS t2 ON t1.Item=t2.id WHERE t1.Owner=".$core->CurrentUser()->ID." AND t1.IsDeleted=0 AND t1.Item!=0 ORDER BY t1.Date DESC LIMIT 50");
  $orders = array();
  while($row = mysql_fetch_assoc($result))
  {
    $orders[] = array("ID" => $row["id"], "Cost" => number_format($row["Count"] * $row["Price"], 0), "Manager" => $names[$row["Manager"]], "Status" => StatusName($row["Status"]), "StatusID" => $row["Status"], "Price" => $row["Price"], "EveGraphicID" => $row["EveGraphicID"], "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]), "Name" => $core->SQLUnEscape($row["Name"]), "Count" => $row["Count"], "Date" => $core->GMTToLocal($row["Date"]));
  }
  mysql_free_result($result);
  $core->assign("orders", $orders);
}
elseif($action == "queue")
{
  $names = $core->GetAllUserNames();
  $names[0] = "-";
  $result = $core->SQL("SELECT t1.id,t1.Owner,t1.Date,t1.Count,t1.Manager,t1.Status,t2.EveGraphicID,t2.GroupName,t2.Race,t2.Name FROM production_orders AS t1 INNER JOIN production_items AS t2 ON t1.Item=t2.id WHERE t1.IsDeleted=0 AND t1.Item!=0 ORDER BY t1.Date DESC LIMIT 50");
  $orders = array();
  while($row = mysql_fetch_assoc($result))
  {
    $orders[] = array("ID" => $row["id"], "Owner" => $names[$row["Owner"]], "Manager" => $names[$row["Manager"]], "Status" => StatusName($row["Status"]), "EveGraphicID" => $row["EveGraphicID"], "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Race" => $core->SQLUnEscape($row["Race"]), "Name" => $core->SQLUnEscape($row["Name"]), "Count" => $row["Count"], "Date" => $core->GMTToLocal($row["Date"]));
  }
  mysql_free_result($result);
  $core->assign("orders", $orders);
}
elseif($action == "addship")
{
  if($core->CurrentUser()->IsAlly)
    $result = $core->SQL("SELECT id,Name,GroupName,AlliancePrice AS Price FROM production_items WHERE Type=0 AND AlliancePrice!=0 ORDER BY GroupName ASC, Race ASC, Name ASC");
  else
    $result = $core->SQL("SELECT id,Name,GroupName,Price FROM production_items WHERE Type=0 ORDER BY GroupName ASC, Race ASC, Name ASC");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
    $items[] = array("ID" => $row["id"], "Price" => number_format($row["Price"], 0), "Name" => $core->SQLUnEscape($row["Name"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Stockpile" => 0,);
  }
  mysql_free_result($result);
  $core->assign("items", $items);
}
elseif($action == "addrig")
{
  if($core->CurrentUser()->IsAlly)
    $result = $core->SQL("SELECT id,Name,GroupName,AlliancePrice AS Price FROM production_items WHERE Type=1 AND AlliancePrice!=0 ORDER BY GroupName ASC, Name ASC");
  else
    $result = $core->SQL("SELECT id,Name,GroupName,Price FROM production_items WHERE Type=1 ORDER BY GroupName ASC, Name ASC");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
    $items[] = array("ID" => $row["id"], "Price" => number_format($row["Price"], 0), "Name" => $core->SQLUnEscape($row["Name"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Stockpile" => 0,);
  }
  mysql_free_result($result);
  $core->assign("items", $items);
}
elseif($action == "store")
{
  if($core->CurrentUser()->IsAlly)
    $result = $core->SQL("SELECT id,Name,GroupName,AlliancePrice AS Price,Stock FROM production_items WHERE Type=2 AND AlliancePrice!=0 AND Stock !=0 ORDER BY GroupName ASC, Name ASC");
  else
    $result = $core->SQL("SELECT id,Name,GroupName,Price,Stock FROM production_items WHERE Type=2 AND Price!=0 AND Stock !=0 ORDER BY GroupName ASC, Name ASC");
  $items = array();
  while($row = mysql_fetch_assoc($result))
  {
    $items[] = array("ID" => $row["id"], "Price" => number_format($row["Price"], 0), "Name" => $core->SQLUnEscape($row["Name"]), "GroupName" => $core->SQLUnEscape($row["GroupName"]), "Stockpile" => $row["Stock"],);
  }
  mysql_free_result($result);
  $core->assign("items", $items);
}
elseif($action == "adddone")
{
  $id = $_POST["item"];
  $count = $_POST["count"];
  $notes = $_POST["notes"];
  $priority = $_POST["priority"];
  if(is_numeric($count) && ($count > 0))
  {
    $query = "INSERT INTO production_orders (Date,Owner,Item,Count,Notes,IsAlly,Priority) VALUES (";
    $query .= "'".$core->GMTTime()."',";
    $query .= $core->CurrentUser()->ID.",";
    $query .= $id.",";
    $query .= $count.",";
    $query .= "'".$core->SQLEscape($notes)."',";
    $query .= ($core->CurrentUser()->IsAlly ? 1 : 0).",";
    $query .= $priority.")";
    $core->SQL($query);
  }
  $core->Goto("index.php");
}
elseif($action == "addmisc")
{
  $names = $core->GetAllUserNames();
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
elseif($action == "addmiscdone")
{
  $notes = $_POST["notes"];
  if(!empty($notes))
  {
    $query = "INSERT INTO production_orders (Date,Owner,Item,Count,Notes) VALUES (";
    $query .= "'".$core->GMTTime()."',";
    $query .= $core->CurrentUser()->ID.",";
    $query .= "0,";
    $query .= "0,";
    $query .= "'".$core->SQLEscape($notes)."')";
    $core->SQL($query);
  }
  $core->Goto("index.php");
}
elseif($action == "cancel")
{
  $id = $_GET["cancel"];
  $core->SQL("UPDATE production_orders SET IsDeleted=1 WHERE id=".$id." LIMIT 1");
  $core->Goto("index.php");
}
elseif($action == "resubmit")
{
  $id = $_GET["resubmit"];
  $core->SQL("UPDATE production_orders SET Status=0 WHERE id=".$id." LIMIT 1");
  $core->Goto("index.php");
}
elseif($action == "help" || $action == "edithelp")
{
  $helptext = file_get_contents("help.html");
  $core->assign("helptext", $helptext);
}
elseif($action == "edithelpdone")
{
  if($_POST["submit"] == "Save")
  {
    $helptext = $_POST["helptext"];
    file_put_contents("help.html", $helptext);
  }
  $core->Goto("index.php?action=help");
}

$core->assign('action', $action);
$core->display($core->PlugInPath."productionorders/productionorders.tpl");

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

?>