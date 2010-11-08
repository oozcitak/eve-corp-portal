<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 2) $core->Goto('../../php/access.php');

$action = @$_GET["action"];

if(isset($_GET["edit"]) || isset($_GET["view"]))
{
  if(isset($_GET["edit"]))
  {
    $id = $_GET["edit"];
    $action = "edit";
  }
  else
  {
    $id = $_GET["view"];
    $action = "view";
  }    
  
  $result = $core->SQL("SELECT * FROM operations_submissions WHERE id=".$id." LIMIT 1");
  $row = mysql_fetch_assoc($result);
  
  $canedit = (($row["Leader"] == $core->CurrentUser()->ID) || in_array($core->CurrentUser()->ID, explode(",", $row["Players"])));
  // Op Status
  // 0 - New
  // 1 - Resubmitted
  // 2 - Canceled
  // 3 - Rejected
  // 4 - Paid
  if($row["Status"] == 4)
    $canedit = false;
  if($core->IsIGB())
    $canedit = false;  
  
  if($core->CurrentUser()->AccessRight() < 3)
  {
    if(($action == "edit") && (!$canedit))
      $core->Goto("index.php");
  }
   
  $names = $core->GetAllUserNames();
  $playernames = array();
  $players = explode(",", $row["Players"]);
  foreach($players as $player)
    $playernames[$player] = $names[$player];
  
  $timeins = array();
  $times = explode(",", $row["TimeIns"]);
  foreach($times as $time)
  {
    $val = explode("=", $time);
    $timeins[$val[0]] = $val[1];
  }
  $timeouts = array();
  $times = explode(",", $row["TimeOuts"]);
  foreach($times as $time)
  {
    $val = explode("=", $time);
    $timeouts[$val[0]] = $val[1];
  }
  
  $items = array();
  $itemsdb = explode(",", $row["Items"]);
  foreach($itemsdb as $item)
  {
    $val = explode("=", $item);
    $items[$val[0]] = $val[1];
  }
  $op = array("ID" => $row["id"], "Leader" => $names[$row["Leader"]], "Players" => $players, "PlayerNames" => $playernames, "Date" => $core->GMTToLocal($row["Date"]), "OpDate" => $row["OpDate"], "Status" => $row["Status"], "RejectReason" => $core->SQLUnEscape($row["RejectReason"]), "Notes" => $core->SQLUnEscape($row["Notes"]), "TimeIns" => $timeins, "TimeOuts" => $timeouts, "Items" => $items, "CanEdit" => $canedit);
  
  // All items
  $result = $core->SQL("SELECT `id`, `Name`, `GroupID` FROM operations_items ORDER BY `GroupID` ASC, `DisplayOrder` ASC, `Name` ASC");
  $allitems = array();
  while($row = mysql_fetch_assoc($result))
  {
    $quantity = "";
    if(isset($items[$row["id"]]))
      $quantity = $items[$row["id"]];
    
    if(($action == "edit") || (($action == "view") && !empty($quantity)))
      $allitems[] = array($row["id"], $row["GroupID"], $core->SQLUnEscape($row["Name"]), $quantity);
  }
  
  $core->assign("items", $allitems);
  $core->assign("names", $names);
  $core->assign("op", $op);
}
elseif(isset($_GET["cancel"]))
{
  $id = $_GET["cancel"];
  
  $result = $core->SQL("SELECT Leader, Players FROM operations_submissions WHERE id=".$id." LIMIT 1");
  $row = mysql_fetch_assoc($result);
  $canedit = (($row["Leader"] == $core->CurrentUser()->ID) || in_array($core->CurrentUser()->ID, explode(",", $row["Players"])));
  
  if($canedit)
    $core->SQL("UPDATE operations_submissions SET Status=5 WHERE id=".$id." LIMIT 1");
  
  $core->Goto("index.php");
}
elseif($action == "editdone")
{
  $id = $_POST["id"];
  $timeins = array();
  $timeouts = array();
  $items = array();
  foreach($_POST as $key => $value)
  {
    if(substr($key, 0, 6) == "timein")
      $timeins[substr($key, 6)] = $value;
    if(substr($key, 0, 7) == "timeout")
      $timeouts[substr($key, 7)] = $value;
    if((substr($key, 0, 4) == "item") && !empty($value) && (intval($value) > 0))
      $items[substr($key, 4)] = intval($value);
  }
  ksort($timeins);
  ksort($timeouts);
  ksort($items);
  $players = array();
  $timeinsdb = array();
  $timeoutsdb = array();
  $itemsdb = array();
  foreach($timeins as $key => $value)
  {
    $players[] = $key;
    $timeinsdb[] = $key."=".$value;
    $timeoutsdb[] = $key."=".$timeouts[$key];
  }
  foreach($items as $key => $value)
  {
    $itemsdb[] = $key."=".$value;
  }
  $notes = $_POST["notes"];
  $notes = "Edited on ".$core->GMTTime()." GMT by ".$core->CurrentUser()->Name."<br />".$notes;
  // Insert into DB
  $query = "UPDATE operations_submissions SET ";
  $query .= "Status=1,";
  $query .= "Players='".implode(",", $players)."',";
  $query .= "TimeIns='".implode(",", $timeinsdb)."',";
  $query .= "TimeOuts='".implode(",", $timeoutsdb)."',";
  $query .= "Items='".implode(",", $itemsdb)."',";
  $query .= "Notes='".$core->SQLEscape($notes)."' WHERE id=".$id." LIMIT 1";
  $core->SQL($query);
  $core->Goto("index.php");
}
else
{
  $action = "home";
  $names = $core->GetAllUserNames();
  $result = $core->SQL("SELECT id, Leader, Players, Date, OpDate, Status FROM operations_submissions WHERE Status!=5 ORDER BY Date DESC, Date DESC LIMIT 50");
  $ops = array();
  while($row = mysql_fetch_assoc($result))
  {
    $players = array();
    $playersdb = explode(",", $row["Players"]);
    foreach($playersdb as $player)
      $players[] = $names[$player];
    $canedit = (($row["Leader"] == $core->CurrentUser()->ID) || in_array($core->CurrentUser()->ID, $playersdb));
    // Op Status
    // 0 - New
    // 1 - Resubmitted
    // 2 - Canceled
    // 3 - Rejected
    // 4 - Paid
    // 5 - Deleted
    if($row["Status"] == 4)
      $canedit = false;
    if($core->IsIGB())
      $canedit = false;
    $ops[] = array("ID" => $row["id"], "Leader" => $names[$row["Leader"]], "Players" => implode(",", $players), "Date" => $core->GMTToLocal($row["Date"]), "OpDate" => $row["OpDate"], "Status" => $row["Status"], "CanEdit" => $canedit);
  }
  $core->assign("ops", $ops);
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
$core->display($core->PlugInPath."payoutview/payoutview.tpl");
?>