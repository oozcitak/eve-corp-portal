<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  if(isset($_GET["unsubscribe"]) && is_numeric(@$_GET["unsubscribe"]))
  {
    $cms->UnSubscribeForumTopic($_GET["unsubscribe"]);
  }
  
  // List all signed-up events
  $calendar = $cms->ReadCalendarSignups();
  $cms->assign("calendar", $calendar);
  
  // Subscribed topics
  $subs = $cms->GetForumSubscriptions();

  $cms->assign('subscriptions', $subs);
  $cms->display('signups.tpl');
?>
