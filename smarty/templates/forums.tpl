{include file='header.tpl' title=$pagetitle script='forums.js'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="forums.php">Forums Home</a>
<a class="header" href="forums.php#recent_posts">Recent Posts</a>
<a class="header" href="forums.php?action=unread">Show All Unread Posts</a>
{if $action == "home"}
  <a class="header" href="forums.php?action=search">Search</a>
{elseif $action == "category"}
  <a class="header" href="forums.php?action=search&amp;searchcategory={$cat->ID}">Search</a>
  <a class="header" href="forums.php?markallread={$cat->ID}">Mark All Topics As Read</a>
{elseif $action == "topic"}
  <a class="header" href="forums.php?action=search&amp;searchcategory={$topic->CategoryID}">Search</a>
	{if $user->Name != "Guest"}
  <a class="header" href="forums.php?subscribe={$topic->ID}">Subscribe</a>
	{/if}
{/if}
{if $action == "category" && $user->AccessRight() >= $cat->WriteAccess && $user->Name != "Guest"}
  <a class="header" href="forums.php?newtopic={$cat->ID}">Start New Topic</a>
{/if}
{if $action == "topic" && $user->AccessRight() >= $topic->WriteAccess && $user->Name != "Guest" && !$topic->IsLocked }
  <a class="header" href="forums.php?reply={$topic->ID}">Reply To Topic</a>
{/if}

{if $ismoderator && ($action == "home" || $action == "category" || $action == "topic" || $action == "displayorder")}
  <a class="adminheader" id="adminlink" style="padding: 1px 0px;" href="#" onclick="javascript:ToggleAdminLinks();return false;"><span id="adminbutton" >&nbsp;</span></a>
  <a class="adminheader" id="adminlink2" style="padding: 1px 0px; display: none;" href="#" onclick="javascript:ToggleAdminLinks();return false;"><span id="adminbutton2" >&nbsp;</span></a>
{/if}
</div>
<br/>
{if $ismoderator && ($action == "home" || $action == "category" || $action == "topic" || $action == "displayorder")}
 <div id="adminlinks">
  {if ($action == "home" || $action == "displayorder") && $ismoderator}
   <a class="adminheader" href="forums.php?action=newcategory">New Category</a>
    <a class="adminheader" href="forums.php?action=displayorder">Display Order</a>
    <a class="adminheader" href="forums.php?action=stats">Statistics</a>
    <a class="adminheader" href="forums.php?action=members">Members</a>
  {/if}
  {if $action == "category" && $ismoderator}
    <a class="adminheader" href="forums.php?editcategory={$cat->ID}">Edit Category</a>
    <a class="adminheader" href="forums.php?setcategorypassword={$cat->ID}">Set Password</a>
  {/if}
  {if $action == "topic" && $ismoderator}
    <a class="adminheader" href="forums.php?rename={$topic->ID}">Rename</a>
    <a class="adminheader" href="forums.php?move={$topic->ID}&amp;originalcategory={$topic->CategoryID}">Move</a>
    <a class="adminheader" href="forums.php?deletetopic={$topic->ID}&amp;originalcategory={$topic->CategoryID}" onclick="return confirm('Are you sure you want to delete this topic?');">Delete Topic</a>
    {if $topic->IsLocked }
      <a class="adminheader" href="forums.php?unlock={$topic->ID}">Unlock</a>
    {else}
      <a class="adminheader" href="forums.php?lock={$topic->ID}">Lock</a>
    {/if}
    {if $topic->IsSticky }
      <a class="adminheader" href="forums.php?unsticky={$topic->ID}">UnSticky</a>
    {else}
      <a class="adminheader" href="forums.php?sticky={$topic->ID}">Sticky</a>
    {/if}
  {/if}
  </div>
{/if}
<!-- End Section Navigation Buttons -->

{if $action == "home"}
  {assign var='lastgroup' value=''}
  {foreach from=$cats item=cat}
    {if $lastgroup != $cat->Group}<h1 class="forumsection">{$cat->Group}</h1>{assign var='lastgroup' value=$cat->Group}{/if}
    <div class="forumrow">
    <div class="forumrowheader">
    {if $cat->HasUnreadTopics}
      <img src="../css/icon_page.png" />
    {else}
      <img src="../css/icon_page_gray.png" />
    {/if}    
    <a class="forumtopic" href="forums.php?category={$cat->ID}">{$cat->Name}</a>
    <span class="info">{if $cat->TopicCount == 1}&nbsp;-&nbsp;1 topic, last post on {$core->GMTToLocal($cat->LastPostDate)}{elseif $cat->TopicCount > 1}&nbsp;-&nbsp;{$cat->TopicCount} topics, last post on {$core->GMTToLocal($cat->LastPostDate)}{/if}&nbsp;</span>
    </div>
    <span class="foruminfo">{$cat->Description}&nbsp;</span>
    </div>
  {/foreach}
  <!-- Hot Topics -->
  {foreach name=hot from=$hottopics item=topic}
    {if $smarty.foreach.hot.first}
    <div class="hottopics" id="recent_posts">
    <h1>Recent Posts</h1>
    <table style="margin:6px;" border="0" cellspacing="0" cellpadding="0" width="100%">
    {/if}
    <tr>
    <td>
    {if $topic->IsUnread}
      <img src="../css/icon_page.png" />
    {else}
      <img src="../css/icon_page_gray.png" />
    {/if}
    {capture name=popupText assign=ToolTip}
      <span style='color: #fff; font-size: 12pt; font-weight: 
bold;'>{$topic->Title|escape}</span><br />
      <hr size='0' />
      <b>Original Poster: </b>{$topic->AuthorName|escape}<br />
      <b>Created: </b>{$core->GMTToLocal($topic->DateCreated)} ({$core->TimeDifference($topic->DateCreated)} ago)<br />
      <b>Forum: </b>{$topic->CategoryName|escape}<br />
      {if $topic->ReplyCount == 1}
      <b>Replies: </b>None<br />
      {else}
      <hr size='0' />
      <b>Replies: </b>{$topic->ReplyCount-1}<br />
      <b>Last Poster: </b>{$topic->LastPosterName|escape}<br />
      <b>Posted: </b>{$core->GMTToLocal($topic->DateLastPost)} ({$core->TimeDifference($topic->DateLastPost)} ago)<br />
      {/if}
    {/capture}
    <span style="vertical-align:top" {if $topic->ReadAccess >= 4}class="director"{elseif $topic->ReadAccess >= 3}class="manager"{/if}>
    <a style="vertical-align:top" {popup text=$ToolTip} href="forums.php?topic={$topic->ID}&amp;page={$topic->PageCount}#item{$topic->LastReplyID}">{$topic->Title}</a>
    </span>
    </td>
    <td>{$topic->LastPosterName}</td>
    <td>{$core->GMTToLocal($topic->DateLastPost)}</td>
    </tr>
    {if $smarty.foreach.hot.last}
    </table>
    </div>
    {/if}
  {/foreach}  
{elseif $action == "displayorder"}
  <table class="data">
  <tr><th>Forum</th><th>Action</th></tr>
  {assign var='lastgroup' value=''}
  {foreach from=$cats item=cat}
  <tr class='{cycle values="altrow1,altrow2"}'>
  {if $lastgroup != $cat->Group}
    <td><span style='font-weight: bold; text-transform: uppercase; font-size: 120%;'>{$cat->Group}</span>{assign var='lastgroup' value=$cat->Group}</td>
    <td>
      <a href="forums.php?movesection={$cat->ID}&amp;dir=up">Move Up</a>
      |
      <a href="forums.php?movesection={$cat->ID}&amp;dir=down">Move Down</a>
    </td>
  </tr>
  <tr class='{cycle values="altrow1,altrow2"}'>
  {/if}
    <td>&nbsp;&nbsp;&nbsp;&nbsp;{$cat->Name}</td>
    <td>
      <a href="forums.php?movecategory={$cat->ID}&amp;dir=up">Move Up</a>
      |
      <a href="forums.php?movecategory={$cat->ID}&amp;dir=down">Move Down</a>
    </td>
  </tr>
  {/foreach}
  </table>
{elseif $action == "stats"}
  <h3>Most Viewed</h3>
  <ol>
  {foreach from=$mostviewed item=item}
    <li><a href="{$item.URL}">{$item.Title}</a> - {$item.Data} views</li>
  {/foreach}
  </ol>

  <h3>Most Replied</h3>
  <ol>
  {foreach from=$mostreplied item=item}
    <li><a href="{$item.URL}">{$item.Title}</a> - {$item.Data} replies</li>
  {/foreach}
  </ol>

  <h3>User Statistics</h3>
  <ol>
  {foreach from=$members item=item}
    <li><a href="{$item.URL}">{$item.Title}</a> - {$item.Data} posts</li>
  {/foreach}
  </ol>
{elseif $action == "members"}
  <h3>Members</h3>
  <ol>
  {foreach from=$members item=item}
    <li><a href="{$item.URL}">{$item.Title}</a> - {$item.Data}</li>
  {/foreach}
  </ol>
{elseif $action == "getcategorypassword"}
  <p>This forum board is password protected. Please enter the board password to continue.</p>
  {if $result == 1}
  <div class="error"><p>Wrong password.</p></div>
  {/if}
  <form method="post" action="forums.php?action=getcategorypassworddone">
  <input type="hidden" name="category" value="{$category}" />
  Password: <input type="password" name="password" />
  <br /><br />
  <input type="submit" name="submit" value="Submit" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "category"}

  <h1 class="forumsection"><a href="forums.php">Forums Home</a> / {$cat->Name}</h1>

  {if empty($topics) }

  <p>There are no topics in this category. Click the 'Start New Topic' button above to create a new topic.</p>

  {/if}

  {foreach from=$topics item=topic}

    <div class="forumrow">

    <div class="forumrowheader">

    {if $topic->IsSticky}

      <img src="../css/icon_star.png" />

    {/if}

    {if $topic->IsLocked}

      <img src="../css/icon_lock.png" />

    {/if}

    {if $topic->IsUnread}

      <img src="../css/icon_page.png" />

    {/if}

    {if !$topic->IsSticky && !$topic->IsLocked && ! $topic->IsUnread}

      <img src="../css/icon_page_gray.png" />

    {/if}

    <a class="forumtopic" href="forums.php?topic={$topic->ID}">{$topic->Title}</a>

    {if $topic->PageCount > 1}<span class="forumtopic">[{section name=pages start=1 loop=$topic->PageCount+1}&nbsp;<a class="forumtopic" href="forums.php?topic={$topic->ID}&amp;page={$smarty.section.pages.index}">{$smarty.section.pages.index}</a>&nbsp;{if !$smarty.section.pages.last}|{/if}{/section}]</span>{/if}

    {if $topic->ReplyCount == 1}

    <span class="info">&nbsp;-&nbsp;No replies, topic created on {$core->GMTToLocal($topic->DateLastPost)} by {$topic->LastPosterName}</span>

    {else}

    <span class="info">&nbsp;-&nbsp;{$topic->ReplyCount-1} {if $topic->ReplyCount == 2}reply{else}replies{/if}, last reply on {$core->GMTToLocal($topic->DateLastPost)} by {$topic->LastPosterName}</span>

    {/if}

    </div>

    <span class="foruminfo">Started by {$topic->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($topic->DateCreated)}</span>

    </div>

  {/foreach}

  

  <!-- Page Navigation -->

  {if $pagecount > 1}

    <div class="pages">

    {if $page!=0}

      <a href="forums.php?category={$cat->ID}&amp;page={$page}">&lt;&lt;</a>

    {else}

      <span>&lt;&lt;</span>

    {/if}

    {section name=pages start=0 loop=$pagecount}

      {if $smarty.section.pages.index==$page}

      <span>{$smarty.section.pages.index+1}</span>

      {else}

      <a href="forums.php?category={$cat->ID}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>

      {/if}

    {/section}

    {if $page<$pagecount-1}

      <a href="forums.php?category={$cat->ID}&amp;page={$page+2}">&gt;&gt;</a>

    {else}

      <span>&gt;&gt;</span>

    {/if}

    </div>

  {/if}

  <!-- End Page Navigation -->

