{include file='header.tpl' title=' | Calendar'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="calendar.php">View All Upcoming Events</a>
{if $canpost }
<a class="header" href="calendar.php?action=new">New Event</a>
{/if}
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $result == 1}
  <div class="error"><p>Title and text cannot be empty.</p></div>
{elseif $result == 2}
  <div class="error"><p>Event cannot be in the past.</p></div>
{elseif $result == 3}
  <div class="error"><p>This is not a valid date. Please make sure that the day is within the allowed number of days for the selected month.</p></div>
{/if}

{if $action == "home"}
  <table><tr><td>
    
  {if empty($calendar)}
    <h2>Calendar</h2>
    <p>There are no upcoming events.</p>
  {/if}
  {foreach name=calendar from=$calendar item=item}
    <a name="item{$item->ID}"></a><h3>{$item->Title}&nbsp;-&nbsp;{$core->GMTToLocal($item->Date)}&nbsp;({$core->GMTFormat($item->Date)} EVE Time)<span class="info">&nbsp;-&nbsp;by&nbsp;{$item->AuthorName}</span></h3>
    {$item->Text}
    <p>
    {if $item->ReadAccess == 3}
      <span class="info">This event is visible to managers only.</span><br/>
    {elseif $item->ReadAccess == 4}
      <span class="info">This event is visible to directors only.</span><br/>
    {/if}
    {if empty($item->Signups)}
      <span class="info">No characters have signed up for this event.</span>
    {else}
      <span class="info"><b>Sign-ups:&nbsp;</b>
      {foreach name=signups from=$item->Signups item=member}{$member}{if !($smarty.foreach.signups.last)},&nbsp;{/if}{/foreach}
      </span>
    {/if}
    </p>
    <p>
    <a class="header" href="calendar.php?signup={$item->ID}">Sign-Up</a>
    {if $item->Author == $user->ID }
      <a class="header" href="calendar.php?edit={$item->ID}">Edit This Event</a>
      <a class="header" href="calendar.php?delete={$item->ID}">Delete This Event</a>
    {elseif $user->AccessRight() >= 4 || $isadmin == true }
      <a class="adminheader" href="calendar.php?edit={$item->ID}">Edit This Event</a>
      <a class="adminheader" href="calendar.php?delete={$item->ID}">Delete This Event</a>
    {/if}
    </p>
    {if $smarty.foreach.calendar.last==FALSE }
      <hr size="0" />
    {/if}
  {/foreach}
  </td><td align="right" style="padding-left: 1em;">
  <!-- Month view -->
  <table class="data">
  <tr><th colspan="7">{$thismonth}</th></tr>
  <tr><th width="14%">Sun</th><th width="14%">Mon</th><th width="14%">Tue</th><th width="14%">Wed</th><th width="14%">Thu</th><th width="15%">Fri</th><th width="15%">Sat</th></tr>
  {assign var='col' value='1'}
  {foreach from=$thismonthdays item=day}
    {if $col == 1}<tr>{/if}
    <td>{$day}</td>
    {assign var='col' value=`$col+1`}
    {if $col == 8}</tr>{assign var='col' value='1'}{/if}  
  {/foreach}
  </table>
  <!-- Next month -->
  <table class="data">
  <tr><th colspan="7">{$nextmonth}</th></tr>
  <tr><th width="14%">Sun</th><th width="14%">Mon</th><th width="14%">Tue</th><th width="14%">Wed</th><th width="14%">Thu</th><th width="15%">Fri</th><th width="15%">Sat</th></tr>
  {assign var='col' value='1'}
  {foreach from=$nextmonthdays item=day}
    {if $col == 1}<tr>{/if}
    <td>{$day}</td>
    {assign var='col' value=`$col+1`}
    {if $col == 8}</tr>{assign var='col' value='1'}{/if}  
  {/foreach}
  </table>
  <!-- End month view -->
  </td></tr></table>
{elseif $action == "view"}
  <h2>Calendar ({$view})</h2>
  
  {if empty($calendar)}
    <p>There are no events in this day.</p>
  {/if}
  {foreach name=calendar from=$calendar item=item}
    <a name="item{$item->ID}"></a><h3>{$item->Title}&nbsp;-&nbsp;{$core->GMTToLocal($item->Date)}&nbsp;({$core->GMTFormat($item->Date)} EVE Time)<span class="info">&nbsp;-&nbsp;by&nbsp;{$item->AuthorName}</span></h3>
    {$item->Text}
    <p>
    {if $item->ReadAccess == 3}
      <span class="info">This event is visible to managers only.</span><br/>
    {elseif $item->ReadAccess == 4}
      <span class="info">This event is visible to directors only.</span><br/>
    {/if}
    {if empty($item->Signups)}
      <span class="info">No characters have signed up for this event.</span>
    {else}
      <span class="info"><b>Sign-ups:&nbsp;</b>
      {foreach name=signups from=$item->Signups item=member}{$member}{if !($smarty.foreach.signups.last)},&nbsp;{/if}{/foreach}
      </span>
    {/if}
    </p>
    <p>
    <a class="header" href="calendar.php?signup={$item->ID}">Sign-Up</a>
    {if $item->Author == $user->ID }
      <a class="header" href="calendar.php?edit={$item->ID}">Edit This Event</a>
      <a class="header" href="calendar.php?delete={$item->ID}">Delete This Event</a>
    {elseif $user->AccessRight() >= 4 || $isadmin == true }
      <a class="adminheader" href="calendar.php?edit={$item->ID}">Edit This Event</a>
      <a class="adminheader" href="calendar.php?delete={$item->ID}">Delete This Event</a>
    {/if}
    </p>
    {if $smarty.foreach.calendar.last==FALSE }
      <hr size="0" />
    {/if}
  {/foreach}
{elseif $action == "new" }
  <form method="post" action="calendar.php?action=newdone">
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  Date: {html_select_date prefix=cal_ start_year=+0 end_year=+1 day_value_format=%02d time=$date}<br /><br />
  EVE Time: {html_select_time prefix=cal_ display_seconds=false minute_interval=15 time=$date}<br /><br />
  
  {$core->HTMLEditor("text", $text, "300")}
  <br />
  
  <b>Who can attend this event?</b><br />
  <blockquote>
  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />
  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />
  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />
  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />
  </blockquote>
    
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "read"}
  <h3>{$title}<span class="info">&nbsp;-&nbsp;by&nbsp;{$author}&nbsp;on&nbsp;{$core->GMTToLocal($date)}&nbsp;({$core->GMTFormat($item->Date)} EVE Time)</span></h3>
  {$text}
  <br />
{elseif $action == "edit" }
  <form method="post" action="calendar.php?action=editdone">
  <input type="hidden" name="id" value="{$id}" />
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  {$core->HTMLEditor("text", $text, "300")}
  <br />
  Date: {html_select_date prefix=cal_ start_year=+0 end_year=+1 day_value_format=%02d time=$date}<br /><br />
  EVE Time: {html_select_time prefix=cal_ display_seconds=false time=$date minute_interval=15}<br /><br />
  
  <b>Who can attend this event?</b><br />
  <blockquote>
  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />
  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />
  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />
  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />
  </blockquote>
  
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Delete" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>  
{/if}

{include file='footer.tpl'}
