<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  if($cms->CurrentUser()->Name == "Guest") { header("Location: access.php"); exit; }

  $action = @$_GET["action"];
  if(empty($action)) $action = "home";
  $result = 0;
  
  if(isset($_GET["read"]) && is_numeric(@$_GET["read"]))
  {
    $note = $cms->ReadNotepad($_GET["read"]);
    $_POST["id"] = $note->ID;
    $_POST["title"] = $note->Title;
    if($cms->IsIGB()) 
      $_POST["text"] = strip_tags($note->Text);
    else
      $_POST["text"] = $note->Text;
    $action = "read";
  }
  elseif($action == "home")
  {
    $titles = $cms->GetNotepadTitles();
    $cms->assign("titles", $titles);
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
        $cms->NewNotepad($_POST["title"], $_POST["text"]);
        $cms->Goto("notepad.php");
      }
    }
    else
      $cms->Goto("notepad.php");
  }
  elseif($action == "editdone")
  {
    if($_POST["submit"] == "Save")
    {
      if(empty($_POST["title"]) || empty($_POST["text"]))
      {
        $action = "read";
        $result = 1;
      }
      else
      {
        if(is_numeric($_POST["id"])) 
$cms->EditNotepad($_POST["id"], $_POST["title"], 
$_POST["text"]);
        $cms->Goto("notepad.php");
      }
    }
    elseif($_POST["submit"] == "Delete" && 
is_numeric(@$_POST["id"]))
    {
      $cms->DeleteNotepad($_POST["id"]);
      $cms->Goto("notepad.php");
    }
    else
      $cms->Goto("notepad.php");
  }
 
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  $cms->assign("id", @$_POST["id"]);
  $cms->assign("title", @$_POST["title"]);
  $cms->assign("text", @$_POST["text"]);
  
  $cms->display('notepad.tpl');
?>
