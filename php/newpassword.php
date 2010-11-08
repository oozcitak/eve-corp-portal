<?php
  require_once('../core/core.class.php');
  $cms = new Core();

  $result = 0;
  $cms->assign("username", @$_POST["username"]);
  $cms->assign("apiuserid", @$_POST["apiuserid"]);
  $cms->assign("apikey", @$_POST["apikey"]);
  
  if(isset($_POST["submit"]))
  {  
    if(empty($_POST["apiuserid"]) || empty($_POST["apikey"]))
    {
      $result = 1;
    }
    elseif(empty($_POST["username"]))
    {
      $result = 7;
    }
    elseif(empty($_POST["password1"]) || empty($_POST["password2"]))
    {
      $result = 4;
    }
    elseif($_POST["password1"] != $_POST["password2"])
    {
      $result = 5;
    }
    else
    {
      $result = $cms->CoreSQL("SELECT * FROM users WHERE Name='".$cms->SQLEscape($_POST["username"])."' OR FIND_IN_SET('".$cms->SQLEscape($_POST["username"])."', Alts) LIMIT 1");
      if(mysql_num_rows($result) == 0)
      {
        $result = 6;
      }
      else
      {
        $res = $cms->GetUserCharacters($_POST["apiuserid"], $_POST["apikey"]);

        if($res === FALSE)
        {
          $result = 2;
        }
        elseif(empty($res))
        {
          $result = 3;
        }
        elseif(count($res) > 0)
        {
          foreach($res as $char)
          {
            if($char["Name"] == $_POST["username"])
            {
              $result = 8;
              $cms->CoreSQL("UPDATE users SET Password='".md5($_POST["password1"])."' WHERE Name='".$cms->SQLEscape($_POST["username"])."' OR FIND_IN_SET('".$cms->SQLEscape($_POST["username"])."', Alts) LIMIT 1");
              break;
            }
          }
          if($result != 8) $result = 6;
        }
      }
    }
  }
  
  $cms->assign('result', $result);
  $cms->display('newpassword.tpl');
?>
