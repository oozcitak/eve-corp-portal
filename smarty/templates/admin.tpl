{include file='header.tpl' title=' | Portal Administration'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="admin.php?action=users">User Management</a>
<a class="header" href="admin.php?action=articles">Classified Articles</a>
<a class="header" href="admin.php?action=names">Settings</a>
<a class="header" href="admin.php?action=membercorps">Allowed/Blocked Corporations</a>
<a class="header" href="admin.php?action=shouts">Edit Shouts</a>
<a class="header" href="admin.php?action=log">Server Log</a>
</div>
<!-- End Section Navigation Buttons -->

<!-- Settings Sub Menu Section Navigation Buttons -->
{if $action == "names" || $action == "frontpage"}
<div class="adminheader">
    <a class="adminheader" href="admin.php?action=names">API Configuration</a>
    <a class="adminheader" href="admin.php?action=frontpage">Front Page</a>
</div>
{/if}
<!-- End Section Navigation Buttons -->
<br />


{if $action == "articles" }

  <p>While editing classified articles, keep in mind that the quick info page and the help page are visible to corporation members only. 
	Whereas, welcome message and the corporate policies are visible to everyone. Do not post sensitive information in the welcome	message 
	or the corporate policies pages.</p>
  <p>
  <a href="admin.php?edit=1">Welcome Message</a><span>&nbsp;&nbsp;(Public)</span><br />
  <a href="admin.php?edit=2">Corporate Policies</a><span>&nbsp;&nbsp;(Public)</span><br />
  <a href="admin.php?edit=3">Quick Info Page</a><span>&nbsp;&nbsp;(Members Only)</span><br />
  <a href="admin.php?edit=4">Help Page</a><span>&nbsp;&nbsp;(Members Only)</span><br />
  </p>
{elseif $action == "edit" }
  <form method="post" action="admin.php?action=editdone">
  <input type="hidden" name="id" value="{$id}" />
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  {$core->HTMLEditor("text", $text, "600")}
  <br />

  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "names" }
  <p>Enter the corporation and alliance name EXACTLY as displayed in-game.
  Alliance web site sets the URL of the "Alliance" button in the top navigation bar.
  Clearing the alliance web site field hides the "Alliance" button.</p>
  <form method="post" action="admin.php?action=namesdone">
  <table>
  <tr><td>Corporation Name: </td><td><input type="text" name="corpname" size="60" value="{$corpname}" /></td></tr>
  <tr><td>Alliance Name: </td><td><input type="text" name="alliancename" size="60" value="{$alliancename}" /></td></tr>
  <tr><td>Alliance Web Site: </td><td><input type="text" name="allianceurl" size="60" value="{$allianceurl}" /></td></tr>
  <tr><td>Killboard URL: </td><td><input type="text" name="killboardurl" size="60" value="{$killboardurl}" /></td></tr>
  </table>
  <p>Some parts of the portal need a Full Access Director/CEO API key to function. This does not need to be the API key of
  your actual character. You can create a Director Alt Character for that purpose and enter this character's credentials below.</p>
  <table>
  <tr><td>Director API Character ID: </td><td><input type="text" name="apicharid" size="60" value="{$apicharid}" /></td></tr>
  <tr><td>Director API User ID: </td><td><input type="text" name="apiuserid" size="60" value="{$apiuserid}" /></td></tr>
  <tr><td>Director API Key: </td><td><input type="text" name="apikey" size="60" value="{$apikey}" /><br />&nbsp;</td></tr>
  </table>
  <p>You have the option to input a Full Access Director/CEO API key of another corporation. This will grant the members of the
  secondary corp access to the website.  Security access will be based on the roles of the player in that corporation's infrastructure.
  No additional data will run on this Full Access Director/CEO API key. Enter this character's credentials below or enter zero to disable.</p>
  <table>
  <tr><td>Secondary Director API Character ID: </td><td><input type="text" name="secondaryapicharid" size="60" value="{$secondaryapicharid}" /></td></tr>
  <tr><td>Secondary Director API User ID: </td><td><input type="text" name="secondaryapiuserid" size="60" value="{$secondaryapiuserid}" /></td></tr>
  <tr><td>Secondary Director API Key: </td><td><input type="text" name="secondaryapikey" size="60" value="{$secondaryapikey}" /><br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $action == "frontpage" }
  <p>These settings control a variety of display options that affect how the front page of the portal will look.</p>
  <form method="post" action="admin.php?action=frontpagedone">
  <table>
  <tr><td>Number of New News Articals Displayed: </td><td><input type="text" name="NewsLimit" size="20" value="{$NewsLimit}" /></td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $action == "membercorps" }
  <p>Following corporations are alliance members. If the portal is set to use <a href="cron.php">Cron Jobs</a>, this list will be updated every day after down-time.
  In case a corporation leaves the alliance and you want to immediately block all its members; select the corporation from the list and click
  the "Save" button.</p>
  <p>Click the "Refresh Member Corporations List" if alliance member corporations were changed recently. This operation may take a long time.</p>
  <form method="post" action="admin.php?action=membercorpsdone">
  <table class="data">
  <tr><th>Blocked</th><th>Name/Ticker</th><th>CEO</th></tr>
  {foreach from=$membercorps item=corp}
  <tr class='{cycle values="altrow1,altrow2"}'>
  <td><input type="checkbox" name="item{$corp.ID}" {if $corp.IsBlocked}checked="checked"{/if} /></td>
  {capture name=popupText assign=ToolTip}
    <span style='color: #fff; font-size: 12pt; font-weight: bold;'>{$corp.Name}</span><br />
    {if $corp.IsExecutor}Executor Corporation{/if}
    <hr size='0' />
    <b>Ticker: </b>{$corp.Ticker}<br />
    <b>CEO: </b>{$corp.CEOName}<br />
    {if $corp.IsBlocked}
    <span style='font-weight: bold; color: #FFa566;'>BLOCKED</span>
    {else}
    <span style='font-weight: bold; color: #66B5FF;'>ALLOWED</span>
    {/if}
  {/capture}      
  <td><span {popup text=$ToolTip}>{if $corp.IsExecutor}<b>{/if}{$corp.Name} ({$corp.Ticker}){if $corp.IsExecutor}</b>{/if}</span></td>
  <td>{$corp.CEOName}</td>
  </tr>
  {/foreach}
  </table>
  <input type="submit" name="submit" value="Save List and Update Users" />&nbsp;<input type="submit" name="submit" value="Refresh Member Corporations List" />
  </form>  
{elseif $action == "users" }
  <p>Select an administrative task.</p>
  <p>
  <a href="admin.php?action=oneclick">One-Click User Maintenance</a><br />
  <a href="admin.php?action=editroles">Edit Roles</a><br />
  <a href="admin.php?action=ban">Ban/Unban User Accounts</a><br />
  <a href="admin.php?action=guests">Allies &amp; Guests</a><br />
  </p>
  <p>
  <a href="admin.php?action=setinactivity">Set Inactivity Period</a><span>&nbsp;&nbsp;({$core->GetSetting("InactivityPeriod")} days)</span><br />
  <a href="admin.php?action=activityreport">View Activity Report</a><br />
  </p>
{elseif $action == "setinactivity" }
  <form method="post" action="admin.php?action=setinactivitydone">
  <table>
  <tr><td>Inactivity Period: </td><td><input type="text" name="inactivityperiod" size="10" value="{$inactivityperiod}" />&nbsp;days<br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>  
{elseif $action == "activityreport" }
  <h3>Inactive Members</h3>
  {if $error == true}
      <p>Unable to connect to the EVE API Server. Please try again later.</p>  
  {else}
    {if $hasinactives != true}
        <p>All members have been active within the inactivity period ({$inactivityperiod} days).</p>  
    {else}
      <p>Members that have been inactive within the inactivity period ({$inactivityperiod} days) are listed below.</p>  
      <table cellspacing="0" class="data">
      <tr><th>Name</th><th>Last Portal Login</th><th>Last Game Login</th><th>Out Of Pod Status</th></tr>
      {foreach from=$registered item=member}
        {if ($member.IsOOP == false) && (($member.PortalInactivity > ($inactivityperiod * 24 * 60 * 60)) || ($member.GameInactivity > ($inactivityperiod * 24 * 60 * 60)))}
          <tr class='{cycle values="altrow1,altrow2"}'>
            <td>{$member.Name}</td>
            <td> {if $member.LastPortalLogin == "0000-00-00 00:00:00"}<span class="highlight">Never</span>{else}{$core->GMTToLocal($member.LastPortalLogin)} (<span{if $member.PortalInactivity > ($inactivityperiod * 24 * 60 * 60)} class="highlight"{/if}>{$core->SecondsToTime($member.PortalInactivity)} ago</span>){/if}</td>
            <td>{$core->GMTToLocal($member.LastGameLogin)} (<span{if $member.GameInactivity > ($inactivityperiod * 24 * 60 * 60)} class="highlight"{/if}>{$core->SecondsToTime($member.GameInactivity)} ago</span>)</td>
            <td>Not OOP</td>
          </tr>
        {/if}
      {/foreach}
      </table>
    {/if}    
    {if $hasoops != true}
      <h3>OOP Members</h3>
      <p>These members declared themselves as Out Of Pod.</p>
      <table cellspacing="0" class="data">
      <tr><th>Name</th><th>Last Portal Login</th><th>Last Game Login</th><th>Out Of Pod Status</th></tr>
      {foreach from=$registered item=member}
        {if $member.IsOOP == true}
          <tr class='{cycle values="altrow1,altrow2"}'>
            <td>{$member.Name}</td>
            <td> {if $member.LastPortalLogin == "0000-00-00 00:00:00"}<span class="highlight">Never</span>{else}{$core->GMTToLocal($member.LastPortalLogin)} (<span{if $member.PortalInactivity > ($inactivityperiod * 24 * 60 * 60)} class="highlight"{/if}>{$core->SecondsToTime($member.PortalInactivity)} ago</span>){/if}</td>
            <td>{$core->GMTToLocal($member.LastGameLogin)} (<span{if $member.GameInactivity > ($inactivityperiod * 24 * 60 * 60)} class="highlight"{/if}>{$core->SecondsToTime($member.GameInactivity)}</span>)</td>
            <td>{if $member.IsOOP}OOP Until {$member.OOPUntil}{if !empty($member.OOPNote)} ({$member.OOPNote}){/if}{else}Not OOP{/if}</td>
          </tr>
        {/if}
      {/foreach}
      </table>
    {/if}
    {if !empty($unregistered)}
      <h3>Unregistered Members</h3>
      <p>Following characters are listed as corporation members in-game, but they are not registered to the portal.</p>
      <table cellspacing="0" class="data">
      <tr><th>Name</th><th>Last Portal Login</th><th>Last Game Login</th><th>Out Of Pod Status</th></tr>
      {foreach from=$unregistered item=member}
        <tr class='{cycle values="altrow1,altrow2"}'>
          <td>{$member.Name}</td>
          <td><span class="highlight">Never</span></td>
          <td>{$core->GMTToLocal($member.LastGameLogin)} (<span{if $member.GameInactivity > ($inactivityperiod * 24 * 60 * 60)} class="highlight"{/if}>{$core->SecondsToTime($member.GameInactivity)}</span>)</td>
          <td>N/A</td>
        </tr>
      {/foreach}
      </table>
    {/if}
  {/if}
{elseif $action == "oneclick" }
  <p>This will synchronize the Portal database with the EVE API server. If your server is configured to automatically synchronize 
  with the EVE API Server using <a href="cron.php">Cron Jobs</a>, manual synchronization will not be required. By default, titles and roles
  are updated every day at 00:00 GMT.</p>
  <p>When you click the "Perform One-Click Maintenance" button, following tasks will be performed.</p>
  <p>
  <ul>
    <li>If the CEO title is passed on to someone else in-game, the new CEO will be promoted and the former CEO will be demoted.</li>
    <li>If a member is promoted to a Director position in-game, they will be granted privileges.</li>
    <li>If a member is demoted from a Director position in-game, their Director privileges will be removed.</li>
    <li>If a member's roles are changed in-game, their new roles will be transferred here.</li>
    <li>If a member's title is changed in-game, their new title will be transferred here.</li>
    <li>If a registered user has joined the corporation, he will be granted member access.</li>
    <li>If a member has left the corporation, he will be demoted to "Registered Guest" status.</li>
  </ul>
  </p>
  <p>
  <a class="header" href="admin.php?action=oneclickdo">Perform One-Click Maintenance</a>
  <a class="header" href="admin.php">Cancel</a>
  </p>
{elseif $action == "oneclickdo" || $action == "membercorpsdone" }
  {if $error == true}
      <p>Unable to connect to the EVE API Server. Please try again later.</p>  
  {elseif empty($syncres)}
      <p>Portal database is already in sync with the EVE API Server. No changes were made.</p>
  {else}
    <p>Following tasks were performed.</p>  
    <table>
    <tr><th>Name</th><th>Task</th></tr>
    {foreach from=$syncres item=result}
      <tr>
        <td>{$result.0}</td>
        <td>{$result.1}</td>
      </tr>
    {/foreach}
    </table>
  {/if}
{elseif $action == "editroles" }
  <p>You can edit only the Portal roles here. In-game roles are automatically updated using the EVE API. Roll over names to view EVE roles.</p>
  <form method="post" action="admin.php?action=editrolesdone">
  <table width="100%" cellspacing="0" class="data">
  {foreach name=members from=$members item=member}
    {if $smarty.foreach.members.index % 20 == 0}
      <tr>
        <th class="center">Name</th>
        <th class="center">Title</th>
        <th class="center">Can<br />Submit<br />News</th>
        <th class="center">Can<br />Submit<br />Events</th>
        <th class="center">Forum<br />Moderator</th>
        <th class="center">Manager</th>
        <th class="center">Developer</th>
        <th class="center">Administrator</th>
        <th class="center">Honorary Member</th>
        <th class="center">Alliance Leadership</th>
      </tr>
    {/if}
    <tr class='{cycle values="altrow1,altrow2"}'>
    <td>
    {if $member->IsCEO()}
    <span class="ceo">
    {elseif $member->IsDirector()}
    <span class="director">
    {elseif $member->IsManager()}
    <span class="manager">
    {elseif $member->AccessRight() >= 2}
    <span class="member">
    {else}
    <span class="guest">
    {/if}
    <span class="tooltip" title="{$member->StringFromEVERoles()}">
    {$member->Name}
    </span>
    </span>
    </td>
    <td>&nbsp;{$member->Title}</td>
    <td class="center"><input type="checkbox" name="news{$member->ID}" {if $member->HasPortalRole(4)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="calendar{$member->ID}" {if $member->HasPortalRole(8)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="forummod{$member->ID}" {if $member->HasPortalRole(16)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="manager{$member->ID}" {if $member->HasPortalRole(2)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="dev{$member->ID}" {if $member->HasPortalRole(64)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="admin{$member->ID}" {if $member->HasPortalRole(32)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="honorary{$member->ID}" {if $member->HasPortalRole(128)}checked="checked"{/if} /></td>
    <td class="center"><input type="checkbox" name="allyleader{$member->ID}" {if $member->HasPortalRole(256)}checked="checked"{/if} /></td>
    </tr>
  {/foreach}
  </table>
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
  
  <ul>
    <li>Can Submit News: Allows the user to post news.</li>
    <li>Can Submit Events: Allows the user to post calendar events.</li>
    <li>Forum Moderator: Allows the user to moderate the forums by editing and deleting posts, 
    creating and editing forum boards, moving topics between boards and renaming topics. 
    This role by itself, does not give the user access to Managers' or Directors' forum boards.</li>
    <li>Manager: Allows access to Managers' forum boards.</li>
    <li>Administrator: Allows access to the entire portal, including administrative pages and Managers' and 
    Directors' forum boards. The CEO and Directors have administrative rights even if this role is not set.</li>
    <li>Honorary Member: Has the same rights as a regular corporation member.</li>
    <li>Alliance Leadership: Special alliance access which is used for certain plugins.</li>
  </ul>
{elseif $action == "guests" }
  <h3>Allies</h3>
  <table>
  {foreach from=$allies item=member}
    <tr>
      <td><span class="guest">{$member->Name}</span></td>
    </tr>
  {/foreach}
  </table>
  
  <h3>Guests</h3>
  <table>
  {foreach name=guests from=$guests item=member}
    <tr>
      <td><span class="guest">{$member->Name}</span></td>
    </tr>
  {/foreach}
  </table>
{elseif $action == "ban" }
  <p>Banning a user account will deny him access to the portal. Note that this does not prevent him from registering a new guest account with an alt.</p>  
  <form method="post" action="admin.php?action=bandone">
  <table cellspacing="0" class="data">
  {foreach name=members from=$members item=member}
    {if $smarty.foreach.members.index % 20 == 0}
      <tr>
        <th class="center">Banned</th>
        <th class="center">Name</th>
        <th class="center">Role</th>
      </tr>
    {/if}
    {if !($member->IsCEO())}
      <tr class='{cycle values="altrow1,altrow2"}'>
      <td class="center"><input type="checkbox" id="ban{$member->ID}" name="ban{$member->ID}" {if !($member->IsActive)}checked="checked"{/if} /></td>
      <td>
      {if $member->IsDirector()}
      <span class="director">
      {elseif $member->IsManager()}
      <span class="manager">
      {elseif $member->AccessRight() >= 2}
      <span class="member">
      {else}
      <span class="guest">
      {/if}
      <label for="ban{$member->ID}">{$member->Name}</label>
      </span>
      </td>
      <td>
      {if $member->IsDirector()}
      Director
      {elseif $member->IsManager()}
      Manager
      {elseif $member->AccessRight() >= 2}
      Corporation Member
      {elseif $member->IsAlly}
      Ally
      {else}
      Guest
      {/if}
      </td>
      </tr>
    {/if}
  {/foreach}
  </table>
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
  
{elseif $action == "shouts" }
  {if empty($shouts)}
    <p>There are no shouts in the database.</p>
  {else}
    <table cellspacing="0" class="data">
    <tr><th>User</th><th>Text</th><th></th></tr>
    {foreach from=$shouts item=shout}
      <tr class='{cycle values="altrow1,altrow2"}'>
        <td>{$shout->AuthorName}</td>
        <td>{$shout->Text}</td>
        <td>&nbsp;<a href="admin.php?deleteshout={$shout->ID}">Delete</a></td>
      </tr>
    {/foreach}
    </table>
  {/if}
{elseif $action == "log" }
  {if empty($logs)}
    <p>Server log is empty.</p>
  {else}
    {if $pagecount > 1}
      <form method="get" action="admin.php?action=log">
      <input type="hidden" name="action" value="log" />
      Page: <select name="page">{section name=pages start=1 loop=$pagecount+1}<option value="{$smarty.section.pages.index}" {if $page == $smarty.section.pages.index}selected="selected"{/if}>{$smarty.section.pages.index}</option>{/section}</select>
      <input type="submit" name="submit" value="Go" />
      </form>
      <hr size="0" />
    {/if}
    <table cellspacing="0" cellpadding="0" class="data">
    <tr><th>User</th><th>Date</th><th>Log Entry</th></tr>
    {foreach from=$logs item=log}
    <tr class='{cycle values="altrow1,altrow2"}'>
    <td><b>{$log->UserName|replace:' ':'&nbsp;'}</b>&nbsp;</td><td><i>{$core->GMTToLocal($log->Date)|replace:' ':'&nbsp;'}</i>&nbsp;</td><td>{$log->Text}</td>
    </tr>
    {/foreach}
    </table>
    {if $pagecount > 1}
      <hr size="0" />
      <form method="get" action="admin.php">
      <input type="hidden" name="action" value="log" />
      Page: <select name="page">{section name=pages start=1 loop=$pagecount+1}<option value="{$smarty.section.pages.index}" {if $page == $smarty.section.pages.index}selected="selected"{/if}>{$smarty.section.pages.index}</option>{/section}</select>
      <input type="submit" name="submit" value="Go" />
      </form>
    {/if}
  {/if}
{/if}

{if $result == 1}
  <div class="error"><p>Title and text cannot be empty.</p></div>
{elseif $result == 2}
  <div class="error"><p>Corporation name cannot be empty.</p></div>
{elseif $result == 3}
  <div class="info"><p>Corporation and alliance names are saved.</p></div>
{elseif $result == 4}
  <div class="error"><p>Please enter a number for the inactivity period.</p></div>
{elseif $result == 5}
  <div class="error"><p>You cannot have any values empty.</p></div>
{/if}

{include file='footer.tpl'}
