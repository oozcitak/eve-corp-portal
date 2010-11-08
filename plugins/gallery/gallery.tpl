{include file='header.tpl' title=' | Image Gallery' style='gallery.css' extraheader="<link rel='alternate' href='gallery.rss.php?action=$action' type='application/rss+xml' title='' id='gallery' />" script='http://lite.piclens.com/current/piclens.js'}

<!-- Section Navigation Buttons -->
<div class="header">
{if $action == "recruitment" || isset($recruitname)}

    {if $user->AccessRight() >= 4}
    <a class="header" href="../recruitment/index?action=applications&portalid={$recruitment}{$portalid}">Back to {$recruitname}'s Application</a>
    {/if}

    {if ($action == "show") && ($canedit > 0)}
    <a class="{if $canedit == 2}adminheader{else}header{/if}" href="index.php?delete={$image.ID}" onclick="javascript:return confirm('Are you sure you want to delete this image?');">Delete This Image</a>
    {/if}

{else}


    {if $user->AccessRight() == 0}
        <a class="header" href="../recruitment/index.php">Recruitment Center</a>
        <a class="header" href="index.php?action=user">My Screenshots</a>
        <a class="header" href="index.php?action=upload">Upload</a>
    {else}
        <a class="header" href="index.php">Gallery Home</a>
        <a class="header" href="index.php?action=user">My Images</a>
        <a class="header" href="index.php?action=search">Search</a>
        <a class="header" href="index.php?action=upload">Upload</a>
    {/if}

        {if ($action == "show") && ($canedit > 0)}
            <a class="{if $canedit == 2}adminheader{else}header{/if}" href="index.php?delete={$image.ID}" onclick="javascript:return confirm('Are you sure you want to delete this image?');">Delete This Image</a>
        {/if}

        {if $user->AccessRight() >= 4}
            <a class="adminheader" href="index.php?action=admin">Gallery Administration</a>
        {/if}
{/if}
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "home" || $action == "user" || $action == "search" || $action == "recruitment"}
  {if $action == "home" || $action == "recruitment"}
    <h3>Image Gallery</h3>
  {elseif $action == "user" || $action == "recruitment"}
    <h3>My Images</h3>
  {else}
    <h3>Search Image Gallery</h3>
  {/if}

  {if $action == "home" || $action == "user" || $action == "recruitment"}
    <p>Image gallery uses media RSS to publish images. You can view the gallery as a slide show if you have <a href="http://www.piclens.com/">PicLens</a> installed.</p>
    <p>
    <a href="javascript:PicLensLite.start();">
    Start Slide Show
    <img src="http://lite.piclens.com/images/PicLensButton.png" style="vertical-align: middle;" alt="PicLens" width="16" height="12" border="0" />
    </a>
    </p>
  {else}    
    <form method="get" action="index.php">
    <input type="text" name="search" value="{$searchtext}" size="40" />&nbsp;<input type="submit" name="submit" value="Search" />
    </form>
  {/if}
  
  {if empty($gallery)}
    {if !empty($searchtext) }
      <p>Your search - <b>{$searchtext}</b> - did not return any results.</p>
    {elseif $action == "home" || $action == "user"}
      <p>Image gallery is empty.</p>
    {/if}
  {else}
    <!-- Page Navigation -->
    {if $pagecount > 1}
      <div class="pages">
      {if $page != 1}
        <a href="index.php?action={$action}&amp;page={$page-1}">&lt;&lt;</a>
      {else}
        <span>&lt;&lt;</span>
      {/if}
      {section name=pages start=0 loop=$pagecount}
        {if $smarty.section.pages.index == $page - 1}
        <span>{$smarty.section.pages.index+1}</span>
        {else}
        <a href="index.php?action={$action}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>
        {/if}
      {/section}
      {if $page != $pagecount}
        <a href="index.php?action={$action}&amp;page={$page+1}">&gt;&gt;</a>
      {else}
        <span>&gt;&gt;</span>
      {/if}
      </div>
    {/if}
    <!-- End Page Navigation -->
  
    <table>
    {section name=i start=0 loop=5}
      <tr>
      {section name=j start=0 loop=5}
        {assign var='index' value=`$smarty.section.i.index*5+$smarty.section.j.index`}
        {assign var='image' value=`$gallery[$index]`}
        {if ($image)}
        {capture name=popUp assign=popText}
          <span style='color: #fff; font-size: 12pt; font-weight: bold;'>{$image.Title}</span><br />
          by {$image.Owner}<br />
          {$image.Date}
        {/capture}
        <td class="gal" {popup text=$popText}>
          {if $action == "recruitment"}
          <a href="index.php?show={$image.ID}&portalid={$recruitment}&recruitname={$recruitname}"><img src="{$image.ThumbURL}" /></a>
          {else}
          <a href="index.php?show={$image.ID}"><img src="{$image.ThumbURL}" /></a>
          {/if}
        </td>
        {else}
        <td>&nbsp;</td>
        {/if}
      {/section}
      </tr>
      <tr>
      {section name=j start=0 loop=5}
        {assign var='index' value=`$smarty.section.i.index*5+$smarty.section.j.index`}
        {assign var='image' value=`$gallery[$index]`}
        {if ($image)}
        <td class="galhead">
          <a href="index.php?show={$image.ID}">{$image.Title}</a>
        </td>
        {else}
        <td>&nbsp;</td>
        {/if}
      {/section}
      </tr>      
    {/section}
    </table>
    
    <!-- Page Navigation -->
    {if $pagecount > 1}
      <div class="pages">
      {if $page != 1}
        <a href="index.php?action={$action}&amp;page={$page-1}">&lt;&lt;</a>
      {else}
        <span>&lt;&lt;</span>
      {/if}
      {section name=pages start=0 loop=$pagecount}
        {if $smarty.section.pages.index == $page - 1}
        <span>{$smarty.section.pages.index+1}</span>
        {else}
        <a href="index.php?action={$action}&amp;page={$smarty.section.pages.index+1}">{$smarty.section.pages.index+1}</a>
        {/if}
      {/section}
      {if $page != $pagecount}
        <a href="index.php?action={$action}&amp;page={$page+1}">&gt;&gt;</a>
      {else}
        <span>&gt;&gt;</span>
      {/if}
      </div>
    {/if}
    <!-- End Page Navigation -->
  {/if}
{elseif $action == "show"}
  {if empty($image)}
    <p>This image no longer exists.</p>
  {else}
    <h3>{$image.Title}</h3>
    <p>Uploaded by {$image.Owner} on {$image.Date}</p>
    <a href="{$image.URL}">
    <img class="galleryimage" src="{$image.URL}" />
    </a>
   {if $user->AccessRight() == 0}
   {else}
    <h3>Comments</h3>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    {foreach from=$comments item=comment}
      <tr><td class="replyheader"><a name="item{$comment.ID}"></a><b>{$comment.User}</b> on {$core->GMTToLocal($comment.Date)} <a class="permalink" title="Permalink" href="#item{$comment.ID}">&nbsp;</a>            {if $comment.UserID == $user->ID || $user->AccessRight() >= 4}<a href="index.php?deletecomment={$comment.ID}&amp;image={$image.ID}">Delete</a>{/if}</td></tr>
      <tr>
        <td class="replybody">
          <div class="replybody">
            {$comment.Text}
          </div>
        </td>
      </tr>
    {/foreach}
    </table>
    <form method="post" action="index.php?action=comment">
    <input type="hidden" name="image" value="{$image.ID}" />
    <input type="text" name="comment" size="60" />&nbsp;<input type="submit" name="submit" value="Post Comment" />
    </form>
   {/if}
  {/if}
{elseif $action == "upload"}
  <h3>Upload Image</h3>
  {if $percent == 100}
    <p>Image gallery is using all its allocated disk space. You can not upload new images. Please contact an administrator.</p>
    <div class="progress"><div style="width:{$percent}%">{$percent}%&nbsp;({$size}/{$maxsize}&nbsp;GB)</div></div>
  {else}
    <p>You can upload GIF, JPG and PNG files. File size is limited to 5 MB.</p>
    <div class="progress"><div style="width:{$percent}%">{$percent}%&nbsp;({$size}/{$maxsize}&nbsp;GB)</div></div>
    <p>&nbsp;</p>
       {if $user->AccessRight() == 0}
       <h3><span style="color: #ff6600">Use the following criteria below for uploading your screenshots.<br>
       Do not forget to label your screenshots.</span></h3>
       <ol>
        <li><i>Character Selection Screen showing all alts (1 screenshot per Account) </i></li>
        <li><i>Wallet Journal filtered to &quot;Player Donations&quot; (1 screenshot per character</i>)</li>
        </ol><br><br>
       {/if}
    <form method="post" action="index.php?action=uploaddone" enctype="multipart/form-data">
    <table>
    <tr><td>Filename: </td><td><input type="file" name="file" size="40" /></td></tr>
    <tr><td>Title: </td><td><input type="text" name="title" size="80" /><br />&nbsp;</td></tr>
    <tr><td>Access: </td><td>
   {if $user->AccessRight() == 0}
    <input type="radio" name="readaccess" id="readaccess1" value="1" checked="checked" /><label for="readaccess1">Registered Members</label><br />
   {else}
    <input type="radio" name="readaccess" id="readaccessp" value="-1" /><label for="readaccessp">Private</label><br />
    <input type="radio" name="readaccess" id="readaccess3" value="2" checked="checked" /><label for="readaccess3">Corporation Members</label><br />
    <input type="radio" name="readaccess" id="readaccess4" value="3" /><label for="readaccess4">Managers</label><br />
    <input type="radio" name="readaccess" id="readaccess5" value="4" /><label for="readaccess5">Directors</label><br />
   {/if}
    <br />&nbsp;</td></tr>
    <tr><td>&nbsp;</td><td><input type='submit' name='submit' value='Upload' /></td></tr>
    </table>
    </form>
  {/if}
{elseif $action == "admin"}
  <h3>Gallery Administration</h3>
  
  <p>The gallery contains {$count} images including thumbnails. Total disk size of the images is {$size}.</p>
  <div class="progress"><div style="width:{$percent}%">{$percent}%&nbsp;({$size}/{$maxsize}&nbsp;GB)</div></div>
  <p>Purging old images does not delete images used in user signatures. Deleting all images deletes signature images too.</p>
  
  <a href="index.php?action=delete1year" onclick="javascript:return confirm('Are you sure you want to delete images older than a year?');">Purge Images Older Than A Year</a><br />
  <a href="index.php?action=delete6month" onclick="javascript:return confirm('Are you sure you want to delete images older than six months?');">Purge Images Older Than Six Months</a><br />
  <a href="index.php?action=delete1month" onclick="javascript:return confirm('Are you sure you want to delete images older than one month?');">Purge Images Older Than One Month</a><br />
  <br />
  <a href="index.php?action=deleteall" onclick="javascript:return confirm('Are you sure you want to delete ALL images?');">Delete All Images</a><br />
  <br />
  <a href="index.php?action=deletethumbs">Recreate Thumbnails</a><br />
  </p>
{/if}

{if $result == 1}
<div class="info">Image successfully uploaded.</div>
{elseif $result == 2}
<div class="error">Error uploading image. <ul><li>You can upload GIF, JPG and PNG files.</li><li>File size is limited to 5 MB.</li></ul></div>
{/if}

{include file='footer.tpl'}