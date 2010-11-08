{include file='header.tpl' title=' | Mail' script='mail.js'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="mail.php">Inbox</a>
{if $IGB } | {/if}
<a class="header" href="mail.php?action=sentitems">Sent Items</a>
{if $IGB } | {/if}
<a class="header" href="mail.php?action=compose">Compose Message</a>
{if $action == "read"}
<br /><br />
<a class="subheader" href="mail.php?reply={$message->ID}">Reply</a>
{if $IGB } | {/if}
<a class="subheader" href="mail.php?forward={$message->ID}">Forward</a>
{if $IGB } | {/if}
<a class="subheader" href="mail.php?replytoall={$message->ID}">Reply To All</a>
{if $IGB } | {/if}
<a class="subheader" href="mail.php?delete={$message->ID}" onclick="javascript:return confirm('Are you sure you want to delete this message?');" >Delete</a>
{if $IGB } | {/if}
<a class="subheader" href="mail.php?move={$message->ID}">Move To Folder</a>
{elseif ($action == "inbox" || $action == "sentitems") && !empty($messages) && ! $IGB}
<br /><br />
<form method="get" name="searchform" action="mail.php">
<a class="subheader" href="mail.php?action={$action}&amp;folder={$folder}&amp;order=date">Sort By Date</a>
<a class="subheader" href="mail.php?action={$action}&amp;folder={$folder}&amp;order=from">Sort By Sender</a>
<a class="subheader" href="mail.php?action={$action}&amp;folder={$folder}&amp;order=subject">Sort By Subject</a>
<input type="hidden" name="action" value="search" />
<input type="hidden" name="mailbox" value="{$action}" />
<input type="text" name="query" size="20" value="{$query}" />
<a class="subheader" href="#" onclick="javascript:document.searchform.submit();return false;">Search</a>
</form>
{elseif $action == "search" && ! $IGB}
<br /><br />
<form method="get" name="searchform" action="mail.php">
<input type="hidden" name="action" value="search" />
<input type="hidden" name="mailbox" value="{$mailbox}" />
<input type="text" name="query" size="20" value="{$query}" />
<a class="subheader" href="#" onclick="javascript:document.searchform.submit();return false;">Search</a>
</form>
{/if}
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "inbox" || $action == "sentitems" || $action == "search"}

  <h2>{if $action == "inbox"}Inbox{elseif $action == "sentitems"}Sent Items{elseif $action == "search"}Search Results{/if}{if !empty($folder)} ({$folder}){/if}</h2>
  
  <!-- Page Navigation -->
  {if $pagecount > 1}
    <br />
    <div class="pages">
    {if $page!=0}
      <a href="mail.php?action={$action}&amp;folder={$folder}&amp;page={$page}">&lt;&lt;</a>
    {else}
      <span>&lt;&lt;</span>
    {/if}
    {if $IGB} | {/if}
    {section name=pages start=0 loop=$pagecount}
      {if $smarty.section.pages.index==$page}
      <span>{$smarty.section.pages.index+1}</span>
      {else}
      <a href="mail.php?action={$action}&amp;folder={$folder}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>
      {/if}
      {if $IGB} | {/if}
    {/section}
    {if $page<$pagecount-1}
      <a href="mail.php?action={$action}&amp;folder={$folder}&amp;page={$page+2}">&gt;&gt;</a>
    {else}
      <span>&gt;&gt;</span>
    {/if}
    </div>
  {/if}
  <!-- End Page Navigation -->
 
  {if empty($messages)}
    {if $action == "search"}
    <p>Your search - <b>{$query}</b> - did not match any messages.</p>
    {else}
    <p>There are no messages in this folder.</p>
    {/if}
  {else}
    <form method="post" name="maillist" action="mail.php?action=do">
    <input type="hidden" name="mailbox" value="{$action}" />

    <table class="data" style="table-layout: fixed; width: 100%">
    <tr>
    <th style="width: 2em;">&nbsp;</th>
    <th style="width: 10em;">{if $action == "inbox"}From{else}To{/if}</th>
    <th>Message</th>
    <th style="width: 8em;">{if $action == "inbox"}Received{else}Sent{/if}</th>
    </tr>  
  {/if}
  
  {foreach from=$messages item=message}
    <tr class='{cycle values="altrow1,altrow2"}'>
    <td style="width: 2em;"><input type="checkbox" name="mailitem{$message->ID}" /></td>
    <td style="width: 10em; overflow:hidden;"><a class="maillist" href="mail.php?read={$message->ID}" style="white-space: nowrap;">{if $action == "inbox"}{$message->FromName|truncate:40}{else}{$message->ToName|truncate:40}{/if}</a></td>
    <td style="overflow:hidden; white-space: nowrap;">
    <a class="maillist" href="mail.php?read={$message->ID}">
    {if $message->IsRead == false}<span style="color: #FFFF66;">{else}<span>{/if}{$message->Title|truncate:60}</span>
    <span class="maillistinfo"> - {$message->Text|truncate:200}</span>
    </a>
    </td>
    <td style="width: 8em; overflow:hidden; white-space: nowrap;"><a class="maillist" href="mail.php?read={$message->ID}">{$core->GMTToLocal($message->Date)}</a></td>
    </tr>
  {/foreach}

  {if !empty($messages)}
    </table>
    {if !$IGB}
    <a href="#" onclick="javascript:SelectAll(); return false;">Select All</a>
    |
    <a href="#" onclick="javascript:SelectNone(); return false;">Select None</a>
    |
    {/if}
    With Selected Messages:&nbsp;
    <select name="dowhat" id="dowhat">
    <option value="markread">Mark As Read</option>
    <option value="markunread">Mark As Unread</option>
    <option value="delete">Delete</option>
    </select>
    <input type="submit" name="submit" value="Go" onclick="javascript:var o = ObjFromID('dowhat');var os = o.options[o.selectedIndex];if(os.value == 'delete') return confirm('Are you sure you want to delete the selected messages?');" />
    </form>
  {/if}

  <!-- Page Navigation -->
  {if $pagecount > 1}
    <br />
    <div class="pages">
    {if $page!=0}
      <a href="mail.php?action={$action}&amp;folder={$folder}&amp;page={$page}">&lt;&lt;</a>
    {else}
      <span>&lt;&lt;</span>
    {/if}
    {if $IGB} | {/if}
    {section name=pages start=0 loop=$pagecount}
      {if $smarty.section.pages.index==$page}
      <span>{$smarty.section.pages.index+1}</span>
      {else}
      <a href="mail.php?action={$action}&amp;folder={$folder}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>
      {/if}
      {if $IGB} | {/if}
    {/section}
    {if $page<$pagecount-1}
      <a href="mail.php?action={$action}&amp;folder={$folder}&amp;page={$page+2}">&gt;&gt;</a>
    {else}
      <span>&gt;&gt;</span>
    {/if}
    </div>
  {/if}
  <!-- End Page Navigation -->

