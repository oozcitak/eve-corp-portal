<?php
require_once('../core/core.class.php');
$cms = new Core();

if($cms->IsIGB())
{
  // Calendar
  $calendar = $cms->ReadCalendar();
  $cms->assign("calendar", $calendar);
  
  // Corp news
  $news = $cms->ReadNews();
  $cms->assign("news", $news);
}
else
{
  // Save shout
  if(isset($_GET["shout"])) 
  {
    $cms->SaveShout($_GET["shout"]);
    $cms->Goto("home.php");
  }

  // Current user
  $user = $cms->CurrentUser();

  // Welcome message
  $article = $cms->ReadArticle(1);
  $cms->assign("welcome", $article);
  
  // Calendar
  $calendar = $cms->ReadCalendar();
  $cms->assign("calendar", $calendar);
  
  // Corp news
  $news = $cms->ReadNews();
  $cms->assign("news", $news);
  $shortnews = "";
  for($i = 0; $i < min(count($news), 5); $i++)
  {
    $feed = $news[$i];
    $shortnews .= "<p><a href=\"news.php#item".$feed->ID."\">".$feed->Title."</a><br />".substr(strip_tags($feed->Text), 0, 140)."...</p>";
  }
  $cms->assign("shortnews", $shortnews);
  
  // Recent forum topics
  $hottopics = $cms->ReadHotForumTopics();
  $cms->assign("hottopics", $hottopics);
  
  // Shouts
  $cms->assign("shouts", $cms->ReadShouts());
  
  // Tranquility status
  $cms->assign("showtranqstatus", ($user->PortalSettings & User::ShowTQStatus) ? 1 : 0);
    
  // Skill in training
  $cms->assign("showtraining", ($user->PortalSettings & User::ShowCurrentSkill) ? 1 : 0);
  
  // Game News
  $gamenews = array();
  if($user->PortalSettings & User::ShowGameNews) $gamenews = $cms->ReadGameNews();
  $cms->assign('gamenews', $gamenews);
  $cms->assign('showevenews', ($user->PortalSettings & User::ShowGameNews ? 1 : 0));
  // Dev Blog
  $devblogs = array();
  if($user->PortalSettings & User::ShowDevBlogs) $devblogs = $cms->ReadDevBlogs();
  $cms->assign('devblogs', $devblogs);
  $cms->assign('showdevblogs', ($user->PortalSettings & User::ShowDevBlogs ? 1 : 0));
  // RP news
  $rpnews = array();
  if($user->PortalSettings & User::ShowRPNews) $rpnews = $cms->ReadRPNews();
  $cms->assign('rpnews', $rpnews);
  $cms->assign('showrpnews', ($user->PortalSettings & User::ShowRPNews ? 1 : 0));
  
  // Online characters
  $onlinechars = "";
  $chars = $cms->GetOnlineCharacters();
  if(!empty($chars))
  {
    $onlinechars = "<p>";
    foreach($chars as $id => $char)
      $onlinechars .= "<a href=\"profile.php?user=".$id."\">".$char."</a><br />";
    $onlinechars .= "</p>";
  }
  $cms->assign('onlinechars', $onlinechars);
  
  // Plug-in XML feeds
  $feeds = $cms->ReadPlugInFeedbacks();
  $cms->assign('pluginfeeds', $feeds);
}

$cms->display('home.tpl');

// *******************************************************
// Returns the skill in training
// *******************************************************  
function GetSkillInTraining()
{
  global $cms;
  if($cms->CurrentUser()->IsGuest) return "";
  $raw = $cms->APIQuery("http://api.eve-online.com/char/SkillInTraining.xml.aspx");
  if($raw == FALSE) return array(0, "<p>Could not connect to the API server.</p>");
  
  $xml = new SimpleXMLElement($raw);
  
  if ((int)$xml->error['code'] > 0) return array(0, "<p>Error ".$xml->error['code'].": ".$xml->error."</p>");
  
  $training = ($xml->result->skillInTraining == 1) ? true : false;
  $starttime = $xml->result->trainingStartTime;
  $endtime = $xml->result->trainingEndTime;
  $skillid = $xml->result->trainingTypeID;
  $tolevel = (int)$xml->result->trainingToLevel;
  $cacheduntil = $xml->cachedUntil;

  if($training && (strtotime($endtime) - strtotime($cms->GMTTime()) > 0))
  {
    $romans = array(1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V");
    $result = $cms->EveSQL("SELECT typeName FROM invTypes WHERE typeID=".$skillid);
    if(mysql_num_rows($result) == 0) 
      $skillname = "Unknown Skill (TypeID = ".$skillid.")";
    else
      $skillname = mysql_result($result, 0);
      $seconds = strtotime($endtime) - strtotime($cms->GMTTime());
    return array($seconds, "<p id='sit_main'>".$skillname." ".$romans[$tolevel]."<br /><span id='sit_timer'>".$cms->SecondsToTime($seconds, true)."</span>&nbsp;(".$cms->GMTToLocal($endtime).")</p><p><img src='../img/level".$tolevel."_act.gif' /></p><p><span class='info'>(Cached until ".$cms->GMTToLocal($cacheduntil).")</span></p>");
  }
  else
    return array(0, "<p>There is no skill in training!</p><p><span class='info'>(Skill training information is cached for 15 minutes. Next update on ".$cms->GMTToLocal($cacheduntil).").</span></p>");
}
	
?>
