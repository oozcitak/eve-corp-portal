<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  $action = @$_GET["action"];
  $folder = @$_GET["folder"];
  $message = "";
  $result = 0;
  
  if(isset($_GET["read"]) && is_numeric(@$_GET["read"]))
  {
    $action = "read";
    $cms->MarkMailRead($_GET["read"], true);
    $message = $cms->ReadMail($_GET["read"]);
  }
  elseif(isset($_GET["reply"]) && is_numeric(@$_GET["reply"]))
  {
    $action = "reply";
    $message = $_GET["reply"];
  }
  elseif(isset($_GET["replytoall"]) && is_numeric(@$_GET["replytoall"]))
  {
    $action = "replytoall";
    $message = $_GET["replytoall"];
  }
  elseif(isset($_GET["forward"]) && is_numeric(@$_GET["forward"]))
  {
    $action = "forward";
    $message = $_GET["forward"];
  }
  elseif(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]))
  {
    $action = "delete";
    $message = $_GET["delete"];
  }
  elseif(isset($_GET["move"]) && is_numeric(@$_GET["move"]))
  {
    $action = "move";
    $message = $_GET["move"];
  }
  elseif(empty($action))
  {
    $action = "inbox";
  }
  
  if(($action == "inbox") || ($action == "sentitems"))
  {
    $isinbox = ($action == "inbox");
    $sort = @$_GET["order"];
    $page = 0;
    if(isset($_GET["page"]) && is_numeric(@$_GET["page"])) $page = 
$_GET["page"] - 1;
    $pagecount = ceil($cms->MailBoxCount($isinbox, $folder) / 20);
    if($page > $pagecount - 1) $page = $pagecount - 1;
    if($page < 0) $page = 0;
    
    $messages = $cms->ReadMailBox($isinbox, $folder, $page * 20, 20, $sort);
    $cms->assign("messages", $messages);
    $cms->assign("page", $page);
    $cms->assign("pagecount", $pagecount);
  }
  elseif(($action == "search"))
  {
    $query = @$_GET["query"];
    $mailbox = @$_GET["mailbox"];
    if(empty($query))
      $cms->Goto("mail.php?action=".$mailbox);
      
    $isinbox = ($mailbox == "inbox");    
    $messages = $cms->SearchMailBox($query, $isinbox, $folder);
    $cms->assign("messages", $messages);
    $cms->assign("mailbox", $mailbox);
    $cms->assign("query", $query);
  }
  elseif($action == "compose" || $action == "reply" || $action == "replytoall" || $action == "forward")
  {
    $names = $cms->GetAllUserNames();
    $lists = array(-1 => "*Everyone*", -2 => "*Corporation Members*", -3 => "*Managers*", -4 => "*Directors and CEO*");
    $cms->assign("names", $lists + $names);
    if($action == "reply" || $action == "replytoall" || $action == "forward")
    {
      $message = $cms->ReadMail($message);
      $subject = $message->Title;
      $to = "";
      $toid = "";
      $cc = "";
      $ccid = "";

      if($action == "reply" || $action == "replytoall")
      {
        if(strcasecmp(substr($subject, 0, 3), "RE:") != 0)
          $subject = "RE: ".$subject;
      }
      elseif($action == "forward")
      {
        if(strcasecmp(substr($subject, 0, 3), "FW:") != 0)
          $subject = "FW: ".$subject;
      }

      $text = "<p>&nbsp;</p><hr size='0' /><p><b>".$message->FromName."</b> to ".$message->ToName."</p>".$message->Text;
      
      if($action == "reply")
      {
        $to = $names[$message->From];
        $toid = $message->From;
      }
      elseif($action == "replytoall")
      {
        $to = array();
        $toid = array();
        $cc = array();
        $ccid = array();
        
        foreach($message->To as $val)
        {
          if($val != $cms->CurrentUser()->ID)
          {
            $to[] = $names[$val];
            $toid[] = $val;
          }
        }
        foreach($message->CC as $val)
        {
          if($val != $cms->CurrentUser()->ID)
          {
            $cc[] = $names[$val];
            $ccid[] = $val;
          }
        }
        $to = implode(",", $to);
        $toid = implode(",", $toid);
        $cc = implode(",", $cc);
        $ccid = implode(",", $ccid);
      }
      
      $action = "compose";
      $cms->assign("subject", $subject);
      $cms->assign("text", $text);
      $cms->assign("to", $to);
      $cms->assign("toid", $toid);
      $cms->assign("cc", $cc);
      $cms->assign("ccid", $ccid);
    }
  }
  elseif($action == "composedone")
  {
    if($_POST["submit"] == "Send")
    {
      $subject = @$_POST["subject"];
      $text = @$_POST["text"];
      $to = @$_POST["to"];
      $cc = @$_POST["cc"];
      $bcc = @$_POST["bcc"];
      $toid = @$_POST["toid"];
      $ccid = @$_POST["ccid"];
      $bccid = @$_POST["bccid"];
      
      $cms->assign("subject", $subject);
      $cms->assign("text", $text);
      $cms->assign("to", $to);
      $cms->assign("cc", $cc);
      $cms->assign("bcc", $bcc);
      $cms->assign("toid", $toid);
      $cms->assign("ccid", $ccid);
      $cms->assign("bccid", $bccid);
      $names = $cms->GetAllUserNames();
      $lists = array(-1 => "*Everyone*", -2 => "*Corporation Members*", -3 => "*Managers*", -4 => "*Directors and CEO*");
      $cms->assign("names", $lists + $names);
      
      if(empty($subject) || empty($text))
      {
        $result = 1;
        $action = "compose";
      }
      elseif(empty($to))
      {
        $result = 2;
        $action = "compose";
      }
      else
      {
        $cms->SendMail($subject, $text, $toid, $ccid, $bccid);
        $cms->Goto("mail.php");
      }
    }
    elseif($_POST["submit"] == "Add To")
    {
      $subject = @$_POST["subject"];
      $text = @$_POST["text"];
      $to = @$_POST["to"];
      $toid = @$_POST["toid"];
    
      $names = $cms->GetAllUserNames();
      $lists = array(-1 => "*Everyone*", -2 => "*Corporation Members*", -3 => "*Managers*", -4 => "*Directors and CEO*");
      $names = $lists + $names;
      $cms->assign("names", $names);
      
      $selid = $_POST["names"];
      $selname = $names[$selid];
      if(empty($to))
      {
        $to = $selname;
        $toid = $selid;
      }
      else
      {
        $to = implode(",", array_unique(explode(",", $to . "," . $selname)));
        $toid = implode(",", array_unique(explode(",", $toid . "," . $selid)));
      }
    
      $cms->assign("subject", $subject);
      $cms->assign("text", $text);
      $cms->assign("to", $to);
      $cms->assign("toid", $toid);
    
      $action = "compose";
    }
    else
      $cms->Goto("mail.php");
  }
  elseif($action == "delete")
  {
    $cms->DeleteMail($message);
    $cms->Goto("mail.php");
  }
  elseif($action == "do")
  {
    $todo = $_POST["dowhat"];
    
    $ids = array();
    foreach($_POST as $key => $value)
    {
      if((substr($key, 0, 8) == "mailitem") && ($value == "on"))
        $ids[] = substr($key, 8);
    }    
    
    if($todo == "delete")
      $cms->DeleteMail($ids);
    elseif($todo == "markread")
      $cms->MarkMailRead($ids, true);
    elseif($todo == "markunread")
      $cms->MarkMailRead($ids, false);
      
    $cms->Goto("mail.php?action=".$_POST["mailbox"]);
  }
  
  $cms->assign("action", $action);
  $cms->assign("folder", $folder);
  $cms->assign("message", $message);
  $cms->assign("result", $result);
  
  $cms->display('mail.tpl');
?>
