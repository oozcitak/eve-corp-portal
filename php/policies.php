<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  $article = $cms->ReadArticle(2);
  $cms->assign("policies", $article);
  
  $cms->display('policies.tpl');
?>
