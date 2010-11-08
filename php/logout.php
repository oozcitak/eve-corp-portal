<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  $cms->Logout();
  $cms->Goto("home.php");
  exit;
?>
