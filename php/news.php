<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  $action = @$_GET["action"];
  if(empty($action)) $action = "home";
  $result = 0;
  
  $isadmin = $cms->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $cms->CurrentUser()->HasPortalRole(User::MDYN_Administrator) || $cms->CurrentUser()->HasEVERole(User::EVE_Director) ? 1 : 0;
  $cms->assign("isadmin", $isadmin);
  if($cms->CurrentUser()->HasPortalRole(User::MDYN_CanSubmitNews) || $cms->CurrentUser()->AccessRight() >= 3) $cms->assign("canpost", true);
  
  if(isset($_GET["read"]) && is_numeric(@$_GET["read"]))
  {
    // Show news item from the archive
    $note = $cms->ReadNewsItem($_GET["read"]);
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
    $note = $cms->ReadNewsItem($_GET["edit"]);
    $_POST["id"] = $note->ID;
    $_POST["title"] = $note->Title;
    $_POST["text"] = $note->Text;
    $_POST["readaccess"] = $note->ReadAccess;
    $action = "edit";
  }
  elseif(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]))
  {
    $cms->DeleteNewsItem($_GET["delete"]);
    $cms->Goto("news.php");
  }
  elseif($action == "newdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["text"]))
      {
        $action = "new";
        $result = 1;
      }
      else
      {
        $cms->InsertNewsItem($_POST["title"], $_POST["text"], $_POST["readaccess"]);
        $cms->Goto("news.php");
      }
    }
    else
      $cms->Goto("news.php");
  }
  elseif($action == "editdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["text"]))
      {
        $action = "edit";
        $result = 1;
      }
      else
      {
        if(is_numeric($_POST["id"])) $cms->EditNewsItem($_POST["id"], 
$_POST["title"], 
$_POST["text"], $_POST["readaccess"]);
        $cms->Goto("news.php");
      }
    }
    elseif($_POST["submit"] == "Delete")
    {
      if(is_numeric($_POST["id"])) $cms->DeleteNewsItem($_POST["id"]);
      $cms->Goto("news.php");
    }
    else
      $cms->Goto("news.php");
  }
  elseif($action == "archive")
  {
    // List all news
    $news = $cms->ReadAllNews();
    $cms->assign("news", $news);
  }
  elseif($action == "home")
  {
    // List all news
    $news = $cms->ReadNews();
    $cms->assign("news", $news);
  }

  if(!isset($_POST["readaccess"])) $_POST["readaccess"] = 2;
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  $cms->assign("id", @$_POST["id"]);
  $cms->assign("title", @$_POST["title"]);
  $cms->assign("text", @$_POST["text"]);
  $cms->assign("readaccess", @$_POST["readaccess"]);
  
  $cms->display('news.tpl');
?>