{elseif $action == "unread"}

  <h1 class="forumsection"><a href="forums.php">Forums Home</a> / Unread Topics</h1>



  {if empty($topics) }

    <p>There are no unread topics in any category.</p>

  {/if}

  

  {if $pagecount > 1}

    <div class="pages">

    {if $page!=0}

      <a href="forums.php?action=unread&amp;page={$page}">&lt;&lt;</a>

    {else}

      <span>&lt;&lt;</span>

    {/if}

    {section name=pages start=0 loop=$pagecount}

      {if $smarty.section.pages.index==$page}

      <span>{$smarty.section.pages.index+1}</span>

      {else}

      <a href="forums.php?action=unread&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>

      {/if}

    {/section}

    {if $page<$pagecount-1}

      <a href="forums.php?action=unread&amp;page={$page+2}">&gt;&gt;</a>

    {else}

      <span>&gt;&gt;</span>

    {/if}

    </div>

  {/if}

  

  {foreach from=$topics item=topic}

    <div class="forumrow">

    <div class="forumrowheader">

    {if $topic->IsSticky}

      <img src="../css/icon_star.png" />

    {/if}

    {if $topic->IsLocked}

      <img src="../css/icon_lock.png" />

    {/if}

    {if !$topic->IsSticky && !$topic->IsLocked}

      <img src="../css/icon_page.png" />

    {/if}    

    <span style="vertical-align:top" {if $topic->ReadAccess >= 4}class="director"{elseif $topic->ReadAccess >= 3}class="manager"{/if}>

    <a style="vertical-align:top" class="forumtopic" href="forums.php?topic={$topic->ID}">{$topic->Title}</a>

    </span>

    <span class="info">&nbsp;-&nbsp;{$topic->ReplyCount} replies, last reply on {$core->GMTToLocal($topic->DateLastPost)} by {$topic->LastPosterName}</span>

    </div>

    <span class="foruminfo">Started by {$topic->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($topic->DateCreated)}</span>

    </div>

  {/foreach}

  

  {if $pagecount > 1}

    <div class="pages">

    {if $page!=0}

      <a href="forums.php?action=unread&amp;page={$page}">&lt;&lt;</a>

    {else}

      <span>&lt;&lt;</span>

    {/if}

    {section name=pages start=0 loop=$pagecount}

      {if $smarty.section.pages.index==$page}

      <span>{$smarty.section.pages.index+1}</span>

      {else}

      <a href="forums.php?action=unread&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>

      {/if}

    {/section}

    {if $page<$pagecount-1}

      <a href="forums.php?action=unread&amp;page={$page+2}">&gt;&gt;</a>

    {else}

      <span>&gt;&gt;</span>

    {/if}

    </div>

  {/if}

