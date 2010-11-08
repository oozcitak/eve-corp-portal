<?php

require_once('../../core/core.class.php');
$cms = new Core();

// Headers
header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";

// We don't like guests here
if($cms->CurrentUser()->IsGuest) 
{
  echo "<response>";
  echo "<error>Could not connect to the API server.</error>";
  echo "<training>0</training>";
  echo "<secondsleft></secondsleft>";
  echo "<skillname></skillname>";
  echo "<tolevel></tolevel>";
  echo "<endtime></endtime>";
  echo "<cacheduntil></cacheduntil>";
  echo "</response>";
  exit;
}

echo "<response>";

$result = GetSkillInTraining();
if($result[0] == -1)
{
  echo "<error>Could not connect to the API server.</error>";
  echo "<training>0</training>";
  echo "<secondsleft></secondsleft>";
  echo "<skillname></skillname>";
  echo "<tolevel></tolevel>";
  echo "<endtime></endtime>";
  echo "<cacheduntil></cacheduntil>";
}
elseif($result[0] == -2)
{
  echo "<error>Error ".$result[1].": ".$result[2]."</error>";
  echo "<training>0</training>";
  echo "<secondsleft></secondsleft>";
  echo "<skillname></skillname>";
  echo "<tolevel></tolevel>";
  echo "<endtime></endtime>";
  echo "<cacheduntil></cacheduntil>";
}
elseif($result[0] == 0)
{
  echo "<error></error>";
  echo "<training>0</training>";
  echo "<secondsleft></secondsleft>";
  echo "<skillname></skillname>";
  echo "<tolevel></tolevel>";
  echo "<endtime></endtime>";
  echo "<cacheduntil>".$result[5]."</cacheduntil>";
}
else
{
  echo "<error></error>";
  echo "<training>1</training>";
  echo "<secondsleft>".$result[1]."</secondsleft>";
  echo "<skillname>".$result[2]."</skillname>";
  echo "<tolevel>".$result[3]."</tolevel>";
  echo "<endtime>".$result[4]."</endtime>";
  echo "<cacheduntil>".$result[5]."</cacheduntil>";
}  

echo "</response>";

exit;

// *******************************************************
// Returns the skill in training
// *******************************************************  
function GetSkillInTraining()
{
  global $cms;
  if($cms->CurrentUser()->IsGuest) return "";
  $raw = $cms->APIQuery("http://api.eve-online.com/char/SkillInTraining.xml.aspx");
  if($raw == FALSE) return array(-1, 0, "", 0, "", "");
  
  $xml = new SimpleXMLElement($raw);
  
  if ((int)$xml->error['code'] > 0) return array(-2, $xml->error['code'], $xml->error, 0, "", "");
  
  $training = ($xml->result->skillInTraining == 1) ? true : false;
  $starttime = $xml->result->trainingStartTime;
  $endtime = $xml->result->trainingEndTime;
  $skillid = $xml->result->trainingTypeID;
  $tolevel = (int)$xml->result->trainingToLevel;
  $cacheduntil = $xml->cachedUntil;

  if($training && (strtotime($endtime) - strtotime($cms->GMTTime()) > 0))
  {
    $result = $cms->EveSQL("SELECT typeName FROM invTypes WHERE typeID=".$skillid);
    if(mysql_num_rows($result) == 0) 
      $skillname = "Unknown Skill (TypeID = ".$skillid.")";
    else
      $skillname = mysql_result($result, 0);
      
    $seconds = strtotime($endtime) - strtotime($cms->GMTTime());
    return array(1, $seconds, $skillname, $tolevel, $cms->GMTToLocal($endtime), $cms->GMTToLocal($cacheduntil));
  }
  else
    return array(0, 0, "", 0, "", $cms->GMTToLocal($cacheduntil));
}

?>
