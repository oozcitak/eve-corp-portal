{include file='header.tpl' title=' | Plug-In Management'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="plugins.php?action=plugins">Plug-Ins</a>
<a class="header" href="plugins.php?action=newplugin">Create New Plug-In</a>
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "plugins" }
  {if empty($plugins)}
    <p>There are no plug-ins. Click the "Create New Plug-In" button above to create one.</p>
  {/if}
  {foreach name=plugins from=$plugins key=key item=plugin}
      <h3>{$plugin->Title}</h3>
      <table>
      <tr><td>Name: </td><td><b>{$plugin->Name}</b></td></tr>
      <tr><td>Title: </td><td>{$plugin->Title}</td></tr>
      <tr><td>Developer: </td><td>{$plugin->DeveloperName}<br />&nbsp;</td></tr>
      <tr><td>Release: </td><td>{if $plugin->Release == 0}Alpha (Development Release){elseif $plugin->Release == 1}Beta (Public Test Release){elseif $plugin->Release == 2}Gold (Production Release){/if}</td></tr>
      <tr><td>Access Control: </td><td>{if $plugin->ReadAccess == 0}Guests{elseif $plugin->ReadAccess == 1}Alliance Members{elseif $plugin->ReadAccess == 2}Corporation Members{elseif $plugin->ReadAccess == 3}Managers{elseif $plugin->ReadAccess == 4}Directors{/if}<br />&nbsp;</td></tr>
      <tr><td>Resolved URL: </td><td><a href="{$plugin->URL}">{$plugin->URL}</a></td></tr>
      <tr><td>Entry Page Check: </td><td>{if $plugin->FileExists}OK{else}FAILED. Plug-in entry page should be named "index.php" and it should be located in /plugins/{$plugin->Name}/index.php{/if}<br />&nbsp;</td></tr>
      <tr><td>Show in IGB: </td><td>{if $plugin->ShowIGB}Yes{else}No{/if}</td></tr>
      <tr><td>Show in Admin Panel: </td><td>{if $plugin->ShowAdmin}Yes{else}No{/if}</td></tr>
      </table>
        <p>
      {if $plugin->Developer == $user->ID }
        <a class="header" href="plugins.php?edit={$plugin->ID}">Edit</a>
        <a class="header" href="plugins.php?delete={$plugin->ID}">Delete</a>
      {elseif $user->AccessRight() >= 4 }
        <a class="adminheader" href="plugins.php?edit={$plugin->ID}">Edit</a>
        <a class="adminheader" href="plugins.php?delete={$plugin->ID}">Delete</a>
      {/if}
      {if $user->AccessRight() >= 4 }
        <a class="adminheader" href="plugins.php?developer={$plugin->ID}">Assign Developer</a>
      {/if}
        </p>
      {if $smarty.foreach.plugins.last==FALSE }
        <hr size="0" />
      {/if}
  {/foreach}
{elseif $action == "newplugin" }
  <p>Plug-In name will also be used for the folder containing plug-in files. Please choose a proper directory name.</p>
  <form method="post" action="plugins.php?action=newdone">
  <table>
  <tr><td>Name: </td><td><input type="text" size="40" name="name" value="{$name}" /></td></tr>
  <tr><td>Title: </td><td><input type="text" size="40" name="title" value="{$title}" /><br />&nbsp;</td></tr>
  <tr><td rowspan="5">Access Control: </td><td><input type="radio" name="accesscontrol" id="accesscontrol0" value="0" {if $accesscontrol == 0}checked="checked"{/if} /><label for="accesscontrol0">Guests</label></td>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol1" value="1" {if $accesscontrol == 1}checked="checked"{/if} /><label for="accesscontrol1">Alliance Members</label></td></tr>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol2" value="2" {if $accesscontrol == 2}checked="checked"{/if} /><label for="accesscontrol2">Corporation Members</label></td></tr>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol3" value="3" {if $accesscontrol == 3}checked="checked"{/if} /><label for="accesscontrol3">Managers</label></td></tr>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol4" value="4" {if $accesscontrol == 4}checked="checked"{/if} /><label for="accesscontrol4">Directors</label><br />&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><input type="checkbox" name="showigb" id="showigb" {if $showigb == "on"}checked="checked"{/if} /><label for="showigb">Show in IGB</label></td></tr>
  <tr><td>&nbsp;</td><td><input type="checkbox" name="showadmin" id="showadmin" {if $showadmin == "on"}checked="checked"{/if} /><label for="showadmin">Show in Admin Panel</label><br />&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><input type="checkbox" name="createfiles" id="createfiles" {if $createfiles == "on"}checked="checked"{/if} /><label for="createfiles">Create plug-in folder and sample files</label><br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $action == "edit" }
  <form method="post" action="plugins.php?action=editdone">
  <input type="hidden" name="id" value="{$id}" />
  <table>
  <tr><td>Title: </td><td><input type="text" size="40" name="title" value="{$title}" /><br />&nbsp;</td></tr>
  <tr><td rowspan="3">Release Control: </td><td><input type="radio" name="releasecontrol" id="releasecontrol0" value="0" {if $releasecontrol == 0}checked="checked"{/if} /><label for="releasecontrol0">Alpha (Development Release)</label></td>
  <tr><td><input type="radio" name="releasecontrol" id="releasecontrol1" value="1" {if $releasecontrol == 1}checked="checked"{/if} /><label for="releasecontrol1">Beta (Public Test Release)</label></td></tr>
  <tr><td><input type="radio" name="releasecontrol" id="releasecontrol2" value="2" {if $releasecontrol == 2}checked="checked"{/if} /><label for="releasecontrol2">Gold (Production Release)</label><br />&nbsp;</td></tr>
  <tr><td rowspan="5">Access Control: </td><td><input type="radio" name="accesscontrol" id="accesscontrol0" value="0" {if $accesscontrol == 0}checked="checked"{/if} /><label for="accesscontrol0">Guests</label></td>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol1" value="1" {if $accesscontrol == 1}checked="checked"{/if} /><label for="accesscontrol1">Alliance Members</label></td></tr>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol2" value="2" {if $accesscontrol == 2}checked="checked"{/if} /><label for="accesscontrol2">Corporation Members</label></td></tr>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol3" value="3" {if $accesscontrol == 3}checked="checked"{/if} /><label for="accesscontrol3">Managers</label></td></tr>
  <tr><td><input type="radio" name="accesscontrol" id="accesscontrol4" value="4" {if $accesscontrol == 4}checked="checked"{/if} /><label for="accesscontrol4">Directors</label><br />&nbsp;</td></tr>
  <tr><td>&nbsp;</td><td><input type="checkbox" name="showigb" id="showigb" {if $showigb == "on"}checked="checked"{/if} /><label for="showigb">Show in IGB</label></td></tr>
  <tr><td>&nbsp;</td><td><input type="checkbox" name="showadmin" id="showadmin" {if $showadmin == "on"}checked="checked"{/if} /><label for="showadmin">Show in Admin Panel</label><br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $action == "delete" }
  <form method="post" action="plugins.php?action=deletedone">
  <input type="hidden" name="id" value="{$id}" />
  <p>Are you sure you want to delete the plug-in: '{$name}'?</p>
  <p>
  <input type="checkbox" name="deletefolder" id="deletefolder" /><label for="deletefolder">Delete the plug-in directory and its contents.</label>
  </p>
  <input type="submit" name="submit" value="Delete" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "developer" }
  <form method="post" action="plugins.php?action=developerdone">
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
  <div class="error"><p>Plug-in name and title cannot be empty.</p></div>
{elseif $result == 2}
  <div class="error"><p>A plug-in with that name already exists. Please enter a different name.</p></div>
{/if}

{include file='footer.tpl'}
