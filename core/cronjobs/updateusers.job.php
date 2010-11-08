<?php

//////////////////////////////////////////////////////////////
// DAILY CRON JOB: Update Titles and Roles
// Fetch user info via EVE Online API and update the portal database
//////////////////////////////////////////////////////////////
if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php");
elseif(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php"))
  require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php");
elseif(file_exists("core.class.php"))
  require_once("core.class.php");
else
{
  $core->Log("CRON(Member info synchronization) Error: Could not find core.class.php.");
  exit;
}

$core = new Core();

$result = $core->UpdateAllUsers();

if($result === false)
  $core->Log("CRON(Member info synchronization) Error: Could not connect to the EVE API server.");
elseif(empty($result))
  $core->Log("CRON(Member info synchronization) Done. No changes were required.");
else
  $core->Log("CRON(Member info synchronization) Done. ".count($result)." members were updated.");


?>
