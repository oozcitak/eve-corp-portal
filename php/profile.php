<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  if($cms->CurrentUser()->Name == "Guest") { header("Location: access.php"); exit; }

  $action = @$_GET["action"];
  $result = 0;

  if(isset($_GET["user"]) && is_numeric(@$_GET["user"]))
  {
    $cms->assign('showuser', $cms->GetUserFromID($_GET["user"]));
    $cms->assign('posts', $cms->ForumRepliesByAuthor($_GET["user"]));
    $_GET["action"] = "user";
  }
  elseif($action == "editdone")
  {
    if($_POST["submit"] == "Save")
    {
      $cms->EditUserInfo($_POST["timezone"], $_POST["email"], $_POST["im"], $_POST["dob_Year"]."-".$_POST["dob_Month"]."-".$_POST["dob_Day"], $_POST["location"]);
      if(!empty($_POST["apiuserid"]) && !empty($_POST["apikey"])) $cms->EditUserAPIInfo($_POST["apiuserid"], $_POST["apikey"]);
      $settings = 0;
      if(@$_POST["showgamenews"] == "on") $settings = $settings | User::ShowGameNews;
      if(@$_POST["showdevblogs"] == "on") $settings = $settings | User::ShowDevBlogs;
      if(@$_POST["showrpnews"] == "on") $settings = $settings | User::ShowRPNews;
      if(@$_POST["showtqstatus"] == "on") $settings = $settings | User::ShowTQStatus;
      if(@$_POST["showcurrentskill"] == "on") $settings = $settings | User::ShowCurrentSkill;
      if(@$_POST["forumdisplay"] == "on") $settings = $settings | User::CondensedForums;
      if(@$_POST["contactinfo"] == "1") $settings = $settings | User::ContactInfoDirectors;
      if(@$_POST["contactinfo"] == "2") $settings = $settings | User::ContactInfoPublic;
      if(@$_POST["forwardmail"] == "on") $settings = $settings | User::ForwardMail;
      $cms->EditUserPortalSettings($settings, $_POST["dateformat"]);
      $cms->EditUserRLStatus((@$_POST["rlstatus"] == "on" ? true : false), $_POST["oop_Year"]."-".$_POST["oop_Month"]."-".$_POST["oop_Day"], $_POST["oopnote"]);
      $result = 1;
    }
    
    unset($_GET["action"]);
  }
  elseif($action == "passworddone")
  {
    if($_POST["submit"] == "Change Password")
    {
      if(empty($_POST["password1"]) || empty($_POST["password2"]))
      {
        $result = 4;
        $_GET["action"] = "password";
      }
      elseif($_POST["password1"] != $_POST["password2"])
      {
        $result = 3;
        $_GET["action"] = "password";
      }
      else
      {
        $cms->EditUserPassword($_POST["password1"]);        
        unset($_GET["action"]);
        $result = 2;
      }
    }
    else
      unset($_GET["action"]);    
  }
  elseif($action == "signaturedone")
  {
    if($_POST["submit"] == "Save")
    {
      $cms->EditUserSignature(stripslashes($_POST["signature"]));
      unset($_GET["action"]);
      $result = 6;
    }
    else
      unset($_GET["action"]);
  }
  elseif($action == "removealts")
  {
    if($_POST["submit"] == "Remove Selected Alts")
    {
      $alts = $cms->CurrentUser()->Alts;
      $altscopy = $alts;
      foreach($alts as $key => $alt)
      {
        if(@$_POST["alt".$key] == "on") unset($altscopy[$key]);
      }
      $cms->EditUserAlts($altscopy);
      $result = 5;
    }
    
    unset($_GET["action"]);
  }
  elseif($action == "registeralt2")
  {
    $res = $cms->GetUserCharacters($_POST["apiuserid"], $_POST["apikey"], true);

    if($res === FALSE)
    {
      $result = 20;
      $action = "registeralt";
    }
    elseif(empty($res))
    {
      $result = 21;
      $action = "registeralt";
    }
    else
    {
      $cms->assign("characters", $res);
    }
  }
  elseif($action == "registeralt3")
  {
    $altname = $_POST["name_".$_POST["char"]];
    $cms->RegisterAlt($altname);
    $action = "home";
  }
  elseif($action == "updateportrait")
  {
    $cms->DeletePortrait();
    $action = "home";    
  }

  // Date formats
  $dateformats1 = array("Y.m.d H:i", "m.d.Y H:i", "d.m.Y H:i", "F d, Y H:i", "d F Y H:i", "d M y Hi");
  $dateformats2 = array();
  foreach($dateformats1 as $dateformat)
  {
    $dateformats2[$dateformat] = gmdate($dateformat, time() + $cms->CurrentUser()->TimeZone * 3600);
  }
  $cms->assign('dateformats', $dateformats2);
  $cms->assign('result', $result);
  $cms->display('profile.tpl');
?>
