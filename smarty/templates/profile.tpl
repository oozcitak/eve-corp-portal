{include file='header.tpl' title=' | User Profile' script='profile.js'}

{if $smarty.get.action != "user"}
  <!-- Section Navigation Buttons -->
  <div class="header">
  <a class="header" href="profile.php">User Profile</a>
  <a class="header" href="profile.php?action=edit">Edit User Information</a>
  {if $user->Alts}
  <a class="header" href="profile.php?action=alts">Manage Alts</a>
  {/if}
  {if !$user->IsGuest}
  <a class="header" href="profile.php?action=registeralt">Register Alt</a>
  {/if}
  <a class="header" href="profile.php?action=signature">Signature</a>
  <a class="header" href="profile.php?action=password">Change Password</a>
  </div>
  <br />
  <!-- End Section Navigation Buttons -->
{/if}

{if $smarty.get.action == "edit"}
  <form method="post" action="profile.php?action=editdone">
  <table>
  <!-- User Info -->
  <tr><td>Time Zone: </td><td>
    <select name='timezone' id='tzselect'>
    {section name=tz loop=26}
      {assign var=zone value=`$smarty.section.tz.index-12`}
      <option value="{$zone}" {if $user->TimeZone == $zone} selected="selected"{/if}>
      {if $zone == 0}
      GMT
      {elseif $zone < 0}
      GMT {$zone}
      {else}
      GMT +{$zone}
      {/if}
      </option>
    {/section}
    </select>
    <a href="#" onclick="javascript:SelectByTimeZone('tzselect');return false;">Auto Detect</a>
  </td></tr>
  <tr><td rowspan="2">EMail: </td><td><input type="text" name="email" value="{$user->Email}" /></td></tr>
  <tr><td><input type="checkbox" name="forwardmail" id="forwardmail" {if $user->PortalSettings & 128} checked="checked" {/if}/><label for="forwardmail">Forward portal messages to my e-mail</label></td></tr>
  <tr><td>IM: </td><td><input type="text" name="im" value="{$user->IM}" /></td></tr>
  <tr><td>Date Of Birth: </td><td>
  {if $user->BirthDate == "0000-00-00 00:00:00"}
    {html_select_date prefix=dob_ start_year=1945 end_year=+0 day_value_format=%02d day_empty=Day month_empty=Month year_empty=Year}
  {else}
    {html_select_date prefix=dob_ time=$user->BirthDate start_year=1945 end_year=+0 day_value_format=%02d }
  {/if}
  </td></tr>
  <tr><td>Location: </td><td><input type="text" name="location" value="{$user->Location}" /><br />&nbsp;</td></tr>
  
  <!-- API info -->
  <tr><td>API User ID: </td><td><input type="text" name="apiuserid" /></td></tr>
  <tr><td>API Key: </td><td><input type="text" name="apikey" size="60" /><br />&nbsp;</td></tr>
  
  <!-- Portal Settings -->
  <tr><td rowspan="5">Show On Home Page: </td><td><input type="checkbox" name="showgamenews" id="showgamenews" {if $user->PortalSettings & 1} checked="checked" {/if}/><label for="showgamenews">EVE News</label></td></tr>
  <tr><td><input type="checkbox" name="showdevblogs" id="showdevblogs" {if $user->PortalSettings & 2} checked="checked" {/if}/><label for="showdevblogs">Dev Blogs</label></td></tr>
  <tr><td><input type="checkbox" name="showrpnews" id="showrpnews" {if $user->PortalSettings & 4} checked="checked" {/if}/><label for="showrpnews">Role-Playing News</label></td></tr>
  <tr><td><input type="checkbox" name="showtqstatus" id="showtqstatus" {if $user->PortalSettings & 8} checked="checked" {/if}/><label for="showtqstatus">Tranquility Status</label></td></tr>
  <tr><td><input type="checkbox" name="showcurrentskill" id="showcurrentskill" {if $user->PortalSettings & 256} checked="checked" {/if}/><label for="showcurrentskill">Show Currently Training Skill</label><br />&nbsp;</td></tr>
  <!-- Date format -->
  <tr><td>Date Format: </td><td><select name='dateformat'>
  {foreach name=dateloop from=$dateformats key=dateformat item=datevalue}
    <option value="{$dateformat}" {if $user->DateFormat == $dateformat} selected="selected"{/if} >{$datevalue}</option>
  {/foreach}
  </select><br />&nbsp;</td></tr>
  <!-- Forum type -->
  <tr><td rowspan="2">Forum Display: </td><td>
  <input type="radio" name="forumdisplay" id="forumdisplay1" value="off" {if ($user->PortalSettings & 16) == 0} checked="checked" {/if}/><label for="forumdisplay1">Show Portaits and Signatures</label></td></tr>
  <tr><td><input type="radio" name="forumdisplay" id="forumdisplay2" value="on" {if $user->PortalSettings & 16} checked="checked" {/if}/><label for="forumdisplay2">Show Reply Texts Only</label><br />&nbsp;</td></tr>
  <!-- Privacy settings -->
  <tr><td rowspan="3">Show My RL Information To: <br />(EMail, IM, DOB and Location)</td><td>
  <input type="radio" name="contactinfo" id="contactinfo1" value="0" {if ($user->PortalSettings & 32) == 0 && ($user->PortalSettings & 64) == 0} checked="checked" {/if}/><label for="contactinfo1">No One</label></td></tr>
  <tr><td><input type="radio" name="contactinfo" id="contactinfo2" value="1" {if $user->PortalSettings & 32} checked="checked" {/if}/><label for="contactinfo2">Directors Only</label></td></tr>
  <tr><td><input type="radio" name="contactinfo" id="contactinfo3" value="2" {if $user->PortalSettings & 64} checked="checked" {/if}/><label for="contactinfo3">All Corporation Members</label><br />&nbsp;</td></tr>
  <!-- RL Status -->
  <tr><td>RL Status: </td><td><input type="checkbox" name="rlstatus" id="rlstatus" value="on" {if $user->IsOOP} checked="checked" {/if}/><label for="rlstatus">Out Of Pod</label></td></tr>
  <tr><td>OOP Until: </td><td>
  {if !$user->IsOOP}
    {html_select_date prefix=oop_ start_year=+0 end_year=+1 day_value_format=%02d day_empty=Day month_empty=Month year_empty=Year}
  {else}
    {html_select_date prefix=oop_ time=$user->OOPUntil start_year=+0 end_year=+1 day_value_format=%02d}
  {/if}
  </td></tr>
  <tr><td>OOP Note: </td><td><input type="text" name="oopnote" size="60" value="{$user->OOPNote}" /><br />&nbsp;</td></tr>

  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  
  </table>
  </form>  
{elseif $smarty.get.action == "signature"}
  <form method="post" action="profile.php?action=signaturedone">
  {$core->HTMLEditor("signature", $user->Signature)}
  <br />  
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $smarty.get.action == "password"}
  <form method="post" action="profile.php?action=passworddone">
  <table>
  
  <tr><td>New Password: </td><td><input type="password" name="password1" /></td></tr>
  <tr><td>Confirm New Password: </td><td><input type="password" name="password2" /><br />&nbsp;</td></tr>
  
  <tr><td colspan="2"><input type="submit" name="submit" value="Change Password" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  
  </table>
  </form>
  
  {if $result == 3}
  <div class="error"><p>The passwords you typed do not match. Please retype the new password in BOTH boxes.</p></div>
  {elseif $result == 4}
  <div class="error"><p>Passwords cannot be blank. Please retype your new password.</p></div>
  {/if}
{elseif $smarty.get.action == "alts"}
  <form method="post" action="profile.php?action=removealts">
  <table>
  {foreach name=alts from=$user->Alts key=key item=alt}
    <tr><td><input type="checkbox" id="alt{$key}" name="alt{$key}" /><label for="alt{$key}">{$alt}</label>{if $smarty.foreach.alts.last}<br />&nbsp;{/if}</td></tr>
  {/foreach}
  </table>
  <tr><td><input type="submit" name="submit" value="Remove Selected Alts" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </form>
{elseif $smarty.get.action == "registeralt"}
  <form method="post" action="profile.php?action=registeralt2">
  <table>
  <tr><td>API User ID: </td><td><input type="text" name="apiuserid" /></td></tr>
  <tr><td>API Key: </td><td><input type="text" name="apikey" size="60" /><br />&nbsp;</td></tr>
  <tr><td><input type="submit" name="submit" value="Fetch Characters" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
  {if $result == 20}
  <div class="error"><p>Please enter your API User ID and API Key.</p></div>
  {elseif $result == 21}
  <div class="error"><p>Could not connect to the API server. Please try again later.</p></div>
  {/if}
{elseif $smarty.get.action == "registeralt2"}
  <form method="post" action="profile.php?action=registeralt3">
  <table>
  {foreach name=chars from=$characters item=char}
  <tr>
    <td style="vertical-align: middle"><input type="hidden" name="name_{$char.CharacterID}" value="{$char.Name}" /><input type="hidden" name="corp_{$char.CharacterID}" value="{$char.CorporationName}" /><input type="radio" name="char" id="char_{$char.CharacterID}" value="{$char.CharacterID}" {if $smarty.foreach.chars.first} checked="checked" {/if}/></td>
    <td style="vertical-align: middle"><label for="char_{$char.CharacterID}"><img class="portrait" src="{$core->PortraitFromCharID($char.CharacterID, 64)}" width="64" height="64" /></label></td>
    <td style="vertical-align: middle"><label for="char_{$char.CharacterID}">{$char.Name} of {$char.CorporationName}</label></td>
  </tr>
  {/foreach}
  <tr><td colspan="3"><br />&nbsp;</td></tr>
  <tr><td colspan="3"><input type="submit" name="submit" value="Register" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>
  </table>
  </form>
{elseif $smarty.get.action == "user"}
  <img class="portrait" src="{$core->PortraitFromCharID($showuser->CharID, 256)}" width="128" height="128" />
  <table>
  <tr><th>Name:</th><td>{$showuser->Name}</td></tr>
  <tr><th>Corporation:</th><td>{$showuser->CorporationName} ({$showuser->CorporationTicker})</td></tr>
  {if !empty($showuser->Title)}<tr><th>Title:</th><td>{$showuser->Title}</td></tr>{/if}
  {if !empty($showuser->Alts) && !$showuser->IsGuest}
    {foreach name=alts from = $showuser->Alts item=alt}
      {if $smarty.foreach.alts.first}
        <tr><th rowspan="{$smarty.foreach.alts.total}">Alts:</th><td>{$alt}</td></tr>
      {else}
        <tr><td>{$alt}</td></tr>
      {/if}
    {/foreach}
  {/if}
  <tr><th>&nbsp;</th><td>&nbsp;</td></tr>
  <tr><th>Time Zone:</th><td>GMT&nbsp;{if $showuser->TimeZone < 0}{$showuser->TimeZone}{elseif $showuser->TimeZone > 0}+{$showuser->TimeZone}{/if}</td></tr>
  <tr><th>Local Time:</th><td>{$core->GMTToLocalTZ($core->GMTTime(), $showuser->TimeZone)}</td></tr>
  {if (($showuser->PortalSettings & 32) && ($user->AccessRight() >= 4)) || ($showuser->PortalSettings & 64)}
    <tr><th>&nbsp;</th><td>&nbsp;</td></tr>
    <tr><th>Email:</th><td>{$showuser->Email}</td></tr>
    <tr><th>IM:</th><td>{$showuser->IM}</td></tr>
    <tr><th>Date of Birth:</th><td>{$showuser->BirthDate|date_format:"%B %e, %Y"}</td></tr>
    <tr><th>Location:</th><td>{$showuser->Location}</td></tr>
  {/if}
  {if $showuser->IsOOP}
    <tr><th>&nbsp;</th><td>&nbsp;</td></tr>
    <tr><th>RL Status:</th><td>Out Of Pod Until {$showuser->OOPUntil|date_format:"%B %e, %Y"}{if $showuser->IsOOP && !empty($showuser->OOPNote)} <br />{$showuser->OOPNote}{/if}</td></tr>
  {/if}
  </table>
  
  {if !empty($posts) }
    <hr size="0" />
    <h3>Recent Forum Posts by {$showuser->Name}</h3>
    {foreach name=results from=$posts key=key item=result}
      <p><a href="forums.php?readreply={$result->ID}&amp;topicid={$result->TopicID}">{$result->TopicTitle}</a>
      <span class="info"> on {$core->GMTToLocal($result->DateCreated)}</span>
      <br />{$result->Text}</p>
    {/foreach}
  {/if}
    
