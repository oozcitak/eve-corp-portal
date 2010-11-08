<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  // Login user
  $username = @$_POST["username"];
  $password = @$_POST["password"];  

  if($cms->Login($username, $password))
  {
    @session_start;
    if(isset($_SESSION["lastpage"]))
    {
      $lastpage = $_SESSION["lastpage"];
      unset($_SESSION["lastpage"]);
      if((stripos($lastpage, "login.php") !== FALSE) || (stripos($lastpage, "newpassword.php") !== FALSE) || (stripos($lastpage, "register.php") !== FALSE))
        $cms->Goto("home.php");
      else
        $cms->Goto($lastpage);
    }
    else
      $cms->Goto("home.php");
  }
  else
    $cms->Log("Login error. Wrong username (".$username.") or password.");
  
  $cms->display('login.tpl');
?>
