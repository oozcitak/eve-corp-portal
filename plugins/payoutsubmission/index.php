<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 2) $core->Goto('../../php/access.php');

$action = @$_GET["action"];
if(empty($action)) $action = "home";

$names = $core->GetAllUserNames();
$core->assign("names", $names);

if($action == "times" || (@$_POST["submit"] == "Add Player") || (substr(@$_POST["submit"], 0, 13) == "Remove Player"))
{
  $result = $core->SQL("SELECT Distinct `GroupID` FROM `operations_items` Order By `GroupID`");
  while($row = mysql_fetch_assoc($result))
  {
    $var = "group".$row['GroupID'];
    $core->assign($var, @$_POST[$var]);
    $groupnumber[$row['GroupID']] = @$_POST["group".$row['GroupID']];
  }
  $core->assign("groupnumber", $groupnumber);

  $opdate = @$_POST["opdate"];
  if(empty($opdate)) $opdate = gmdate("Y-m-d");
  $core->assign("opdate", $opdate);

  $count = @$_POST["count"];
  $players = array();
  if(empty($count)) $count = 0;
  for($i = 1; $i <= $count; $i++)
    $players[] = array($_POST["playerid".$i], $names[$_POST["playerid".$i]], $_POST["timein".$i], $_POST["timeout".$i]);

  if(@$_POST["submit"] == "Add Player")
  {
    $id = $_POST["names"];
    $players[] = array($id, $names[$id], "09:00", "10:00");
    $count = $count + 1;
  }

  if(substr(@$_POST["submit"], 0, 13) == "Remove Player")
  {
    $i = substr(@$_POST["submit"], 14);
    unset($players[$i - 1]);
    $count = $count - 1;
  }

  $action = "times";
  $core->assign("count", $count);
  $core->assign("players", $players);
}
elseif($action == "items")
{
  $result = $core->SQL("SELECT Distinct `GroupID` FROM `operations_items` Order By `GroupID`");
  while($row = mysql_fetch_assoc($result))
  {
    $var = "group".$row['GroupID'];
    $core->assign($var, @$_POST[$var]);
    $groupnumber[$row['GroupID']] = @$_POST[$var];
  }
  $core->assign("groupnumber", $groupnumber);

  $opdate = $_POST["opdate"];
  $core->assign("opdate", $opdate);

  $count = $_POST["count"];

  $players = array();
  if(empty($count))
  {
    $count = 1;
    $players[] = array($core->CurrentUser()->ID, $core->CurrentUser()->Name, "09:00", "10:00");
  }
  else
  {
    for($i = 1; $i <= $count; $i++)
      $players[] = array($_POST["playerid".$i], $names[$_POST["playerid".$i]], $_POST["timein".$i], $_POST["timeout".$i]);
  }
  $core->assign("count", $count);
  $core->assign("players", $players);

  // Check times.
  $check = true;
  if(strtotime($opdate) === FALSE)
  {
    $check = false;
    $_GET["result"] = 1;
  }
  foreach($players as $player)
  {
    if((preg_match("/^(\d?)(\d)(:)(\d)(\d)$/", $player[2]) == 0) || (preg_match("/^(\d?)(\d)(:)(\d)(\d)$/", $player[3]) == 0))
    {
      $check = false;
      $_GET["result"] = 2;
      break;
    }
  }
  if($check)
  {
    $groups = array();
    $result = $core->SQL("SELECT Distinct `GroupID` FROM `operations_items` Order By `GroupID`");
    while($row = mysql_fetch_assoc($result))
    {
      $var = "group".$row['GroupID'];
      if(@$_POST[$var] == "on") $groups[] = $row['GroupID'];
    }

    $result = $core->SQL("SELECT `id`, `Name`, `GroupID` FROM operations_items WHERE FIND_IN_SET(`GroupID`, '".implode(",", $groups)."') ORDER BY `GroupID` ASC, `DisplayOrder` ASC, `Name` ASC");
    $items = array();
    while($row = mysql_fetch_assoc($result))
    {
      $items[] = array($row["id"], $row["GroupID"], $core->SQLUnEscape($row["Name"]));
    }
    $core->assign("items", $items);
  }
  else
  {    
    $action = "times";
  }
}
elseif($action == "done")
{
  $names = $core->GetAllUserNames();
  $result = $core->SQL("SELECT `id`, `Name`, `GroupID` FROM operations_items ORDER BY `GroupID` ASC, `DisplayOrder` ASC, `Name` ASC");
  $allitems = array();
  while($row = mysql_fetch_assoc($result))
  {
    $allitems[$row["id"]] = array($core->SQLUnEscape($row["Name"]), $row["GroupID"]);
  }
  
  $opdate = $_POST["opdate"];
  
  $count = $_POST["count"];
  $players = array();
  if(empty($count))
  {
    $count = 1;
    $players[] = array($core->CurrentUser()->ID, $core->CurrentUser()->Name, "09:00", "10:00");
  }
  else
  {
    for($i = 1; $i <= $count; $i++)
      $players[] = array($_POST["playerid".$i], $names[$_POST["playerid".$i]], $_POST["timein".$i], $_POST["timeout".$i]);
  }
  
  $items = array();
  foreach($_POST as $key => $value)
  {
    if((substr($key, 0, 4) == "item") && !empty($value) && (intval($value) > 0))
      $items[substr($key, 4)] = intval($value);
  }
  ksort($items);
  $itemsdb = array();
  foreach($items as $key => $value)
    $itemsdb[] = $key."=".$value;
  
  $playersdb = array();
  $timeinsdb = array();
  $timeoutsdb = array();
  foreach($players as $player)
  {
    $playersdb[] = $player[0];
    $timeinsdb[] = $player[0]."=".$player[2];
    $timeoutsdb[] = $player[0]."=".$player[3];
  }
  
  $itemsdisplay = array();
  foreach($allitems as $key => $value)
  {
    if(isset($items[$key]))
      $itemsdisplay[] = array($value[0], $value[1], $items[$key]);
  }
  $notes = $_POST["notes"];
  
  // Insert into DB
  $query = "INSERT INTO operations_submissions (Date,OpDate,Leader,Players,TimeIns,TimeOuts,Items,Notes) VALUES (";
  $query .= "'".$core->GMTTime()."',";
  $query .= "'".$opdate."',";
  $query .= $core->CurrentUser()->ID.",";
  $query .= "'".implode(",", $playersdb)."',";
  $query .= "'".implode(",", $timeinsdb)."',";
  $query .= "'".implode(",", $timeoutsdb)."',";
  $query .= "'".implode(",", $itemsdb)."',";
  $query .= "'".$core->SQLEscape($notes)."')";
  $core->SQL($query);
  
  // Estimated op value
  $result = $core->SQL("SELECT `id`, `Price` FROM operations_items");
  $allitems = array();
  while($row = mysql_fetch_assoc($result))
  {
    $allitems[$row["id"]] = $row["Price"];
  }
  $opvalue = 0;
  foreach($itemsdb as $item)
  {
    $val = explode("=", $item);
    $opvalue += $allitems[$val[0]] * $val[1];
  }
  $opvalue = number_format($opvalue, 0);
  
  // Display to the user
  $core->assign("opdate", $opdate);
  $core->assign("players", $players);
  $core->assign("items", $itemsdisplay);
  $core->assign("notes", $notes);
  $core->assign("opvalue", $opvalue);
}

$result = $core->SQL("SELECT * FROM `operations_groups` WHERE Active = 1 ORDER BY `DisplayOrder`, `GroupID`");
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
$core->display($core->PlugInPath."payoutsubmission/payoutsubmission.tpl");
?>