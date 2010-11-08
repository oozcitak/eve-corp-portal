<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  $action = @$_GET["action"];
  if(empty($action)) $action = "home";
  $result = 0;

  $isadmin = $cms->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $cms->CurrentUser()->HasPortalRole(User::MDYN_Administrator) || $cms->CurrentUser()->HasEVERole(User::EVE_Director) ? 1 : 0;
  $cms->assign("isadmin", $isadmin);
  if($cms->CurrentUser()->HasPortalRole(User::MDYN_CanSubmitCalendar) || $cms->CurrentUser()->AccessRight() >= 3) $cms->assign("canpost", true);
  // All corp members can post
  if($cms->CurrentUser()->AccessRight() >= 2) $cms->assign("canpost", true);
  
  if(isset($_GET["view"]))
  {
    // List all calendar entries
    $allcalendar = $cms->ReadCalendarAll();
    $calendar = array();
    foreach($allcalendar as $item)
      if(date("Ymd", strtotime($item->Date)) == $_GET["view"]) $calendar[] = $item;
    $cms->assign("view", date("Y-m-d", strtotime($_GET["view"])));
    $cms->assign("calendar", $calendar);
    $action = "view";
  }
  elseif(isset($_GET["read"]) && is_numeric(@$_GET["read"]))
  {
    // Show calendar entry
    $note = $cms->ReadCalendarEntry($_GET["read"]);
    $_POST["id"] = $note->ID;
    $_POST["title"] = $note->Title;
    $_POST["text"] = $note->Text;
    $_POST["readaccess"] = $note->ReadAccess;
    $cms->assign("author", $note->AuthorName);
    $cms->assign("date", $note->Date);
    $cms->assign("editid", $isadmin == true || $cms->CurrentUser()->AccessRight() >= 4 || $note->Author == $cms->CurrentUser()->ID ? $note->ID : 0);
    $action = "read";
  }
  elseif(isset($_GET["edit"]) && is_numeric(@$_GET["edit"]))
  {
    $note = $cms->ReadCalendarEntry($_GET["edit"]);
    $_POST["id"] = $note->ID;
    $_POST["title"] = $note->Title;
    $_POST["text"] = $note->Text;
    $_POST["readaccess"] = $note->ReadAccess;
    $date = getdate(mktime(6, 0, 0, date("m"), date("d") + 1, date("Y")));
    $_POST["cal_Year"] = $date["year"];
    $_POST["cal_Month"] = $date["mon"];
    $_POST["cal_Day"] = $date["mday"];
    $_POST["cal_Hour"] = $date["hours"];
    $_POST["cal_Minute"] = $date["minutes"];
    $action = "edit";
  }
  elseif(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]))
  {
    $cms->DeleteCalendarEntry($_GET["delete"]);
    $cms->Goto("calendar.php");    
  }
  elseif(isset($_GET["signup"]) && is_numeric(@$_GET["signup"]))
  {
    $cms->SignUpToCalendarEntry($_GET["signup"]);
    $cms->Goto("calendar.php");    
  }
  elseif($action == "new")
  {
    $_POST["readaccess"] = 2;
    $date = getdate(mktime(6, 0, 0, date("m"), date("d") + 1, date("Y")));
    $_POST["cal_Year"] = $date["year"];
    $_POST["cal_Month"] = $date["mon"];
    $_POST["cal_Day"] = $date["mday"];
    $_POST["cal_Hour"] = $date["hours"];
    $_POST["cal_Minute"] = $date["minutes"];
  }
  elseif($action == "newdone")
  {
    if($_POST["submit"] == "Save")
    {
      $date = $_POST["cal_Year"]."-".$_POST["cal_Month"]."-".$_POST["cal_Day"]." ".$_POST["cal_Hour"].":".$_POST["cal_Minute"].":00";
      if(empty($_POST["title"]) || empty($_POST["text"]))
      {
        $action = "new";
        $result = 1;
      }
      elseif(strtotime($date) <= time())
      {
        $action = "new";
        $result = 2;
      }
      elseif(!checkdate($_POST["cal_Month"], $_POST["cal_Day"], $_POST["cal_Year"]))
      {
        $action = "new";
        $result = 3;
      }
      else
      {
        $cms->InsertCalendarEntry($date, $_POST["title"], $_POST["text"], $_POST["readaccess"]);
        $cms->Goto("calendar.php");
      }
    }
    else
      $cms->Goto("calendar.php");
  }
  elseif($action == "editdone")
  {
    if($_POST["submit"] == "Save")
    {
      $date = $_POST["cal_Year"]."-".$_POST["cal_Month"]."-".$_POST["cal_Day"]." ".$_POST["cal_Hour"].":".$_POST["cal_Minute"].":0";
      if(empty($_POST["title"]) || empty($_POST["text"]))
      {
        $action = "edit";
        $result = 1;
      }
      elseif(strtotime($date) <= time())
      {
        $action = "edit";
        $result = 2;
      }
      elseif(!checkdate($_POST["cal_Month"], $_POST["cal_Day"], $_POST["cal_Year"]))
      {
        $action = "new";
        $result = 3;
      }
      else
      {
        $cms->EditCalendarEntry($_POST["id"], $date, $_POST["title"], $_POST["text"], $_POST["readaccess"]);
        $cms->Goto("calendar.php");
      }
    }
    elseif($_POST["submit"] == "Delete")
    {
      if(is_numeric($_POST["id"])) $cms->DeleteCalendarEntry($_POST["id"]);
      $cms->Goto("calendar.php");
    }
    else
      $cms->Goto("calendar.php");
  }
  elseif($action == "home")
  {
    // List all calendar entries
    $calendar = $cms->ReadCalendar();
    $cms->assign("calendar", $calendar);
    $calendar = $cms->ReadCalendarAll();
    // Month view
    $firstday = mktime(0, 0, 0, date("m"), 1, date("Y"));
    $firstday = $firstday - date("w", $firstday) * (60 * 60 * 24);
    $lastday = $firstday + 42 * (60 * 60 * 24);
    $days1 = array();
    $current = $firstday;
    while($current < $lastday)
    {
      $titles = array();
      foreach($calendar as $item)
        if(date("d m Y", strtotime($item->Date)) == date("d m Y", $current)) $titles[] = $item->Title;
      
      if(empty($titles))      
        $text = date("j", $current);
      else
        $text = "<a href=\"calendar.php?view=".date("Ymd", $current)."\" title=\"".implode(", ", $titles)."\">".date("j", $current)."</a>";
        
      $days1[] = date("m", $current) == date("m") ? $text : "&nbsp;";
      $current = $current + (60 * 60 * 24);
    }
    $cms->assign("thismonth", date("F Y"));
    $cms->assign("thismonthdays", $days1);
    // Next month
    $firstday = mktime(0, 0, 0, date("m") + 1, 1, date("Y"));
    $firstday = $firstday - date("w", $firstday) * (60 * 60 * 24);
    $lastday = $firstday + 42 * (60 * 60 * 24);
    $days2 = array();
    $current = $firstday;
    while($current < $lastday)
    {
      $days2[] = date("m", $current) == date("m") + 1 ? date("j", $current) : "&nbsp;";
      $current = $current + (60 * 60 * 24);
    }
    $cms->assign("nextmonth", date("F Y", mktime(0, 0, 0, date("m") + 1, date("d"),   date("Y"))));
    $cms->assign("nextmonthdays", $days2);
  }

  if(!isset($_POST["readaccess"])) $_POST["readaccess"] = 2;
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  $cms->assign("id", @$_POST["id"]);
  $cms->assign("title", @$_POST["title"]);
  $cms->assign("text", @$_POST["text"]);
  $cms->assign("readaccess", @$_POST["readaccess"]);
  if(isset($_POST["cal_Year"]))
    $cms->assign("date", mktime($_POST["cal_Hour"], $_POST["cal_Minute"], 0, $_POST["cal_Month"], $_POST["cal_Day"], $_POST["cal_Year"]));
  
  $cms->display('calendar.tpl');
?>
