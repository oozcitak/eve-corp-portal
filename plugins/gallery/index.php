<?php
require_once('../../core/core.class.php');
$core = new Core();

//Access control
//if($core->CurrentUser()->AccessRight() < 1) $core->Goto('../../php/access.php');
if($core->CurrentUser()->Name == "Guest") $core->Goto('../../php/access.php');

if($core->CurrentUser()->AccessRight() == 0)
{
    $action = @$_GET["action"];
    if(isset($_GET["show"])) $action = "show";
    if(isset($_GET["delete"])) $action = "delete";
    if(isset($_GET["deletecomment"])) $action = "user";
    if(isset($_GET["search"])) $action = "user";
    if(empty($action)) $action = "home";
}
else
{
    $action = @$_GET["action"];
    if(isset($_GET["show"])) $action = "show";
    if(isset($_GET["delete"])) $action = "delete";
    if(isset($_GET["deletecomment"])) $action = "deletecomment";
    if(isset($_GET["search"])) $action = "search";
    if(isset($_GET["recruitment"])) $action = "recruitment";
    if(empty($action)) $action = "home";
}

if($action == "home" || $action == "user" || $action == "recruitment")
{
  $page = @$_GET["page"];
  if(empty($page)) $page = 1;
  $max = 25;

  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images";
  if(!file_exists($path))
    mkdir($path);

  $baseurl = "http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/images/";
  $names = $core->GetAllUserNames();

  if($action == "home")
  {
    if($core->CurrentUser()->AccessRight() == 0)
    {
    $result = $core->SQL("SELECT COUNT(*) FROM gallery");
    $pagecount = ceil(mysql_result($result, 0) / $max);
    $result = $core->SQL("SELECT * FROM gallery WHERE Owner=".$core->CurrentUser()->ID." AND AccessRight=1 ORDER BY Date DESC LIMIT ".($max * ($page - 1)).",".$max);
    }
    else
    {
    $result = $core->SQL("SELECT COUNT(*) FROM gallery");
    $pagecount = ceil(mysql_result($result, 0) / $max);
    $result = $core->SQL("SELECT * FROM gallery WHERE ".$core->CurrentUser()->AccessRight().">=AccessRight AND AccessRight!=-1 AND AccessRight!=1 ORDER BY Date DESC LIMIT ".($max * ($page - 1)).",".$max);
    }
  }
  elseif($action == "recruitment")
  {
    if($core->CurrentUser()->AccessRight() < 3) $core->Goto('../../php/access.php');
    $recruitment = $_GET["recruitment"];
     if ($recruitment == "all")
    {
        $result = $core->SQL("SELECT COUNT(*) FROM gallery");
        $pagecount = ceil(mysql_result($result, 0) / $max);
        $result = $core->SQL("SELECT * FROM gallery WHERE AccessRight=1 ORDER BY Date DESC LIMIT ".($max * ($page - 1)).",".$max);
    }
    else
    {
        $core->assign("recruitname", $core->GetUserFromID($recruitment)->Name);
        $core->assign("recruitment", $recruitment);

        $result = $core->SQL("SELECT COUNT(*) FROM gallery WHERE Owner=".$recruitment);
        $pagecount = ceil(mysql_result($result, 0) / $max);
        $result = $core->SQL("SELECT * FROM gallery WHERE Owner=".$recruitment." AND AccessRight=1 ORDER BY Date DESC LIMIT ".($max * ($page - 1)).",".$max);
    }
  }
  else
  {
    if($core->CurrentUser()->AccessRight() == 0)
    {
    $result = $core->SQL("SELECT COUNT(*) FROM gallery");
    $pagecount = ceil(mysql_result($result, 0) / $max);
    $result = $core->SQL("SELECT * FROM gallery WHERE Owner=".$core->CurrentUser()->ID." AND AccessRight=1 ORDER BY Date DESC LIMIT ".($max * ($page - 1)).",".$max);
    }
    else
    {
    $result = $core->SQL("SELECT COUNT(*) FROM gallery WHERE Owner=".$core->CurrentUser()->ID);
    $pagecount = ceil(mysql_result($result, 0) / $max);
    $result = $core->SQL("SELECT * FROM gallery WHERE Owner=".$core->CurrentUser()->ID." AND AccessRight!=1 ORDER BY Date DESC LIMIT ".($max * ($page - 1)).",".$max);
    }
  }
  $gallery = array();
  while($row = mysql_fetch_assoc($result))
  {
    $name = $row["Owner"]."_".$row["id"].".".$row["Extension"];
    $thumb = $row["Owner"]."_".$row["id"]."_thumb.jpg";
    // Check and create thumbnail
    CheckThumb($path, $row["Owner"], $row["id"], $row["Extension"]);
    if(file_exists($path.DIRECTORY_SEPARATOR.$name) && file_exists($path.DIRECTORY_SEPARATOR.$thumb))
      $gallery[] = array("ID" => $row["id"], "Owner" => $names[$row["Owner"]], "Title" => $core->SQLUnEscape($row["Title"]), "Date" => $core->GMTToLocal($row["Date"]), "URL" => $baseurl.$name, "ThumbURL" => $baseurl.$thumb);
  }

  $core->assign("page", $page);
  $core->assign("pagecount", $pagecount);
  $core->assign("gallery", $gallery);
}
elseif($action == "search")
{
  $searchtext = @$_GET["search"];
  if(!empty($searchtext))
  {
    $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images";
    if(!file_exists($path))
      mkdir($path);

    $baseurl = "http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/images/";
    $names = $core->GetAllUserNames();

    $result = $core->SQL("SELECT * FROM gallery WHERE Title LIKE '%".$core->SQLEscape($searchtext)."%' AND ".$core->CurrentUser()->AccessRight().">=AccessRight AND AccessRight!=-1 AND AccessRight!=1 ORDER BY Date DESC LIMIT 25");
    $gallery = array();
    while($row = mysql_fetch_assoc($result))
    {
      $name = $row["Owner"]."_".$row["id"].".".$row["Extension"];
      $thumb = $row["Owner"]."_".$row["id"]."_thumb.jpg";
      // Check and create thumbnail
      CheckThumb($path, $row["Owner"], $row["id"], $row["Extension"]);
      if(file_exists($path.DIRECTORY_SEPARATOR.$name) && file_exists($path.DIRECTORY_SEPARATOR.$thumb))
        $gallery[] = array("ID" => $row["id"], "Owner" => $names[$row["Owner"]], "Title" => $core->SQLUnEscape($row["Title"]), "Date" => $core->GMTToLocal($row["Date"]), "URL" => $baseurl.$name, "ThumbURL" => $baseurl.$thumb);
    }
    $core->assign("gallery", $gallery);
  }
  $core->assign("searchtext", $searchtext);
}
elseif($action == "show")
{
  if(isset($_GET["recruitname"])) $core->assign("recruitname", $_GET["recruitname"]);
  if(isset($_GET["portalid"])) $core->assign("portalid", $_GET["portalid"]);
  $id = $_GET["show"];
  $baseurl = "http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/images/";
  $names = $core->GetAllUserNames();

  $result = $core->SQL("SELECT * FROM gallery WHERE id=".$id);
  if(mysql_num_rows($result) == 0)
    $core->assign("image", "");
  else
  {
    $row = mysql_fetch_assoc($result);
    $name = $row["Owner"]."_".$row["id"].".".$row["Extension"];
    $image = array("ID" => $row["id"], "Owner" => $names[$row["Owner"]], "Title" => $core->SQLUnEscape($row["Title"]), "Date" => $core->GMTToLocal($row["Date"]), "URL" => $baseurl.$name);
    $canedit = 0;
    if($core->CurrentUser()->AccessRight() >= 4) $canedit = 2;
    if($row["Owner"] == $core->CurrentUser()->ID) $canedit = 1;
    // Comments
    $result = $core->SQL("SELECT id,Date,User,Comment FROM gallery_comments WHERE Image=".$id." ORDER BY Date ASC");
    $comments = array();
    while($row = mysql_fetch_assoc($result))
    {
      $comments[] = array("ID" => $row["id"], "Date" => $row["Date"], "UserID" => $row["User"], "User" => $names[$row["User"]], "Text" => $core->SQLUnEscape($row["Comment"]));
    }
    $core->assign("canedit", $canedit);
    $core->assign("image", $image);
    $core->assign("comments", $comments);
  }
}
elseif($action == "upload")
{
    if($core->CurrentUser()->AccessRight() == 0)
    {
        $setid = $core->CurrentUser()->ID;
        $result2 = $core->SQL("SELECT * FROM `recruitment` WHERE id = ".$setid);
        while($row = mysql_fetch_assoc($result2))
        {
            $recruitstatus = $row['status'];
        }
        if($recruitstatus == 2 || $recruitstatus == 3)
        {
        }
        else
        {
            $core->Goto('../../php/access.php');
        }
    }
  $maxsize = 2; // GB
  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR;
  $dir = opendir($path);
  $count = 0;
  $size = 0;
  while(($file = readdir($dir)) !== false)
  {
    $name = $path.$file;
    if(!is_dir($name))
    {
      $count += 1;
      $size += filesize($name);
    }
  }
  closedir($dir);
  $percent = min(100, round($size / (1024 * 1024 * 1024) / $maxsize * 100, 0));
  if($size > (1024 * 1024 * 1024))
    $size = round($size / (1024 * 1024 * 1024), 2)."&nbsp;GB";
  elseif($size > (1024 * 1024))
    $size = round($size / (1024 * 1024), 2)."&nbsp;MB";
  elseif($size > 1024)
    $size = round($size / 1024, 2)."&nbsp;KB";
  else
    $size = $size."&nbsp;Bytes";

  $core->assign("count", $count);
  $core->assign("size", $size);
  $core->assign("percent", $percent);
  $core->assign("maxsize", $maxsize);
}
elseif($action == "uploaddone")
{
  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images";

  $type = $_FILES["file"]["type"];
  $size = $_FILES["file"]["size"];
  $error = $_FILES["file"]["error"];
  $tmpname = $_FILES["file"]["tmp_name"];
  $allowedtypes = array("image/gif", "image/png", "image/jpeg", "image/pjpeg", "image/bmp");
  $title = $_POST["title"];
  $access = $_POST["readaccess"];
  $id = time();

  if(in_array($type, $allowedtypes) && ($size <= (5 * 1024 * 1024)) && ($error == 0))
  {
    if($type == "image/gif")
      $ext = "gif";
    elseif($type == "image/png")
      $ext = "png";
    elseif(($type == "image/jpeg") || ($type == "image/pjpeg"))
      $ext = "jpg";
    elseif($type == "image/bmp")
      $ext = "bmp";

    $name = $core->CurrentUser()->ID."_".$id.".".$ext;
    move_uploaded_file($tmpname, $path.DIRECTORY_SEPARATOR.$name);

    $query = "INSERT INTO gallery (id,Date,Owner,Title,Extension,AccessRight) VALUES (";
    $query .= $id.",";
    $query .= "'".$core->GMTTime()."',";
    $query .= $core->CurrentUser()->ID.",";
    $query .= "'".$core->SQLEscape($title)."',";
    $query .= "'".$ext."',";
    $query .= $access.")";
    $core->SQL($query);

    if($core->CurrentUser()->AccessRight() == 0)
    {
        $core->Goto("index.php?action=user&result=1");
    }
    else
    {
    	$core->Goto("index.php?action=user&result=1");
    }
  }
  else
    $core->Goto("index.php?action=upload&result=2");
}
elseif($action == "delete")
{
  $id = $_GET["delete"];
  $result = $core->SQL("SELECT id,Owner,Extension FROM gallery WHERE id=".$id);
  $row = mysql_fetch_assoc($result);
  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images";

  $name = $row["Owner"]."_".$row["id"].".".$row["Extension"];
  unlink($path.DIRECTORY_SEPARATOR.$name);

  $thumb = $row["Owner"]."_".$row["id"]."_thumb.jpg";
  unlink($path.DIRECTORY_SEPARATOR.$thumb);

  $core->SQL("DELETE FROM gallery WHERE id=".$id." LIMIT 1");
  $core->Goto("index.php");
}
elseif($action == "admin")
{
  if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');

  $maxsize = 2; // GB
  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR;
  $dir = opendir($path);
  $count = 0;
  $size = 0;
  while(($file = readdir($dir)) !== false)
  {
    $name = $path.$file;
    if(!is_dir($name))
    {
      $count += 1;
      $size += filesize($name);
    }
  }
  closedir($dir);
  $percent = min(100, round($size / (1024 * 1024 * 1024) / $maxsize * 100, 0));
  if($size > (1024 * 1024 * 1024))
    $size = round($size / (1024 * 1024 * 1024), 2)."&nbsp;GB";
  elseif($size > (1024 * 1024))
    $size = round($size / (1024 * 1024), 2)."&nbsp;MB";
  elseif($size > 1024)
    $size = round($size / 1024, 2)."&nbsp;KB";
  else
    $size = $size."&nbsp;Bytes";

  $core->assign("count", $count);
  $core->assign("size", $size);
  $core->assign("percent", $percent);
  $core->assign("maxsize", $maxsize);
}
elseif($action == "delete1year" || $action == "delete6month" || $action == "delete1month")
{
  if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');

  $users = $core->GetAllUsers(false, true);
  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images";

  $limit = strtotime(gmdate("Y-m-d"));
  if($action == "delete1year")
    $limit -= 365 * 24 * 60 * 60;
  elseif($action == "delete6month")
    $limit -= 6 * 30 * 24 * 60 * 60;
  elseif($action == "delete1month")
    $limit -= 30 * 24 * 60 * 60;
  $limit = date("Y-m-d", $limit);

  $result = $core->SQL("SELECT id,Owner,Extension FROM gallery WHERE Date<'".$limit."'");
  $ids = array();
  while($row = mysql_fetch_assoc($result))
  {
    $name = $row["Owner"]."_".$row["id"].".".$row["Extension"];
    $thumb = $row["Owner"]."_".$row["id"]."_thumb.jpg";

    $check = true;
    foreach($users as $user)
    {
      if(stripos($user->Signature, $name) !== FALSE)
        $check = false;
    }
    if($check)
    {
      unlink($path.DIRECTORY_SEPARATOR.$name);
      unlink($path.DIRECTORY_SEPARATOR.$thumb);

      $ids[] = $row["id"];
    }
  }
  $core->SQL("DELETE FROM gallery WHERE FIND_IN_SET(id,'".implode(",",$ids)."')");
  $core->Goto("index.php?action=admin");
}
elseif($action == "deleteall")
{
  if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');

  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR;
  $dir = opendir($path);
  while(($file = readdir($dir)) !== false)
  {
    $name = $path.$file;
    if(!is_dir($name)) unlink($name);
  }
  closedir($dir);
  $core->SQL("DELETE FROM gallery");

  $core->Goto("index.php?action=admin");
}
elseif($action == "deletethumbs")
{
  if($core->CurrentUser()->AccessRight() < 4) $core->Goto('../../php/access.php');

  $path = dirname(__FILE__).DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR;
  $dir = opendir($path);
  while(($file = readdir($dir)) !== false)
  {
    $name = $path.$file;
    if(!is_dir($name) && (substr($name, -10) == "_thumb.jpg")) unlink($name);
  }
  closedir($dir);

  $core->Goto("index.php?action=admin");
}
elseif($action == "comment")
{
  $id = $_POST["image"];
  $text = @$_POST["comment"];
  if(!empty($text))
  {
    $core->SQL("INSERT INTO gallery_comments (Image,Date,User,Comment) VALUES (".$id.",'".$core->GMTTime()."',".$core->CurrentUser()->ID.",'".$core->SQLEscape($text)."')");
  }
  $core->Goto("index.php?show=".$id);
}
elseif($action == "deletecomment")
{
  $id = $_GET["deletecomment"];
  $core->SQL("DELETE FROM gallery_comments WHERE id=".$id);
  $core->Goto("index.php?show=".$_GET["image"]);
}

