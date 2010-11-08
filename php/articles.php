<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  $action = @$_GET["action"];
  if(empty($action)) $action = "home";
  $result = 0;
  
  if(isset($_GET["read"]) && is_numeric(@$_GET["read"]))
  {
    $note = $cms->ReadArticle($_GET["read"]);
    $_POST["id"] = $note->ID;
    $_POST["title"] = $note->Title;
    $_POST["text"] = $note->Text;
    $_POST["readaccess"] = $note->ReadAccess;
    $_POST["writeaccess"] = $note->WriteAccess;
    $cms->assign("articleid", $note->ID);
    $cms->assign("authorid", $note->Author);
    $cms->assign("author", $note->AuthorName);
    $cms->assign("signature", $note->AuthorSignature);
    $cms->assign("date", $note->Date);
    $cms->assign("editid", $note->WriteAccess <= $cms->CurrentUser()->AccessRight() || $note->Author == $cms->CurrentUser()->ID ? $note->ID : 0);
    $cms->assign("isadmin", $note->Author != $cms->CurrentUser()->ID ? 1 : 0);
    $cms->assign("comments", $note->Comments);
    $action = "read";
  }
  elseif(isset($_GET["edit"])&& is_numeric(@$_GET["edit"]))
  {
    $note = $cms->ReadArticle($_GET["edit"]);
    $_POST["id"] = $note->ID;
    $_POST["title"] = $note->Title;
    $_POST["text"] = $note->Text;
    $_POST["readaccess"] = $note->ReadAccess;
    $_POST["writeaccess"] = $note->WriteAccess;
    $action = "edit";
  }
  elseif(isset($_GET["deletecomment"]) && is_numeric(@$_GET["deletecomment"]))
  {
    $article = $_GET["article"];
    $id = $_GET["deletecomment"];
    $cms->DeleteArticleComment($id);
    $cms->Goto("articles.php?read=".$article);
  }
  elseif(isset($_GET["postcomment"]) && is_numeric(@$_GET["postcomment"]))
  {
    $article = $_GET["postcomment"];
    $cms->assign("articleid", $article);
    $action = "postcomment";
  }
  elseif($action == "home")
  {
    $titles = $cms->GetArticleTitles();
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
        $cms->NewArticle($_POST["title"], $_POST["text"], $_POST["readaccess"], $_POST["writeaccess"]);
        $cms->Goto("articles.php");
      }
    }
    else
      $cms->Goto("articles.php");
  }
  elseif($action == "editdone" && is_numeric(@$_POST["id"]))
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
        $cms->EditArticle($_POST["id"], $_POST["title"], $_POST["text"], $_POST["readaccess"], $_POST["writeaccess"]);
        $cms->Goto("articles.php");
      }
    }
    elseif($_POST["submit"] == "Delete" && is_numeric(@$_POST["id"]))
    {
      $cms->DeleteArticle($_POST["id"]);
      $cms->Goto("articles.php");
    }
    else
      $cms->Goto("articles.php");
  }
  elseif($action == "newcomment" && is_numeric(@$_POST["article"]))
  {
    $id = $_POST["article"];
    $text = @$_POST["text"];
    if(!empty($text)) $cms->NewArticleComment($id, $text);
    $cms->Goto("articles.php?read=".$id);
  }
  
  if(!isset($_POST["readaccess"])) $_POST["readaccess"] = 2;
  if(!isset($_POST["writeaccess"])) $_POST["writeaccess"] = 4;
  $cms->assign("action", $action);
  $cms->assign("result", $result);
  $cms->assign("id", @$_POST["id"]);
  $cms->assign("title", @$_POST["title"]);
  $cms->assign("text", @$_POST["text"]);
  $cms->assign("readaccess", @$_POST["readaccess"]);
  $cms->assign("writeaccess", @$_POST["writeaccess"]);
  
  $cms->display('articles.tpl');
?>
