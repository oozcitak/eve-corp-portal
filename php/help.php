<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  $article = $cms->ReadArticle(4);
  $cms->assign("help", $article);
  
  $cms->display('help.tpl');
?>
