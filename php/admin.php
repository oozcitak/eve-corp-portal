<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  if(!$cms->AccessCheck(User::EVE_Director, array(User::MDYN_CEO, User::MDYN_Administrator))) { $cms->Goto("access.php"); }
  
  $action = @$_GET["action"];
  if(empty($action)) $action = "users";
  $result = 0;
  
  if(isset($_GET["edit"]) && is_numeric(@$_GET["edit"]))
  {
    if(!isset($_POST["id"]))
    {
      $note = $cms->ReadArticle($_GET["edit"]);
      $_POST["id"] = $note->ID;
      $_POST["title"] = $note->Title;
      $_POST["text"] = $note->Text;
    }
    $cms->assign("id", @$_POST["id"]);
    $cms->assign("title", @$_POST["title"]);
    $cms->assign("text", @$_POST["text"]);
    $action = "edit";
  }
  elseif($action == "editdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["text"]))
      {
        $action = "edit";
        $result = 1;
        $cms->assign("id", @$_POST["id"]);
        $cms->assign("title", @$_POST["title"]);
        $cms->assign("text", @$_POST["text"]);
      }
      else
      {
        if($_POST["id"] == 1 || $_POST["id"] == 2)
          $cms->EditArticle($_POST["id"], $_POST["title"], $_POST["text"], 0, 4);
        else
          $cms->EditArticle($_POST["id"], $_POST["title"], $_POST["text"], 2, 4);
        $cms->Log("Edited classified article: ".$_POST["title"]);
        $cms->Goto("admin.php?action=articles");
      }
    }
    else
      $cms->Goto("admin.php?action=articles");
  }
  elseif($action == "shouts")
  {
    $shouts = $cms->ReadShouts();
    $cms->assign("shouts", $shouts);
  }
  elseif(isset($_GET["deleteshout"]) && is_numeric(@$_GET["deleteshout"]))
  {
    $cms->DeleteShout($_GET["deleteshout"]);
    $cms->Goto("admin.php?action=shouts");
  }
  elseif($action == "users")
  {
    if(!isset($_POST["inactivityperiod"]))
    {
      $_POST["corpname"] = $cms->GetSetting("CorporationName");
      $_POST["alliancename"] = $cms->GetSetting("AllianceName");
    }
    $cms->assign("corpname", @$_POST["corpname"]);
    $cms->assign("alliancename", @$_POST["alliancename"]);
  }
  elseif($action == "names")
  {
    if(!isset($_POST["corpname"]))
    {
      $_POST["corpname"] = $cms->GetSetting("CorporationName");
      $_POST["alliancename"] = $cms->GetSetting("AllianceName");
      $_POST["allianceurl"] = $cms->GetSetting("AllianceURL");
      $_POST["killboardurl"] = $cms->GetSetting("KillboardURL");
      $_POST["apicharid"] = $cms->GetSetting("DirectorAPICharID");
      $_POST["apiuserid"] = $cms->GetSetting("DirectorAPIUserID");
      $_POST["apikey"] = $cms->GetSetting("DirectorAPIKey");
      $_POST["secondaryapicharid"] = $cms->GetSetting("SecondaryDirectorAPICharID");
      $_POST["secondaryapiuserid"] = $cms->GetSetting("SecondaryDirectorAPIUserID");
      $_POST["secondaryapikey"] = $cms->GetSetting("SecondaryDirectorAPIKey");
    }
    $cms->assign("corpname", @$_POST["corpname"]);
    $cms->assign("alliancename", @$_POST["alliancename"]);
    $cms->assign("allianceurl", @$_POST["allianceurl"]);
    $cms->assign("killboardurl", @$_POST["killboardurl"]);
    $cms->assign("apicharid", @$_POST["apicharid"]);
    $cms->assign("apiuserid", @$_POST["apiuserid"]);
    $cms->assign("apikey", @$_POST["apikey"]);
    $cms->assign("secondaryapicharid", @$_POST["secondaryapicharid"]);
    $cms->assign("secondaryapiuserid", @$_POST["secondaryapiuserid"]);
    $cms->assign("secondaryapikey", @$_POST["secondaryapikey"]);
  }
  elseif($action == "namesdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["corpname"]))
      {
        $action = "names";
        $result = 2;
        $cms->assign("corpname", @$_POST["corpname"]);
        $cms->assign("alliancename", @$_POST["alliancename"]);
        $cms->assign("allianceurl", @$_POST["allianceurl"]);
        $cms->assign("killboardurl", @$_POST["killboardurl"]);
        $cms->assign("apicharid", @$_POST["apicharid"]);
        $cms->assign("apiuserid", @$_POST["apiuserid"]);
        $cms->assign("apikey", @$_POST["apikey"]);
        $cms->assign("secondaryapicharid", @$_POST["secondaryapicharid"]);
        $cms->assign("secondaryapiuserid", @$_POST["secondaryapiuserid"]);
        $cms->assign("secondaryapikey", @$_POST["secondaryapikey"]);
      }
      else
      {
        $cms->SetSetting("CorporationName", $_POST["corpname"]);
        $cms->SetSetting("AllianceName", $_POST["alliancename"]);
        $cms->SetSetting("AllianceURL", $_POST["allianceurl"]);
        $cms->SetSetting("KillboardURL", $_POST["killboardurl"]);
        $cms->SetSetting("DirectorAPICharID", $_POST["apicharid"]);
        $cms->SetSetting("DirectorAPIUserID", $_POST["apiuserid"]);
        $cms->SetSetting("DirectorAPIKey", $_POST["apikey"]);
        $cms->SetSetting("SecondaryDirectorAPICharID", $_POST["secondaryapicharid"]);
        $cms->SetSetting("SecondaryDirectorAPIUserID", $_POST["secondaryapiuserid"]);
        $cms->SetSetting("SecondaryDirectorAPIKey", $_POST["secondaryapikey"]);
        $cms->Log("Changed Corporation/Alliance settings.");
        $cms->Goto("admin.php");
      }
    }
    else
      $cms->Goto("admin.php");
  }
  elseif($action == "frontpage")
  {
    if(!isset($_POST["NewsLimit"]))
    {
      $_POST["NewsLimit"] = $cms->GetSetting("NewsLimit");
    }
    $cms->assign("NewsLimit", @$_POST["NewsLimit"]);
  }
  elseif($action == "frontpagedone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["NewsLimit"]))  // Add a post loop and error on any empty values
      {
        $action = "frontpage";
        $result = 5;
        $cms->assign("NewsLimit", @$_POST["NewsLimit"]);
      }
      else
      {
        $cms->SetSetting("NewsLimit", $_POST["NewsLimit"]);
        $cms->Log("Changed Front Page Configuration Settings.");
        $cms->Goto("admin.php");
      }
    }
    else
      $cms->Goto("admin.php");
  }
  elseif($action == "membercorps")
  {
    $cms->assign("membercorps", $cms->GetAllianceMembers());
  }
  elseif($action == "membercorpsdone")
  {
    if(@$_POST["submit"] == "Save List and Update Users")
    {
      $corps = $cms->GetAllianceMembers();
      $res = array();
      foreach($corps as $corp)
      {
        if(@$_POST["item".$corp["ID"]] == "on") $res[] = $corp["ID"];
      }
      $cms->SetBlockedCorporations($res);
      $result = $cms->UpdateAllUsers();
      if($result === false)
        $cms->assign("error", true);
      else
      {
        $cms->assign("error", false);
        $cms->assign("syncres", $result);
      }
    }
    elseif(@$_POST["submit"] == "Refresh Member Corporations List")
    {
      set_time_limit(0);
      $cms->UpdateAllianceMembers();
      $cms->Goto("admin.php?action=membercorps");    
    }
    else
      $cms->Goto("admin.php");
  }
  elseif($action == "setinactivity")
  {
    $cms->assign("inactivityperiod", $cms->GetSetting("InactivityPeriod"));
  }
  elseif($action == "setinactivitydone")
  {
    if($_POST["submit"] == "Save")
    {
      if(!is_numeric(@$_POST["inactivityperiod"]))
      {
        $action = "setinactivity";
        $result = 4;
      }
      else
      {
        $cms->SetSetting("InactivityPeriod", $_POST["inactivityperiod"]);
        $cms->Goto("admin.php");
      }
    }
    else
      $cms->Goto("admin.php");
  }
  elseif($action == "activityreport")
  {
    $members = $cms->GetAllCorpMembers();
    if(empty($members))
      $cms->assign("error", true);
    else
    {
      $users = $cms->GetAllUsers(true, true);
      $inactivityperiod = $cms->GetSetting("InactivityPeriod");
      $cutoff = $inactivityperiod * 24 * 60 * 60;
      $registered = array();
      foreach($users as $user)
      {
        $inactivity1 = strtotime($cms->GMTTime()) - strtotime($user->LastLogin);
        $gameuser = null;
        foreach($members as $member)
        {
          $inactivity2 = strtotime($cms->GMTTime()) - strtotime($member["LastGameLogin"]);
          if(($member["CharID"] == $user->CharID))
          {
            $gameuser = $member;
            break;
          }
        }
        if($gameuser)
        {
          $registered[] = array_merge($gameuser, array("PortalInactivity" => $inactivity1, "GameInactivity" => $inactivity2, "LastPortalLogin" => $user->LastLogin, "IsOOP" => $user->IsOOP, "OOPUntil" => $user->OOPUntil, "OOPNote" => $user->OOPNote, "TimeZone" => $user->TimeZone));
          if($user->IsOOP) $cms->assign("hasoops", true);
          if(!$user->IsOOP && ($inactivity1 > $cutoff || $inactivity2 > $cutoff)) $cms->assign("hasinactives", true);
        }
      }

      $unregistered = array();
      foreach($members as $member)
      {
        $found = false;
        foreach($users as $user)
        {
          if($member["CharID"] == $user->CharID || in_array($member["Name"], $user->Alts))
          { 
            $found = true; 
            break; 
          }
        }
        $inactivity = strtotime($cms->GMTTime()) - strtotime($member["LastGameLogin"]);
        if(!$found) $unregistered[] = array_merge($member, array("GameInactivity" => $inactivity));
      }
    
      usort($registered, "cmp");
      usort($unregistered, "cmp");
      $cms->assign("error", false);
      $cms->assign("inactivityperiod", $inactivityperiod);
      $cms->assign("registered", $registered);
      $cms->assign("unregistered", $unregistered);    
    }
  }
  elseif($action == "oneclickdo")
  {
    $result = $cms->UpdateAllUsers();
    if($result === false)
      $cms->assign("error", true);
    else
    {
      $cms->assign("error", false);
      $cms->assign("syncres", $result);
    }
  }
  elseif($action == "editroles")
  {
    $users = $cms->GetAllUsers(false, true);
    usort($users, "objcmp");    
    $cms->assign("members", $users);
  }
  elseif($action == "editrolesdone")
  {
    if($_POST["submit"] == "Save")
    {
      $users = $cms->GetAllUsers(false, true);
      foreach($users as $user)
      {
        $roles = "0";
        if(@$_POST["news".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_CanSubmitNews);
        if(@$_POST["calendar".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_CanSubmitCalendar);
        if(@$_POST["forummod".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_ForumModerator);
        if(@$_POST["manager".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_Manager);
        if(@$_POST["admin".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_Administrator);
        if(@$_POST["dev".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_Developer);
        if(@$_POST["honorary".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_HonoraryMember);
        if(@$_POST["allyleader".$user->ID] == "on") $roles = BigNumber::Add($roles, User::MDYN_AllyLeader);
        
        $user->PortalRoles = $roles;
      }
      $cms->UpdateAllUserRoles($users);
      $cms->Log("Edited user roles.");
    }
    $cms->Goto("admin.php");
  }
  elseif($action == "guests")
  {
    $users = $cms->GetAllUsers(false, true);
    usort($users, "objcmp");
    $guests = array();
    $allies = array();
    foreach($users as $user)
    {
      if($user->IsGuest)
         $guests[] = $user;
      elseif($user->IsAlly)
         $allies[] = $user;
    }
    $cms->assign("guests", $guests);
    $cms->assign("allies", $allies);
  }
  elseif($action == "ban")
  {
    $users = $cms->GetAllUsers(false, false);
    usort($users, "objcmp");
    $cms->assign("members", $users);
  }
  elseif($action == "bandone")
  {
    if($_POST["submit"] == "Save")
    {
      $users = $cms->GetAllUsers(false, false);
      foreach($users as $user)
      {
        if(@$_POST["ban".$user->ID] == "on")
          $user->IsActive = false;
        else
          $user->IsActive = true;
      }
      $cms->UpdateBannedUsers($users);
      $cms->Log("Banned or unbanned users.");
    }
    $cms->Goto("admin.php");
  }
  elseif($action == "log")
  {
    $page = @$_GET["page"];
    if(empty($page)) $page = "1";
    $pagesize = 50;
    $count = $cms->LogCount();
    $pagecount = ceil($count / $pagesize);
    
    $cms->assign("logs", $cms->ReadLog(($page - 1) * $pagesize, $pagesize));
    $cms->assign("page", $page);
    $cms->assign("pagecount", $pagecount);
  }
  
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  
  $cms->display('admin.tpl');

  function cmp($a, $b)
  {
    return strcasecmp($a["Name"], $b["Name"]);
  }

  function objcmp($a, $b)
  {
    if($a->IsCEO())
      return -1;
    elseif($b->IsCEO()) 
      return 1;
      
    if($a->IsDirector() && !$b->IsDirector())
      return -1;
    elseif($b->IsDirector() && !$a->IsDirector())
      return 1;
    
    if($a->IsManager() && !$b->IsManager())
      return -1;
    elseif($b->IsManager() && !$a->IsManager())
      return 1;
      
    if($a->AccessRight() > $b->AccessRight())
      return -1;
    elseif($a->AccessRight() < $b->AccessRight())
      return 1;
      
    return strcasecmp($a->Name, $b->Name);
  }
  
?>
