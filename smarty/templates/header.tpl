{php}
  global $perf_starttime;
  $perf_starttime = microtime(true);
{/php}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
  <title>{$core->GetSetting("CorporationName")}{$title}</title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <link rel='shortcut icon' href='http://{$smarty.server.SERVER_NAME}/favicon.ico' />
  {if !empty($extraheader)}
    {$extraheader}
  {/if}
  {if $IGB}
    <link rel='Stylesheet' type='text/css' href='http://{$smarty.server.SERVER_NAME}/css/igb.css' />
  {else}
    {if !empty($script)}
  	<script type="text/javascript" src="{$script}"></script>
    {/if}
    {if !empty($style)}
    <link rel='Stylesheet' type='text/css' href='{$style}' />
    {/if}
    <link rel='Stylesheet' type='text/css' href='http://{$smarty.server.SERVER_NAME}/css/default.css' />
    <script type="text/javascript" src='http://{$smarty.server.SERVER_NAME}/overlib/overlib.js' ></script>
    <script type="text/javascript">
    {literal}
      function ResizeLogo()
      {
        var nWidth = document.body.offsetWidth - 120 - 120;
        if (nWidth < 1024) 
        {
          nPerc = (nWidth / 1024);
          var logo = ObjFromID('logo');
          logo.width = (1024 * nPerc);
          logo.height = (165 * nPerc);
        }
      }
      
      function ObjFromID(id)
      {
      	if(document.getElementById) 
      		return document.getElementById(id);
      	else if(document.all) 
      		return document.all[id]; 
      	else if(document.layers) 
      		return document.layers[id];
      }
    {/literal}
    </script>    
  {/if}
</head>

<body{if !empty($onload) && !($IGB)} onload="ResizeLogo();{$onload}"{elseif !($IGB)} onload="ResizeLogo();"{/if} >

{if !$IGB }
<script type="text/javascript">
  window.onresize = "ResizeLogo();"
</script>    
<!-- OverLib -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
{/if}

{if $IGB }
<div class='page'>
{/if}

<div class='logo' id='logocontainer'>
{if $IGB }
Meridian Dynamics
<hr />
{else}
<img id='logo' src="http://{$smarty.server.SERVER_NAME}/css/logo.jpg" width="1024" height="165" alt="" />
{/if}
</div>

{include file='userbar.tpl'}

{if !($IGB) }
<div class='page'>

<table width="100%" border="0" cellpadding="0" cellspacing="0" >
<tr>
<td id="leftcell" width="200">
{include file='leftbar.tpl'}
</td>
<td id="centercell" style="padding: 0px 10px; {if $layout!=3}padding-right: 4px;{/if}">
{/if}