{elseif $action == "topic"}
  <!-- Topic Title -->
  <h1 class="forumsection">
  <a href="forums.php">
  {if $topic->IsSticky}
    <img src="../css/icon_star.png" />
  {/if}
  {if $topic->IsLocked}
    <img src="../css/icon_lock.png" />
  {/if}
  Forums Home</a> / <a href="forums.php?category={$topic->CategoryID}">{$topic->CategoryName}</a>
  / <span {if $topic->ReadAccess >= 4}class="director"{elseif $topic->ReadAccess >= 3}class="manager"{/if}>
  {$topic->Title}
  </span>
  </h1>
  <!-- End Topic Title -->
  
  <!-- Page Navigation -->
  {if $pagecount > 1}
    <div class="pages">
    {if $page!=0}
      <a href="forums.php?topic={$topic->ID}&amp;page={$page}">&lt;&lt;</a>
    {else}
      <span>&lt;&lt;</span>
    {/if}
    {section name=pages start=0 loop=$pagecount}
      {if $smarty.section.pages.index==$page}
      <span>{$smarty.section.pages.index+1}</span>
      {else}
      <a href="forums.php?topic={$topic->ID}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>
      {/if}
    {/section}
    {if $page<$pagecount-1}
      <a href="forums.php?topic={$topic->ID}&amp;page={$page+2}">&gt;&gt;</a>
    {else}
      <span>&gt;&gt;</span>
    {/if}
    </div>
  {/if}
  <!-- End Page Navigation -->
  
  <!-- Replies -->
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  {foreach from=$replies item=reply}
    <tr><td class="replyheader" {if ($user->PortalSettings & 16) == 0}colspan="2"{/if}><a name="item{$reply->ID}"></a><b>{$reply->AuthorName}{if !empty($reply->AuthorCorpTicker)}&nbsp;[{$reply->AuthorCorpTicker}]{/if}</b> on {$core->GMTToLocal($reply->DateCreated)} <a class="permalink" title="Permalink" href="#item{$reply->ID}">&nbsp;</a></td></tr>
    <tr>
      {if ($user->PortalSettings & 16) == 0}<td width="100" 
