<?php
// Create the core object
require_once('../../core/core.class.php');
$core = new Core();

// Parses user agent string
require_once('browser.php');

//Access control
// $core->CurrentUser() returns the currently logged in user as a "User" object
// AccessRight() is a method of the User object that returns: 0 for guests, 1 for registered guests, 2 for corp members, 3 for managers and 4 for directors
// Here we check to see if the current user is a registered guest (A registered guest is a registered user but not a corp member)
// If we fail the check, the user will be redirected to an error page. 
// Goto($url) is a method of the core object which is a shorthand for { header("Location: $url); exit; }
if($core->CurrentUser()->AccessRight() < 1) $core->Goto('../../php/access.php');

// User name
$username = $core->CurrentUser()->Name;

// Browser string
$browser = @$_SERVER["HTTP_USER_AGENT"];
if(empty($browser)) $browser = "Unknown";

// Save username and browser string in our database
// SQL($query) is a method of the Core object that runs a SQL query on the plugin database
// SQLEscape($string) is also a Core method that is a shorthand for mysql_real_escape_string($string)
$core->SQL("INSERT INTO `browserstats` (`User`, `Browser`) VALUES ('".$core->SQLEscape($username)."', '".$core->SQLEscape($browser)."') ON DUPLICATE KEY UPDATE `Browser`='".$core->SQLEscape($browser)."'");

// Read the database
$result = $core->SQL("SELECT * FROM `browserstats`");
$browserstats = array();
while($row = mysql_fetch_assoc($result))
{
  $br = new Browser($core->SQLUnEscape($row["Browser"]));
  $browsername = "$br->Platform, $br->Name $br->Version";
  if(isset($browserstats[$browsername]))
    $browserstats[$browsername] += 1;
  else
    $browserstats[$browsername] = 1;
}

// We have the browser stats in an array. Now assign it to a Smarty template variable
// So that it will be availabe in the Smarty template.
// You can assign any type of variable: numbers, strings, arrays, objects... all work
// The format is $core->assign("template_variable_name", "variable_value")
$core->assign("browserstats", $browserstats);

// We are done. Last step is to display our Smarty template
// PlugInPath is a property of the Core object that returns the absolute path to the main plugin directory.
// Relative paths do not work here.
$core->display($core->PlugInPath."browserstats/browserstats.tpl");
?>
