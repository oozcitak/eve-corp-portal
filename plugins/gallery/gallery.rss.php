<?php
require_once('../../core/core.class.php');
$core = new Core();

$path = dirname(__FILE__).DIRECTORY_SEPARATOR."images";
if(!file_exists($path))
  mkdir($path);
  
$baseurl = "http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/images/";
$names = $core->GetAllUserNames();

$action = @$_GET["action"];
if(empty($action)) $action = "home";

if($action == "home")
  $result = $core->SQL("SELECT * FROM gallery WHERE 
".$core->CurrentUser()->AccessRight().">=AccessRight AND AccessRight!=-1 
AND AccessRight!=1 ORDER BY Date DESC");
else
  $result = $core->SQL("SELECT * FROM gallery WHERE Owner=".$core->CurrentUser()->ID." ORDER BY Date DESC");
$gallery = array();
while($row = mysql_fetch_assoc($result))
{
  $name = $row["Owner"]."_".$row["id"].".".$row["Extension"];
  // Create thumbnail
  $thumb = $row["Owner"]."_".$row["id"]."_thumb.jpg";
  if(file_exists($path.DIRECTORY_SEPARATOR.$name))
  {
    if(!file_exists($path.DIRECTORY_SEPARATOR.$thumb))
    {
      if($row["Extension"] == "gif")
        $im = @imagecreatefromgif($path.DIRECTORY_SEPARATOR.$name);
      elseif($row["Extension"] == "jpg")
        $im = @imagecreatefromjpeg($path.DIRECTORY_SEPARATOR.$name);
      elseif($row["Extension"] == "png")
        $im = @imagecreatefrompng($path.DIRECTORY_SEPARATOR.$name);
      elseif($row["Extension"] == "bmp")
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
    $gallery[] = array("ID" => $row["id"], "Owner" => $names[$row["Owner"]], "Name" => $name, "Title" => $core->SQLUnEscape($row["Title"]), "Date" => $core->GMTToLocal($row["Date"]), "URL" => $baseurl.$name, "ThumbURL" => $baseurl.$thumb);
  }
}

// Write the XML
header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
echo "\n<rss xmlns:media='http://search.yahoo.com/mrss/' xmlns:atom='http://www.w3.org/2005/Atom' version='2.0'>";
echo "\n\t<channel>";
echo "\n\t<title>Meridian Dynamics Image Gallery</title>";
echo "\n\t<link>http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/index.php</link>";
echo "\n\t<description>Private image gallery for Meridian Dynamics members.</description>";
echo "\n\t<atom:link href='http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/gallery.rss.php?action=".$action."' rel='self' type='application/rss+xml' />";
foreach($gallery as $item)
{
  echo "\n\t\t<item>";
  echo "\n\t\t\t<title>".htmlspecialchars(empty($item["Title"]) ? $item["Name"] : $item["Title"])."</title>";
  echo "\n\t\t\t<link>http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/index.php?show=".$item["ID"]."</link>";
  echo "\n\t\t\t<guid>http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/index.php?show=".$item["ID"]."</guid>";
  echo "\n\t\t\t<media:thumbnail url=\"".$item["ThumbURL"]."\" />";
  echo "\n\t\t\t<media:content url=\"".$item["URL"]."\" />";
  echo "\n\t\t</item>";
}
echo "\n\t</channel>";
echo "\n</rss>";

?>