{else}
  <a href="profile.php?action=updateportrait" title="Update Portait">
  <img class="portrait" src="{$core->PortraitFromCharID($user->CharID, 256)}" width="128" height="128" />
  </a>
  <table>
  <tr><th>Name:</th><td>{$user->Name}</td></tr>
  {if !empty($user->Title)}<tr><th>Title:</th><td>{$user->Title}</td></tr>{/if}
  {if !empty($user->Alts) && !$user->IsGuest}
    {foreach name=alts from = $user->Alts item=alt}
      {if $smarty.foreach.alts.first}
        <tr><th rowspan="{$smarty.foreach.alts.total}">Alts:</th><td>{$alt}</td></tr>
      {else}
        <tr><td>{$alt}</td></tr>
      {/if}
    {/foreach}
  {/if}
  <tr><th>Time Zone:</th><td>GMT&nbsp;{if $user->TimeZone < 0}{$user->TimeZone}{elseif $user->TimeZone > 0}+{$user->TimeZone}{/if}</td></tr>
  <tr><th>Email:</th><td>{$user->Email}</td></tr>
  <tr><th>IM:</th><td>{$user->IM}</td></tr>
  <tr><th>Date of Birth:</th><td>{$user->BirthDate|date_format:"%B %e, %Y"}</td></tr>
  <tr><th>Location:</th><td>{$user->Location}</td></tr>
  {if $user->IsOOP}
    <tr><th>RL Status:</th><td>Out Of Pod Until {$user->OOPUntil|date_format:"%B %e, %Y"}{if $user->IsOOP && !empty($user->OOPNote)} <br />{$user->OOPNote}{/if}</td></tr>
  {/if}
  </table>
  {if $result == 1}
  <div class="info"><p>Your profile changes are saved.</p></div>
  {elseif $result == 2}
  <div class="info"><p>Your password is changed.</p></div>
  {elseif $result == 5}
  <div class="info"><p>Selected alts are removed from your profile.</p></div>
  {elseif $result == 6}
  <div class="info"><p>Your signature is saved.</p></div>
  {/if}
{/if}

{include file='footer.tpl'}
