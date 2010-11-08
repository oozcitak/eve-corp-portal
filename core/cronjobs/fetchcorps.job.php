<?php

//////////////////////////////////////////////////////////////
// DAILY CRON JOB: Fetch Corporations and Alliances
// Fetch corporayion and alliance info via EVE Online API 
// and update the portal database
//////////////////////////////////////////////////////////////
if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php");
elseif(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php");
elseif(file_exists("core.class.php"))
  require_once("core.class.php");
else
{
  exit;
}

set_time_limit(0);
$core = new Core();
$result = $core->UpdateAllianceMembers();

if($result)
  $core->Log("CRON(Fetch Corporations and Alliances) Done.");
else
  $core->Log("CRON(Fetch Corporations and Alliances) Error.");

?>
