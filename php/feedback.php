<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  if(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]) && 
$cms->CurrentUser()->HasPortalRole(User::MDYN_Administrator))
  {
    $cms->CoreSQL("DELETE FROM feedback WHERE id=".$_GET["delete"]." LIMIT 1");
    $cms->Goto("feedback.php");
  }
  elseif($cms->CurrentUser()->HasPortalRole(User::MDYN_Administrator))
  {
    $feedbacks = array();
    $result = $cms->CoreSQL("SELECT * FROM feedback");
    while($row = mysql_fetch_assoc($result))
    {
      $feedbacks[] = array($cms->SQLUnEscape($row["Name"]), $cms->SQLUnEscape($row["EMail"]), $cms->SQLUnEscape($row["APIUserID"]), $cms->SQLUnEscape($row["APIKey"]), $cms->SQLUnEscape($row["Notes"]), $row["id"], $cms->GMTToLocal($row["Date"]));
    }
    $cms->assign("feedbacks", $feedbacks);
  }
  elseif(@$_GET["result"] == "1")
  {
    $cms->assign("result", 1);
  }
  elseif(@$_POST["submit"] == "Submit")
  {
    $query = "INSERT INTO feedback (Date,Name,Email,APIUserID,APIKey,Notes) VALUES (";
    $query .= "'".$cms->GMTTime()."',";
    $query .= "'".$cms->SQLEscape($_POST["name"])."',";
    $query .= "'".$cms->SQLEscape($_POST["email"])."',";
    $query .= "'".$cms->SQLEscape($_POST["apiuserid"])."',";
    $query .= "'".$cms->SQLEscape($_POST["apikey"])."',";
    $query .= "'".$cms->SQLEscape($_POST["notes"])."')";
    $cms->CoreSQL($query);
    $cms->Goto("feedback.php?result=1");
  }
  
  $cms->display('feedback.tpl');
?>
