<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');

$action = @$_GET["action"];

if($action == "payout")
{
  $names = $core->GetAllUserNames();
  $opids = array();
  foreach($_POST as $key => $value)
  {
    if((substr($key, 0, 2) == "op") && ($value == "on"))
      $opids[] = substr($key, 2);
  }
  
  if($_POST["submit"] == "Reject")
  {
    // Reject selected ops
    $core->SQL("UPDATE operations_submissions SET Status=3, RejectReason='".$core->SQLEscape($_POST["reject"])."' WHERE FIND_IN_SET(id, '".implode(",", $opids)."')");
    // Send messages to op leaders
    $result = $core->SQL("SELECT id, OpDate, Leader FROM operations_submissions WHERE FIND_IN_SET(id, '".implode(",", $opids)."')");
    while($row = mysql_fetch_assoc($result))
    {
      $id = $row["id"];
      $date = date("Y-m-d", strtotime($row["OpDate"]));
      $leader = $row["Leader"];
      $text = "<p>Following operation submitted by you was rejected by ".$core->CurrentUser()->Name.".</p>";
      $text .= "<p><a href='../plugins/payoutview/index.php?view=".$id."'>View Rejected Operation</a></p>";
      $text .= "<p><b>REASON:</b><br />".$_POST["reject"]."</p>";
      $core->SendMail($date." Operation Rejected", $text, $leader);
    }
    $core->Goto("index.php");
  }
  else
  {
    $corpcut = $_POST["corpcut"];
    $result = $core->SQL("SELECT * FROM operations_params");
    while($row = mysql_fetch_assoc($result))
    {
      $core->assign($row["Name"], $row["Value"]);
    }
    // Item prices
    $result = $core->SQL("SELECT `id`, `Price` FROM operations_items");
    $allitems = array();
    while($row = mysql_fetch_assoc($result))
    {
      $allitems[$row["id"]] = $row["Price"];
    }
    // Get selected ops
    $result = $core->SQL("SELECT * FROM operations_submissions WHERE FIND_IN_SET(id, '".implode(",", $opids)."')");
    $payouts = array();
    $corptotal = 0;
    $playertotal = 0;
    while($row = mysql_fetch_assoc($result))
    {
      // Total op value
      $opvalue = 0;
      $items = explode(",", $row["Items"]);
      foreach($items as $item)
      {
        $vals = explode("=", $item);
        $opvalue += $allitems[$vals[0]] * $vals[1];
      }
      // Times
      $timeins = array();
      $timeinsdb = explode(",", $row["TimeIns"]);
      foreach($timeinsdb as $item)
      {
        preg_match("/(\d*)=(\d*):(\d*)/", $item, $matches);
        $timeins[$matches[1]] = $matches[2] + $matches[3] / 60;
      }
      $timeouts = array();
      $timeoutsdb = explode(",", $row["TimeOuts"]);
      foreach($timeoutsdb as $item)
      {
        preg_match("/(\d*)=(\d*):(\d*)/", $item, $matches);
        $timeouts[$matches[1]] = $matches[2] + $matches[3] / 60;
      }
      // Hours
      $totaltime = 0;
      $players = explode(",", $row["Players"]);
      $playertimes = array();
      foreach($players as $player)
      {
        $time = $timeouts[$player] - $timeins[$player];
        if($time < 0) $time += 24;
        if($time == 0) $time = 24;
        
        $playertimes[$player] = $time;
        $totaltime += $time;
      }
      
      // Corp cut
      $corptotal += $opvalue * ($corpcut / 100);
      // Player payouts
      foreach($playertimes as $player => $time)
      {
        $payout = $time / $totaltime * $opvalue * (1 - $corpcut / 100);
        $playertotal += $payout;
        if(isset($payouts[$player]))
          $payouts[$player][2] += $payout;
        else
          $payouts[$player] = array($player, $names[$player], $payout);
      }
    }
    // Grand total
    $grandtotal = $corptotal + $playertotal;
    // Format and order
    foreach($payouts as &$payout)
      $payout[2] = number_format($payout[2], 0);
    usort($payouts, "cmp");
    
    $core->assign("opcount", count($opids));
    $core->assign("opids", $opids);
    $core->assign("corpcut", $corpcut);
    $core->assign("payouts", $payouts);
    $core->assign("corptotal", number_format($corptotal, 0));
    $core->assign("playertotal", number_format($playertotal, 0));
    $core->assign("grandtotal", number_format($grandtotal, 0));
  }
}
elseif($action == "payoutdone")
{
  $opids = array();
  foreach($_POST as $key => $value)
  {
    if(substr($key, 0, 2) == "op")
      $opids[] = $value;
  }

  // Mark selected ops as paid
  $core->SQL("UPDATE operations_submissions SET Status=4 WHERE FIND_IN_SET(id, '".implode(",", $opids)."')");
  $core->Goto("index.php");
}
else
{
  $action = "home";
  $names = $core->GetAllUserNames();
  
  // Item prices
  $result = $core->SQL("SELECT `id`, `Price` FROM operations_items");
  $allitems = array();
  while($row = mysql_fetch_assoc($result))
  {
    $allitems[$row["id"]] = $row["Price"];
  }
  
  // Op Status
  // 0 - New
  // 1 - Resubmitted
  // 2 - Canceled
  // 3 - Rejected
  // 4 - Paid
  $result = $core->SQL("SELECT * FROM operations_submissions WHERE Status<=1 ORDER BY Date ASC");
  $ops = array();
  $total = 0;
  while($row = mysql_fetch_assoc($result))
  {
    $opvalue = 0;
    $players = array();
    $playersdb = explode(",", $row["Players"]);
    foreach($playersdb as $player)
      $players[] = $names[$player];
      
    $items = array();
    $itemsdb = explode(",", $row["Items"]);
    foreach($itemsdb as $item)
    {
      $val = explode("=", $item);
      $items[$val[0]] = $val[1];
      $opvalue += $allitems[$val[0]] * $val[1];
    }
    $total += $opvalue;
    $opvalue = number_format($opvalue, 0);
    
    $ops[] = array("ID" => $row["id"], "Value" => $opvalue, "Leader" => $names[$row["Leader"]], "Players" => implode(",", $players), "Date" => $core->GMTToLocal($row["Date"]), "OpDate" => $row["OpDate"], "Notes" => $core->SQLUnEscape($row["Notes"]));
  }
  $total = number_format($total, 0);
  $core->assign("total", $total);
  $core->assign("ops", $ops);
  $core->assign("opcount", count($ops));
}

$core->assign("action", $action);
$core->display($core->PlugInPath."payoutmanagement/payoutmanagement.tpl");

function cmp($a, $b)
{
  return strcasecmp($a[1], $b[1]);
}

?>