class="replybody"><a href="profile.php?user={$reply->AuthorID}"><img 
class="{if $reply->IsHonorary}honportrait{else}forumportrait{/if}" 
src="{$core->PortraitFromCharID($reply->AuthorCharID, 64)}" width="64" height="64" /></a>{if !empty($reply->AuthorTitle)}<br /><span>{$reply->AuthorTitle}</span>{/if}</td>{/if}

      <td class="replybody">
        <div class="replybody" 
onmouseover="ShowReplyButtons('{$reply->ID}');" 
onmouseout="HideReplyButtons('{$reply->ID}');">
          {$reply->Text}
          {if (($user->PortalSettings & 16) == 0) && ($reply->ShowSignature)}{$reply->AuthorSignature}{/if}
          {if ($reply->EditedByID != 0) && $reply->ShowEdited}
            <p><span class="info">Edited on {$core->GMTToLocal($reply->DateEdited)} by {$reply->EditedByName} (Most recent)</span></p>
          {/if}
          
          <div class="clear" id="buttons{$reply->ID}" 
style="margin-bottom: 0.5em; visibility: visible;">
            <script type="text/javascript">
              HideReplyButtons('{$reply->ID}');
            </script>
            {if $user->AccessRight() >= $topic->WriteAccess && !$topic->IsLocked && $user->Name != "Guest"}
              <a class="replybutton" href="forums.php?reply={$topic->ID}">Reply</a>
              <a class="replybutton" href="forums.php?reply={$topic->ID}&amp;quote={$reply->ID}">Quote</a>
            {/if}
            {if $reply->AuthorID == $user->ID && !$topic->IsLocked}
              <a class="replybutton" href="forums.php?edit={$reply->ID}">Edit</a>
            {/if}
            {if $ismoderator }
              {if !($reply->AuthorID == $user->ID  && !$topic->IsLocked)}<a class="adminreplybutton" href="forums.php?edit={$reply->ID}">Edit</a>{/if}
              <a class="adminreplybutton" href="forums.php?delete={$reply->ID}&amp;topicid={$topic->ID}" onclick="return confirm('Are you sure you want to delete this reply?');">Delete Reply</a>
            {/if}
          </div>
        </div>
      </td>
    </tr>  
  {/foreach}
  </table>
  <!-- End Replies -->
  
  <!-- Page Navigation -->
  {if $pagecount > 1}
    <div class="pages">
    {if $page!=0}
      <a href="forums.php?topic={$topic->ID}&amp;page={$page}">&lt;&lt;</a>
    {else}
      <span>&lt;&lt;</span>
    {/if}
    {section name=pages start=0 loop=$pagecount}
      {if $smarty.section.pages.index==$page}
      <span>{$smarty.section.pages.index+1}</span>
      {else}
      <a href="forums.php?topic={$topic->ID}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>
      {/if}
    {/section}
    {if $page<$pagecount-1}
      <a href="forums.php?topic={$topic->ID}&amp;page={$page+2}">&gt;&gt;</a>
    {else}
      <span>&gt;&gt;</span>
    {/if}
    </div>
  {/if}
  <!-- End Page Navigation -->
  
  <!-- Topic Title -->
  <h1 class="forumsection">
  <a href="forums.php">
  {if $topic->IsSticky}
    <img src="../css/icon_star.png" />
  {/if}
  {if $topic->IsLocked}
    <img src="../css/icon_lock.png" />
  {/if}
  Forums Home</a> / <a href="forums.php?category={$topic->CategoryID}">{$topic->CategoryName}</a>
  / {$topic->Title}</h1>
  <!-- End Topic Title -->

