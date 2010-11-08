<?php
require_once("../../core/eveserver.class.php");

// Headers
header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";

$result = new EVEServer();
echo "<response>";
echo "<status>".$result->Status."</status>";
echo "<time>".gmdate("d.m.Y H:i:s")."</time>";
echo "<data>".($result->Pilots != 0 ? $result->Pilots : $result->Countdown)."</data>";
echo "<motd><![CDATA[".$result->MOTD."]]></motd>";
echo "</response>";

?>