$core->assign("action", $action);
$core->assign("result", @$_GET["result"]);
$core->display($core->PlugInPath."gallery/gallery.tpl");

// *****************************************************
// Check if the thumbnail for the given image exists
// Create the thumbnail if necessary
// *****************************************************
function CheckThumb($path, $owner, $id, $ext)
{
  $name = $owner."_".$id.".".$ext;
  $thumb = $owner."_".$id."_thumb.jpg";

  if(file_exists($path.DIRECTORY_SEPARATOR.$name))
  {
    if(!file_exists($path.DIRECTORY_SEPARATOR.$thumb))
    {
      if($ext == "gif")
        $im = @imagecreatefromgif($path.DIRECTORY_SEPARATOR.$name);
      elseif($ext == "jpg")
        $im = @imagecreatefromjpeg($path.DIRECTORY_SEPARATOR.$name);
      elseif($ext == "png")
        $im = @imagecreatefrompng($path.DIRECTORY_SEPARATOR.$name);
      elseif($ext == "bmp")
        $im = @imagecreatefromwbmp($path.DIRECTORY_SEPARATOR.$name);

      if(!$im)
      {
        $ims = imagecreatetruecolor(140, 20);
        $bgc = imagecolorallocate($ims, 255, 255, 255);
        $tc  = imagecolorallocate($ims, 0, 0, 0);
        imagefilledrectangle($ims, 0, 0, 140, 20, $bgc);
        imagestring($ims, 1, 5, 5, "Error creating thumbnail.", $tc);
        imagejpeg($ims, $path.DIRECTORY_SEPARATOR.$thumb);
      }
      else
      {
        $w = imagesx($im);
        $h = imagesy($im);
        
        if(max($w, $h) <= 140)
        {
          $ws = $w;
          $hs = $h;
        }
        elseif($w > $h)
        {
          $ws = 140;
          $hs = 140 / $w * $h;
        }
        else
        {
          $hs = 140;
          $ws = 140 / $h * $w;
        }
        
        $ims = imagecreatetruecolor($ws, $hs);
        imagecopyresampled($ims, $im, 0, 0, 0, 0, $ws, $hs, $w, $h);
        imagejpeg($ims, $path.DIRECTORY_SEPARATOR.$thumb);
      }
    }
  }
}
?>