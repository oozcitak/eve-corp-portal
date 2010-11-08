<?php

require_once('../../core/core.class.php');
$cms = new Core();

// Headers
header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
echo "<result>";

$items = array();
if(@$_GET["feed"] == "evenews")
  $items = $cms->ReadGameNews();
elseif(@$_GET["feed"] == "devblogs")
  $items = $cms->ReadDevBlogs();
else
  $items = $cms->ReadRPNews();

$i = 0;
foreach($items as $item)
{
  echo "<item>";
  echo "<title><![CDATA[".$item->Title."]]></title>";
  echo "<link><![CDATA[".$item->Link."]]></link>";
  echo "<summary><![CDATA[".$item->Summary."]]></summary>";
  echo "</item>";
  $i = $i + 1;
  if($i == 5) 
    break;
}

echo "</result>";

?>