{elseif $action == "reply"}

  <h1 class="forumsection">

  <a href="forums.php">

  {if $topic->IsSticky}

    <img src="../css/icon_star.png" />

  {/if}

  {if $topic->IsLocked}

    <img src="../css/icon_lock.png" />

  {/if}

  Forums Home</a> / 

  <a href="forums.php?category={$topic->CategoryID}">{$topic->CategoryName}</a> /

  <a href="forums.php?topic={$topic->ID}">{$topic->Title}</a>

  </h1>

  {if $result == 1}

  <div class="error"><p>You cannot post an empty reply.</p></div>

  {/if}

  <form method="post" action="forums.php?action=replydone">

  <input type="hidden" name="topic" value="{$topic->ID}" />

  {$core->HTMLEditor("reply", $quote, "300px")}

  <br />

  <input type="checkbox" name="showsignature" id="showsignature" checked="checked" /><label for="showsignature">Display My Signature</label>

  <br /><br />

  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />

  </form>

  <table border="0" cellpadding="0" cellspacing="0" width="100%">

  {foreach from=$replies item=reply}

      <tr><td class="replyheader"><a name="item{$reply->ID}"></a><b>{$reply->AuthorName}</b> on {$core->GMTToLocal($reply->DateCreated)}</td></tr>

      <tr>

        <td class="replybody"><div class="replybody">{$reply->Text}</div></td>

      </tr>

  {/foreach}

  </table>

