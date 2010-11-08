{include file='header.tpl' title=' | Cron Job Management'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="cron.php?action=cronjobs">Cron Jobs</a>
<a class="header" href="cron.php?action=newjob">Create New Cron Job</a>
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "cronjobs" }
  {if empty($jobs)}
    <p>There are no cron jobs. Click the "Create New Cron Job" button above to create one.</p>
  {/if}
  {foreach name=jobs from=$jobs key=key item=job}
    {if $job->Developer == $user->ID || $user->AccessRight() >= 4}
      <h3>{$job->Title}</h3>
      <table>
      <tr><td>Title: </td><td>{$job->Title}</td></tr>
      <tr><td>Developer: </td><td>{$job->DeveloperName}<br />&nbsp;</td></tr>
      <tr><td>Schedule: </td><td>{$job->ScheduleName()}</td></tr>
      <tr><td>PHP Script File: </td><td>{$job->Source}</td></tr>
      <tr><td>File Check: </td><td>{if $job->FileExists}OK{else}FAILED. Could not find cron script file.{/if}<br />&nbsp;</td></tr>
      <tr><td>Last Run: </td><td>{if $job->LastRun == "0000-00-00 00:00:00"}Never{else}{$core->GMTToLocal($job->LastRun)}{/if}</td></tr>
      <tr><td>Last Output: </td><td>{$job->LastError}</td></tr>
      </table>
        <p>
      {if $job->Developer == $user->ID }
        <a class="header" href="cron.php?edit={$job->ID}">Edit</a>
        <a class="header" href="cron.php?delete={$job->ID}">Delete</a>
        <a class="header" href="cron.php?run={$job->ID}">Run Now</a>
      {elseif $user->AccessRight() >= 4 }
        <a class="adminheader" href="cron.php?edit={$job->ID}">Edit</a>
        <a class="adminheader" href="cron.php?delete={$job->ID}">Delete</a>
        <a class="adminheader" href="cron.php?run={$job->ID}">Run Now</a>
      {/if}
      {if $user->AccessRight() >= 4 }
        <a class="adminheader" href="cron.php?developer={$job->ID}">Assign Developer</a>
      {/if}
        </p>
      {if $smarty.foreach.jobs.last==FALSE }
        <hr size="0" />
      {/if}
    {/if}
  {/foreach}
{elseif $action == "newjob" }
  <p>All cron jobs are executed in a single batch file. If a cron script fails, remaining cron jobs in the batch will also fail. 
  Please double check to make sure that you have set up proper error handling routines in your cron script.</p>
  <form method="post" action="cron.php?action=newdone">
  <table>
  <tr><td>Title: </td><td><input type="text" size="40" name="title" value="{$title}" /></td></tr>
  <tr><td>Schedule: </td><td>
  <select name="type">
  {foreach from=$crontypes key=key item=item}
  <option value="{$key}" {if $key==$type}selected="selected"{/if}>{$item}</option>
  {/foreach}
  </select>
  </td></tr>
  <tr><td>PHP Script File Name: </td><td><input type="text" size="60" name="source" value="{$source}" /> (Relative to server root)<br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $action == "edit" }
  <form method="post" action="cron.php?action=editdone">
  <input type="hidden" name="id" value="{$id}" />
  <table>
  <tr><td>Title: </td><td><input type="text" size="40" name="title" value="{$title}" /></td></tr>
  <tr><td>Schedule: </td><td>
  <select name="type">
  {foreach from=$crontypes key=key item=item}
  <option value="{$key}" {if $key==$type}selected="selected"{/if}>{$item}</option>
  {/foreach}
  </select>
  </td></tr>
  <tr><td>PHP Script File Name: </td><td><input type="text" size="60" name="source" value="{$source}" /> (Relative to server root)<br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $action == "delete" }
  <form method="post" action="cron.php?action=deletedone">
  <input type="hidden" name="id" value="{$id}" />
  <p>Are you sure you want to delete the cron job: '{$name}'?</p>
  <input type="submit" name="submit" value="Delete" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "developer" }
  <form method="post" action="cron.php?action=developerdone">
  <input type="hidden" name="id" value="{$id}" />
  <p>
  Developer: <select name="developer">
  {foreach from=$users item=dev}
  {if $dev->HasPortalRole(64)}
  <option value="{$dev->ID}" {if $developer==$dev->ID}selected="selected"{/if}>{$dev->Name}</option>
  {/if}
  {/foreach}
  </select>
  </p>
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{/if}

{if $result == 1}
  <div class="error"><p>Cron job title and source file name cannot be empty.</p></div>
{/if}

{include file='footer.tpl'}
