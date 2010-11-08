<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  if(!$cms->AccessCheck(User::EVE_Director, array(User::MDYN_CEO, User::MDYN_Administrator, User::MDYN_Developer))) { $cms->Goto("access.php"); }
  
  $action = @$_GET["action"];
  if(empty($action)) $action = "cronjobs";
  $result = 0;

  $crontypes = array("Hourly at xx:00", "Hourly at xx:30", "Daily at 00:00 GMT", "Daily at 11:00 GMT", "Daily at 12:00 GMT", "Weekly on Mondays at 00:00 GMT", "Weekly on Wednesdays at 00:00 GMT", "Weekly on Fridays at 00:00 GMT", "Weekly on Saturdays at 00:00 GMT", "Weekly on Sundays at 00:00 GMT");
  $cms->assign("crontypes", $crontypes);
  
  if(isset($_GET["edit"]) && is_numeric(@$_GET["edit"]))
  {
    if(!isset($_POST["id"]))
    {
      $job = $cms->ReadCronJob($_GET["edit"]);
      $_POST["id"] = $job->ID;
      $_POST["title"] = $job->Title;
      $_POST["type"] = $job->ScheduleType;
      $_POST["source"] = $job->Source;
    }
    $cms->assign("id", @$_POST["id"]);
    $cms->assign("title", @$_POST["title"]);
    $cms->assign("type", @$_POST["type"]);
    $cms->assign("source", @$_POST["source"]);
    $action = "edit";
  }
  elseif($action == "editdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["source"]))
      {
        $action = "edit";
        $result = 1;
        $cms->assign("id", @$_POST["id"]);
        $cms->assign("title", @$_POST["title"]);
        $cms->assign("type", @$_POST["type"]);
        $cms->assign("source", @$_POST["source"]);
      }
      else
      {
        $cms->EditCronJob($_POST["id"], $_POST["title"], $_POST["type"], $_POST["source"]);
        $cms->Goto("cron.php");
      }
    }
    else
      $cms->Goto("cron.php");
  }
  if(isset($_GET["run"]) && is_numeric(@$_GET["run"]))
  {
    $cms->RunCronJob($_GET["run"]);
    $cms->Goto("cron.php");
  }
  elseif($action == "newdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["source"]))
      {
        $action = "newjob";
        $result = 1;
        $cms->assign("title", @$_POST["title"]);
        $cms->assign("type", @$_POST["type"]);
        $cms->assign("source", @$_POST["source"]);
      }
      else
      {
        $cms->NewCronJob($_POST["title"], $_POST["type"], @$_POST["source"]);
        $cms->Goto("cron.php");
      }
    }
    else
      $cms->Goto("cron.php");
  }
  elseif(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]))
  {
    $job = $cms->ReadCronJob($_GET["delete"]);
    $cms->assign("id", $job->ID);
    $cms->assign("name", $job->Title);
    $action = "delete";
  }
  elseif($action == "deletedone")
  {
    if($_POST["submit"] == "Delete")
    {
      if(is_numeric($_POST["id"])) $cms->DeleteCronJob($_POST["id"]);
      $cms->Goto("cron.php");
    }
    else
      $cms->Goto("cron.php");
  }
  elseif(isset($_GET["developer"]) && is_numeric(@$_GET["developer"]))
  {
    $job = $cms->ReadCronJob($_GET["developer"]);
    $cms->assign("id", $job->ID);
    $cms->assign("developer", $job->Developer);
    $cms->assign("users", $cms->GetAllUsers(true, true));
    $action = "developer";
  }
  elseif($action == "developerdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(is_numeric($_POST["id"]) && is_numeric($_POST["developer"])) 
$cms->AssignCronDeveloper($_POST["id"], 
$_POST["developer"]);
      $cms->Goto("cron.php");
    }
    else
      $cms->Goto("cron.php");
 }
  elseif($action == "cronjobs")
  {
    $jobs = $cms->ReadAllCronJobs();
    $cms->assign("jobs", $jobs);
  }
  
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  
  $cms->display('cron.tpl');  
?>
