<div class='leftbar'>
<!-- Plug-Ins -->
<div class='navbar'>
  {if $user->Name == "Guest"}
  <a href='{$baseurl}php/feedback.php'>Problems Logging In?</a>
  {else}
  <a href='{$baseurl}php/articles.php'>Articles &amp; Guides</a>
  {/if}
  {foreach from=$plugins item=plugin}
  {if !($plugin->ShowAdmin)}
  <a href='{$plugin->URL}'>{$plugin->Title}{if $plugin->Release == 0} (Alpha){elseif $plugin->Release == 1} (Beta){/if}</a>
  {/if}
  {/foreach}
</div>
<br />

<!-- Admin Panel -->
{assign var='adminopts' value='0'}
{capture name=adminpanel assign=adminpanel}
{foreach from=$plugins item=plugin}
{if $plugin->ShowAdmin}
<a href='{$plugin->URL}'>{$plugin->Title}{if $plugin->Release == 0} (Alpha){elseif $plugin->Release == 1} (Beta){/if}</a>
{assign var='adminopts' value='1'}
{/if}
{/foreach}

{if !$user->IsGuest && $core->AccessCheck("1", array("1", "32", "64")) }
<a href='{$baseurl}php/plugins.php'>Plug-Ins</a>
<a href='{$baseurl}php/cron.php'>Cron Jobs</a>
{assign var='adminopts' value='1'}
{/if}

{if $core->AccessCheck("1", array("1", "32")) }
<a href='{$baseurl}php/admin.php'>Portal Administration</a>
{assign var='adminopts' value='1'}
{/if}

{if $user->HasPortalRole("32")}
<a href='{$baseurl}php/feedback.php'>Feedbacks</a>
{assign var='adminopts' value='1'}
{/if}

{/capture}

{if $adminopts == '1'}
<div class='navbar'>
{$adminpanel}
</div>

{/if}

</div>
