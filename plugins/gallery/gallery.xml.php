<?php
//require_once('../../core/core.class.php');
//$core = new Core();
global $core;

// Read all user names
$names = $core->GetAllUserNames();

// Write the XML
//header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
echo "\n<result>";

// Read comments on current user's images
$result = $core->SQL("SELECT t1.id, t2.Date, t2.User FROM gallery AS t1 INNER JOIN gallery_comments AS t2 ON t1.id=t2.Image WHERE t1.Owner=".$core->CurrentUser()->ID." AND t2.User!=".$core->CurrentUser()->ID." ORDER BY t2.Date DESC");
while($row = mysql_fetch_assoc($result))
{
  echo "\n\t<item>";
  echo "\n\t\t<date>".$row["Date"]."</date>";
  echo "\n\t\t<user>".$row["User"]."</user>";
  echo "\n\t\t<title><![CDATA[".$names[$row["User"]]." has commented on an <a href='http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/index.php?show=".$row["id"]."'>image</a> uploaded by you.]]></title>";
  echo "\n\t</item>";
}

// Read new images
$result = $core->SQL("SELECT * FROM gallery WHERE 
".$core->CurrentUser()->AccessRight().">=AccessRight AND AccessRight!=-1 
AND AccessRight!=1 ORDER BY Date DESC LIMIT 3");
while($row = mysql_fetch_assoc($result))
{
  echo "\n\t<item>";
  echo "\n\t\t<date>".$row["Date"]."</date>";
  echo "\n\t\t<group>2</group>";
  echo "\n\t\t<title><![CDATA[".$names[$row["Owner"]]." uploaded a new <a href='http://".$_SERVER["SERVER_NAME"]."/plugins/gallery/index.php?show=".$row["id"]."'>image</a>.]]></title>";
  echo "\n\t</item>";
}

echo "\n</result>";
?>
