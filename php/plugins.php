<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  if(!$cms->AccessCheck(User::EVE_Director, array(User::MDYN_CEO, User::MDYN_Administrator, User::MDYN_Developer))) { $cms->Goto("access.php"); }
  
  $action = @$_GET["action"];
  if(empty($action)) $action = "plugins";
  $result = 0;
  
  if(isset($_GET["edit"]) && is_numeric(@$_GET["edit"]))
  {
    if(!isset($_POST["id"]))
    {
      $plugin = $cms->ReadPlugIn($_GET["edit"]);
      $_POST["id"] = $plugin->ID;
      $_POST["title"] = $plugin->Title;
      $_POST["releasecontrol"] = $plugin->Release;
      $_POST["accesscontrol"] = $plugin->ReadAccess;
      $_POST["showigb"] = ($plugin->ShowIGB ? "on" : "");
      $_POST["showadmin"] = ($plugin->ShowAdmin ? "on" : "");
    }
    $cms->assign("id", @$_POST["id"]);
    $cms->assign("title", @$_POST["title"]);
    $cms->assign("releasecontrol", @$_POST["releasecontrol"]);
    $cms->assign("accesscontrol", @$_POST["accesscontrol"]);
    $cms->assign("showigb", @$_POST["showigb"]);
    $cms->assign("showadmin", @$_POST["showadmin"]);
    $action = "edit";
  }
  elseif($action == "editdone" && is_numeric(@$_POST["id"]))
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]))
      {
        $action = "edit";
        $result = 1;
        $cms->assign("id", @$_POST["id"]);
        $cms->assign("title", @$_POST["title"]);
        $cms->assign("releasecontrol", @$_POST["releasecontrol"]);
        $cms->assign("accesscontrol", @$_POST["accesscontrol"]);
        $cms->assign("showigb", @$_POST["showigb"]);
        $cms->assign("showadmin", @$_POST["showadmin"]);
      }
      else
      {
        $cms->EditPlugIn($_POST["id"], $_POST["title"], $_POST["releasecontrol"], $_POST["accesscontrol"], (@$_POST["showigb"] == "on" ? true : false), (@$_POST["showadmin"] == "on" ? true : false));
        $cms->Goto("plugins.php");
      }
    }
    else
      $cms->Goto("plugins.php");
  }
  elseif($action == "newplugin")
  {
    $cms->assign("accesscontrol", 2);
    $cms->assign("createfiles", "on");
  }
  elseif($action == "newdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["name"]))
      {
        $action = "newplugin";
        $result = 1;
        $cms->assign("name", @$_POST["name"]);
        $cms->assign("title", @$_POST["title"]);
        $cms->assign("createfiles", @$_POST["createfiles"]);
        $cms->assign("accesscontrol", @$_POST["accesscontrol"]);
        $cms->assign("showigb", @$_POST["showigb"]);
        $cms->assign("showadmin", @$_POST["showadmin"]);
      }
      elseif($cms->PlugInNameExists($_POST["name"]))
      {
        $action = "newplugin";
        $result = 2;
        $cms->assign("name", @$_POST["name"]);
        $cms->assign("title", @$_POST["title"]);
        $cms->assign("createfiles", @$_POST["createfiles"]);
        $cms->assign("accesscontrol", @$_POST["accesscontrol"]);
        $cms->assign("showigb", @$_POST["showigb"]);
        $cms->assign("showadmin", @$_POST["showadmin"]);
      }
      else
      {
        $cms->NewPlugIn($_POST["name"], $_POST["title"], $_POST["accesscontrol"], (@$_POST["createfiles"] == "on" ? true : false), (@$_POST["showigb"] == "on" ? true : false), (@$_POST["showadmin"] == "on" ? true : false));
        $cms->Goto("plugins.php");
      }
    }
    else
      $cms->Goto("plugins.php");
  }
  elseif(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]))
  {
    $plugin = $cms->ReadPlugIn($_GET["delete"]);
    $cms->assign("id", $plugin->ID);
    $cms->assign("name", $plugin->Title);
    $action = "delete";
  }
  elseif($action == "deletedone" && is_numeric(@$_POST["id"]))
  {
    if($_POST["submit"] == "Delete")
    {
      $cms->DeletePlugIn($_POST["id"], @$_POST["deletefolder"] == "on" ? true : false);
      $cms->Goto("plugins.php");
    }
    else
      $cms->Goto("plugins.php");
  }
  elseif(isset($_GET["developer"]) && is_numeric(@$_GET["developer"]))
  {
    $plugin = $cms->ReadPlugIn($_GET["developer"]);
    $cms->assign("id", $plugin->ID);
    $cms->assign("developer", $plugin->Developer);
    $cms->assign("users", $cms->GetAllUsers(true, true));
    $action = "developer";
  }
  elseif($action == "developerdone")
  {
    if($_POST["submit"] == "Save")
    {
       
if(is_numeric($_POST["developer"])  && is_numeric($_POST["id"])) 
$cms->AssignPlugInDeveloper($_POST["id"], 
$_POST["developer"]);
      $cms->Goto("plugins.php");
    }
    else
      $cms->Goto("plugins.php");
  }
  elseif($action == "plugins")
  {
    $plugins = $cms->ReadAllPlugIns();
    $cms->assign("plugins", $plugins);
  }
  
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  
  $cms->display('plugins.tpl');  
?>