{elseif $action == "edit"}

  {if $result == 1}

  <div class="error"><p>You cannot post an empty reply.</p></div>

  {/if}

  <form method="post" action="forums.php?action=editdone">

  <input type="hidden" name="topicid" value="{$topicid}" />

  <input type="hidden" name="replyid" value="{$replyid}" />

  {$core->HTMLEditor("reply", $reply, "300px")}

  <br />

  <input type="checkbox" name="showedited" id="showedited" checked="checked" /><label for="showedited">Show As Edited</label>

  <br />

  <input type="checkbox" name="showsignature" id="showsignature" {if $showsignature}checked="checked"{/if} /><label for="showsignature">Display My Signature</label>

  <br /><br />

  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />

  </form>

{elseif $action == "newtopic"}

  {if $result == 2}

  <div class="error"><p>Title and text cannot be empty.</p></div>

  {/if}

  <form method="post" action="forums.php?action=newtopicdone">

  <input type="hidden" name="category" value="{$cat->ID}" />

  Title: <input type="text" name="title" size="40" value="{$title}" />

  <br /><br />

  {$core->HTMLEditor("text", $text, "300px")}

  <br />

  <input type="checkbox" name="showsignature" id="showsignature" checked="checked" /><label for="showsignature">Display My Signature</label>

  <br /><br />

  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />

  </form>

{elseif $action == "newcategory"}

  {if $result == 20}

  <div class="error"><p>Category and section titles cannot be empty.</p></div>

  {/if}

  <form method="post" action="forums.php?action=newcategorydone">

  <table border="0">

  <tr><td>Title: </td><td><input type="text" name="title" size="40" value="{$title}" /></td></tr>

  <tr><td>Description: </td><td><input type="text" name="description" size="80" value="{$description}" /></td></tr>

  <tr><td>Section: </td><td><select name="section">

  <option value="" {if empty($section)} selected="selected" {/if}>New Section (Type section title below)</option>

  {foreach name=groups from=$groups key=key item=group}

    <option value="{$key}" {if $section==$key } selected="selected" {/if}>{$group}</option>

  {/foreach}

  </select></td></tr>

  <tr><td>&nbsp;</td><td><input type="text" name="newsection" size="40" value="{$newsection}" /></td></tr>

  

  <tr><td>Who can read the topics in this category?</td><td>

  <input type="radio" name="readaccess" id="readaccess1" value="0" {if $readaccess==0}checked="checked"{/if} /><label for="readaccess1">Guests</label><br />

  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />

  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />

  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />

  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />

  </td></tr>

  

  <tr><td>Who can post replies to topics in this category?</td><td>

  <input type="radio" name="writeaccess" id="writeaccess1" value="0" {if $writeaccess==0}checked="checked"{/if} /><label for="writeaccess1">Guests</label><br />

  <input type="radio" name="writeaccess" id="writeaccess2" value="1" {if $writeaccess==1}checked="checked"{/if} /><label for="writeaccess2">Alliance Members</label><br />

  <input type="radio" name="writeaccess" id="writeaccess3" value="2" {if $writeaccess==2}checked="checked"{/if} /><label for="writeaccess3">Corporation Members</label><br />

  <input type="radio" name="writeaccess" id="writeaccess4" value="3" {if $writeaccess==3}checked="checked"{/if} /><label for="writeaccess4">Managers</label><br />

  <input type="radio" name="writeaccess" id="writeaccess5" value="4" {if $writeaccess==4}checked="checked"{/if} /><label for="writeaccess5">Directors</label><br />

  <br />&nbsp;</td></tr>

  

  <tr><td colspan="2"><input type="submit" name="submit" value="Create Category" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>

  </table>

  </form>

