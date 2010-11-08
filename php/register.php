<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  $step = @$_POST["step"];
  if(empty($step)) $step = 1;
  $result = 0;
  
  $cms->assign("apiuserid", @$_POST["apiuserid"]);
  $cms->assign("apikey", @$_POST["apikey"]);
  $cms->assign("charid", @$_POST["charid"]);
  $cms->assign("charname", @$_POST["charname"]);
  $cms->assign("corpname", @$_POST["corpname"]);
  $cms->assign("corpticker", @$_POST["corpticker"]);
  $cms->assign("corpid", @$_POST["corpid"]);
  
  if($step == 2)
  {  
    if(empty($_POST["apiuserid"]) | empty($_POST["apikey"]))
    {
      $result = 1;
      $step = 1;
    }
    else
    {
      $res = $cms->GetUserCharacters($_POST["apiuserid"], $_POST["apikey"]);

      if($res === FALSE)
      {
        $result = 2;
        $step = 1;
      }
      elseif(empty($res))
      {
        $result = 3;
        $step = 1;
      }
      elseif(count($res) == 1)
      {
        $result = 4;
        $step = 2;
        $cms->assign("characters", $res);
        $cms->assign("allusers", $cms->GetRegisteredUserNames());
      }
      else
      {
        $result = 5;
        $step = 2;
        $cms->assign("characters", $res);
        $cms->assign("allusers", $cms->GetRegisteredUserNames());
      }
    }
  }
  elseif($step == 3)
  {
    $cms->assign("charid", $_POST["char"]);
    $cms->assign("charname", $_POST["name_".$_POST["char"]]);
    $cms->assign("corpname", $_POST["corp_".$_POST["char"]]);
    $cms->assign("corpticker", $_POST["corpticker_".$_POST["char"]]);
    $cms->assign("corpid", $_POST["corpid_".$_POST["char"]]);
    if($cms->CharacterIDExists($_POST["char"]))
    {
      $res = $cms->GetUserCharacters($_POST["apiuserid"], $_POST["apikey"]);
      $cms->assign("characters", $res);
      $cms->assign("allusers", $cms->GetRegisteredUserNames());
      $result = 8;
      $step = 2;
    }
  }
  elseif($step == 4)
  {
    if(empty($_POST["password1"]) || empty($_POST["password2"]))
    {
      $result = 6;
      $step = 3;
    }
    elseif($_POST["password1"] != $_POST["password2"])
    {
      $result = 7;
      $step = 3;
    }
    else
      $cms->RegisterNewUser($_POST["apiuserid"], $_POST["apikey"], $_POST["charid"], $_POST["charname"], $_POST["password1"], $_POST["corpid"], $_POST["corpname"], $_POST["corpticker"]);
  }
  
  $cms->assign("step", $step);
  $cms->assign("result", $result);
  
  $cms->display('register.tpl');
?>