{elseif $action == "read"}
  <div class="mailheader">
  <b>From: </b>{$message->FromName}<br />
  <b>To: </b>{$message->ToName}<br />
  {if !empty($message->CCName)}<b>CC: </b>{$message->CCName}<br />{/if}
  {if ($message->IsInbox == false) && !empty($message->BCCName)}<b>BCC: </b>{$message->BCCName}<br />{/if}
  {if $message->IsInbox}<b>Received: </b>{else}<b>Sent: </b>{/if}{$core->GMTToLocal($message->Date)}
  </div>
  <h3>{$message->Title}</h3>
  {$message->Text}
{elseif $action == "compose"}
  <form method="post" action="mail.php?action=composedone">
  <table>
  <tr><td>Select Recipients: </td><td>
    <select name='names' id='names'>
    {foreach from=$names key=id item=name}
      <option value="{$id}">{$name}</option>
    {/foreach}
    </select>&nbsp;
    {if !$IGB}
    <a href="#" onclick="javascript:AddTo();return false;" >Add To</a>
    |
    <a href="#" onclick="javascript:AddCC();return false;" >Add CC</a>
    |
    <a href="#" onclick="javascript:AddBCC();return false;" >Add BCC</a>
    {else}
    <input type="submit" name="submit" value="Add To" />
    {/if}
  </td></tr>
  {if !$IGB}
  <tr><td>To: </td><td><input type="text" readonly="readonly" name="to" id="to" size="60" value="{$to}" /><input type="hidden" name="toid" id="toid" value="{$toid}" />&nbsp;<a href="#" onclick="javascript:ClearTo();return false;" >Clear</a></td></tr>
  <tr><td>CC: </td><td><input type="text" readonly="readonly" name="cc" id="cc" size="60" value="{$cc}" /><input type="hidden" name="ccid" id="ccid" value="{$ccid}" />&nbsp;<a href="#" onclick="javascript:ClearCC();return false;" >Clear</a></td></tr>
  <tr><td>BCC: </td><td><input type="text" readonly="readonly" name="bcc" id="bcc" size="60" value="{$bcc}" /><input type="hidden" name="bccid" id="bccid" value="{$bccid}" />&nbsp;<a href="#" onclick="javascript:ClearBCC();return false;" >Clear</a></td></tr>
  {else}
  <tr><td>To: </td><td>{$to}<input type="hidden" name="to" id="to" value="{$to}" /><input type="hidden" name="toid" id="toid" value="{$toid}" /></td></tr>
  {/if}
  <tr><td>Subject: </td><td><input type="text" name="subject" size="60" value="{$subject}" /><br /></td></tr>
  </table>
  {$core->HTMLEditor("text", $text, "300")}
  <br />
  <input type="submit" name="submit" value="Send" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
  
{/if}


{if $result == 1}
<div class="error"><p>Subject and text cannot be empty.</p></div>
{elseif $result == 2}
<div class="error"><p>You must enter at least one recipient.</p></div>
{/if}

{include file='footer.tpl'}