{elseif $action == "editcategory"}

  {if $result == 20}

  <div class="error"><p>Category and section titles cannot be empty.</p></div>

  {/if}

  <form method="post" action="forums.php?action=editcategorydone">

  <input type="hidden" name="category" value="{$category}" />

  <table border="0">

  <tr><td>Title: </td><td><input type="text" name="title" size="40" value="{$title}" /></td></tr>

  <tr><td>Description: </td><td><input type="text" name="description" size="80" value="{$description}" /></td></tr>

  <tr><td>Section: </td><td><select name="section">

  <option value="" {if empty($section)} selected="selected" {/if}>New Section (Type section title below)</option>

  {foreach name=groups from=$groups key=key item=group}

    <option value="{$key}" {if $section==$key } selected="selected" {/if}>{$group}</option>

  {/foreach}

  </select></td></tr>

  <tr><td>&nbsp;</td><td><input type="text" name="newsection" size="40" value="{$newsection}" /></td></tr>

  

  <tr><td>Who can read the topics in this category?</td><td>

  <input type="radio" name="readaccess" id="readaccess1" value="0" {if $readaccess==0}checked="checked"{/if} /><label for="readaccess1">Guests</label><br />

  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />

  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />

  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />

  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />

  </td></tr>

  

  <tr><td>Who can post replies to topics in this category?</td><td>

  <input type="radio" name="writeaccess" id="writeaccess1" value="0" {if $writeaccess==0}checked="checked"{/if} /><label for="writeaccess1">Guests</label><br />

  <input type="radio" name="writeaccess" id="writeaccess2" value="1" {if $writeaccess==1}checked="checked"{/if} /><label for="writeaccess2">Alliance Members</label><br />

  <input type="radio" name="writeaccess" id="writeaccess3" value="2" {if $writeaccess==2}checked="checked"{/if} /><label for="writeaccess3">Corporation Members</label><br />

  <input type="radio" name="writeaccess" id="writeaccess4" value="3" {if $writeaccess==3}checked="checked"{/if} /><label for="writeaccess4">Managers</label><br />

  <input type="radio" name="writeaccess" id="writeaccess5" value="4" {if $writeaccess==4}checked="checked"{/if} /><label for="writeaccess5">Directors</label><br />

  <br />&nbsp;</td></tr>

  

  <tr><td colspan="2"><input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>

  </table>

  </form>

{elseif $action == "setcategorypassword"}

  <p>Leave the password field blank to remove the password.</p>

  <form method="post" action="forums.php?action=setcategorypassworddone">

  <input type="hidden" name="category" value="{$category}" />

  Password: <input type="password" name="password" />

  <br /><br />

  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />

  </form>

