<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  if(!$cms->AccessCheck()) { header("Location: access.php"); exit; }

  $article = $cms->ReadArticle(3);
  $cms->assign("quickinfo", $article);
  
  $cms->display('quickinfo.tpl');
?>
