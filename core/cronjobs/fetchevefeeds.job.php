<?php

//////////////////////////////////////////////////////////////
// HOURLY CRON JOB: Fetch and cache XML feeds from eve-online
//////////////////////////////////////////////////////////////
if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php"))
	$cachedir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."feedcache";
elseif(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."core.class.php"))
	$cachedir = dirname(__FILE__).DIRECTORY_SEPARATOR."feedcache";
else
{
  exit;
}

if(!file_exists($cachedir))
  mkdir($cachedir);

// EVE News
$url = "http://myeve.eve-online.com/feed/rdfnews.asp?tid=1";
$cachefile = $cachedir.DIRECTORY_SEPARATOR."evenews.xml";
$raw = @file_get_contents($url);
if(($raw !== FALSE) && (substr($raw, 0, 5) == "<?xml")) file_put_contents($cachefile, $raw);

// RP News
$url = "http://myeve.eve-online.com/feed/rdfnews.asp?tid=4";
$cachefile = $cachedir.DIRECTORY_SEPARATOR."rpnews.xml";
$raw = @file_get_contents($url);
if(($raw !== FALSE) && (substr($raw, 0, 5) == "<?xml")) file_put_contents($cachefile, $raw);

// Dev Blogs
$url = "http://myeve.eve-online.com/feed/rdfdevblog.asp";
$cachefile = $cachedir.DIRECTORY_SEPARATOR."devblogs.xml";
$raw = @file_get_contents($url);
if(($raw !== FALSE) && (substr($raw, 0, 5) == "<?xml")) file_put_contents($cachefile, $raw);

?>