{elseif $action == "move"}

  <form method="post" action="forums.php?action=movedone">

  <input type="hidden" name="topic" value="{$topic}" />

  Category: <select name="category">

  {assign var='lastgroup' value=''}

  {foreach name=cats from=$cats key=key item=cat}

    {if $lastgroup != $cat.Group}

      {if !empty($lastgroup)}

      </optgroup>

      {/if}

      <optgroup label="{$cat.Group}">

      {assign var='lastgroup' value=$cat.Group}

    {/if}

    <option value="{$key}" {if $original==$key}selected="selected"{/if}>{$cat.Name}</option>

  {/foreach}

      </optgroup>

  </select><br /><br />

  

  <input type="submit" name="submit" value="Move Topic" />&nbsp;<input type="submit" name="submit" value="Cancel" />

  </form>

{elseif $action == "rename"}

  {if $result == 1}

  <div class="error"><p>Topic title cannot be empty.</p></div>

  {/if}

  <form method="post" action="forums.php?action=renamedone">

  <input type="hidden" name="topic" value="{$topic}" />

  New Title: <input type="text" name="topicname" size="40" value="{$topicname}" />

  <br /><br />

  

  <input type="submit" name="submit" value="Rename Topic" />&nbsp;<input type="submit" name="submit" value="Cancel" />

  </form>

{elseif $action == "search"}

  <form method="get" action="forums.php">

  <input type="hidden" name="action" value="search" />

  <table border="0">

  <tr><td>Search: </td><td><input type="text" name="searchtext" value="{$searchtext}" size="40" /></td></tr>

  <tr><td>In Forum: </td><td><select name="searchcategory">

  <option value="0" {if empty($searchcategory) || $searchcategory==0}selected="selected"{/if}>All Forums</option>

  {assign var='lastgroup' value=''}

  {foreach name=cats from=$cats key=key item=cat}

    {if $lastgroup != $cat.Group}

      {if !empty($lastgroup)}

      </optgroup>

      {/if}

      <optgroup label="{$cat.Group}">

      {assign var='lastgroup' value=$cat.Group}

    {/if}

    <option value="{$key}" {if $searchcategory==$key}selected="selected"{/if}>{$cat.Name}</option>

  {/foreach}

      </optgroup>

  </select><br />&nbsp;</td></tr>

  

  <tr><td colspan="2"><input type="submit" name="submit" value="Search" />&nbsp;<input type="submit" name="submit" value="Cancel" /></td></tr>

  </table>

  </form>

  {if !empty($results) }

    <hr size="0" />

    {assign var='lastgroup' value=''}

    {foreach name=results from=$results key=key item=result}

      {if $lastgroup != $result->CategoryTitle}<h3>{$result->CategoryTitle}</h3>{assign var='lastgroup' value=$result->CategoryTitle}{/if}

      <p><a href="forums.php?readreply={$result->ID}&amp;topicid={$result->TopicID}">{$result->TopicTitle}</a>

      <span class="info">&nbsp;by&nbsp;{$result->AuthorName} on {$core->GMTToLocal($result->DateCreated)}</span>

      <br />{$result->Text}</p>

    {/foreach}

  {elseif !empty($searchtext) }

    <p>Your search - <b>{$searchtext}</b> - did not return any results.</p>

  {/if}

{elseif $action == "subscribe"}

  <div class="info">

    You have been subscribed to new replies to this topic. You will be sent a portal mail whenever a new reply is posted.<br />

    You can manage your subscriptions in your <a href="signups.php">Sign-Ups</a> page.<br />

    Click <a href="forums.php?topic={$topic}">here</a> to return to the topic.

  </div>

{/if}



<!-- Section Navigation Buttons -->

<br/>

<div class="header">

{if $action == "topic"}

  <a class="header" href="forums.php">Forums Home</a>

  <a class="header" href="forums.php#recent_posts">Recent Posts</a>

  <a class="header" href="forums.php?action=unread">Show All Unread Posts</a>

  <a class="header" href="forums.php?action=search&amp;searchcategory={$topic->CategoryID}">Search</a>

	{if $user->Name != "Guest"}

    <a class="header" href="forums.php?subscribe={$topic->ID}">Subscribe</a>

  {/if}

  {if $user->AccessRight() >= $topic->WriteAccess && $user->Name != "Guest" && !$topic->IsLocked }

    <a class="header" href="forums.php?reply={$topic->ID}">Reply To Topic</a>

  {/if}

{/if}

</div>



{include file='footer.tpl'}

