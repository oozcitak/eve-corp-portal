{include file='header.tpl' title=' | Articles &amp; Guides'}

<!-- Section Navigation Buttons -->
{if $user->Name != "Guest"}
<div class="header">
<a class="header" href="articles.php">View All Articles</a>
<a class="header" href="articles.php?action=new">New Article</a>
{if $action == "read"}
<a class="header" href="articles.php?postcomment={$articleid}">Post Comment</a>
{/if}
{if $editid!=0 }
{if $isadmin}
<a class="adminheader" href="articles.php?edit={$editid}">Edit This Article</a>
{else}
<a class="header" href="articles.php?edit={$editid}">Edit This Article</a>
{/if}
{/if}
</div>
<br />
{/if}
<!-- End Section Navigation Buttons -->

{if $result == 1}
  <div class="error"><p>Title and text cannot be empty.</p></div>
{/if}

{if $action == "home" }
  {if empty($titles)}
    <p>There are no articles in the database. Click the 'New Article' button above to create a new article.</p>
  {/if}
  {foreach from=$titles item=item}
    <a href="articles.php?read={$item.ID}">{$item.Title}</a><span>&nbsp;by&nbsp;{$item.AuthorName}&nbsp;on&nbsp;{$item.Date}</span><br />
  {/foreach}
{elseif $action == "new" }
  <form method="post" action="articles.php?action=newdone">
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  {$core->HTMLEditor("text", $text, "600")}
  <br />
  
  <b>Who can read this article?</b><br />
  <blockquote>
  <input type="radio" name="readaccess" id="readaccess1" value="0" {if $readaccess==0}checked="checked"{/if} /><label for="readaccess1">Guests</label><br />
  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />
  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />
  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />
  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />
  </blockquote>
  
  <b>Who can edit this article? (You can always edit your own articles regardless of this setting.)</b><br />
  <blockquote>
  <input type="radio" name="writeaccess" id="writeaccess1" value="0" {if $writeaccess==0}checked="checked"{/if} /><label for="writeaccess1">Guests</label><br />
  <input type="radio" name="writeaccess" id="writeaccess2" value="1" {if $writeaccess==1}checked="checked"{/if} /><label for="writeaccess2">Alliance Members</label><br />
  <input type="radio" name="writeaccess" id="writeaccess3" value="2" {if $writeaccess==2}checked="checked"{/if} /><label for="writeaccess3">Corporation Members</label><br />
  <input type="radio" name="writeaccess" id="writeaccess4" value="3" {if $writeaccess==3}checked="checked"{/if} /><label for="writeaccess4">Managers</label><br />
  <input type="radio" name="writeaccess" id="writeaccess5" value="4" {if $writeaccess==4}checked="checked"{/if} /><label for="writeaccess5">Directors</label><br />
  </blockquote>
  
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "read" }
  <h3>{$title}</h3>
  <span class="info">by {$author} on {$date}</span>
  {$text}
  <div class="clear">
  {$signature}
  </div>
  
  <!-- Comments -->
  {if !empty($comments)}
  <h3>Comments</h3>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  {foreach from=$comments item=reply}
    <tr><td class="replyheader"><a name="item{$reply.ID}"></a><b>{$reply.AuthorName}</b> on {$core->GMTToLocal($reply.Date)} <a class="permalink" title="Permalink" href="#item{$reply.ID}">&nbsp;</a></td></tr>
    <tr>
      <td class="replybody">
        <div class="replybody">
          {$reply.Text}          
          <div class="clear" style="margin-bottom: 0.5em;">
            <a class="replybutton" href="articles.php?postcomment={$articleid}">Reply</a>
            {if $user->ID == $reply.Author}
              <a class="replybutton" href="articles.php?deletecomment={$reply.ID}&amp;article={$articleid}">Delete</a>
            {elseif ($user->ID == $authorid) || ($user->AccessRight() >= 4)}
              <a class="adminreplybutton" href="articles.php?deletecomment={$reply.ID}&amp;article={$articleid}">Delete</a>
            {/if}
          </div>
        </div>
      </td>
    </tr>  
  {/foreach}
  </table>
  {/if}
  <!-- End Comments -->
{elseif $action == "edit" }
  <form method="post" action="articles.php?action=editdone">
  <input type="hidden" name="id" value="{$id}" />
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  {$core->HTMLEditor("text", $text, "600")}
  <br />
  
  <b>Who can read this article?</b><br />
  <blockquote>
  <input type="radio" name="readaccess" id="readaccess1" value="0" {if $readaccess==0}checked="checked"{/if} /><label for="readaccess1">Guests</label><br />
  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />
  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />
  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />
  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />
  </blockquote>
  
  <b>Who can edit this article? (You can always edit your own articles regardless of this setting.)</b><br />
  <blockquote>
  <input type="radio" name="writeaccess" id="writeaccess1" value="0" {if $writeaccess==0}checked="checked"{/if} /><label for="writeaccess1">Guests</label><br />
  <input type="radio" name="writeaccess" id="writeaccess2" value="1" {if $writeaccess==1}checked="checked"{/if} /><label for="writeaccess2">Alliance Members</label><br />
  <input type="radio" name="writeaccess" id="writeaccess3" value="2" {if $writeaccess==2}checked="checked"{/if} /><label for="writeaccess3">Corporation Members</label><br />
  <input type="radio" name="writeaccess" id="writeaccess4" value="3" {if $writeaccess==3}checked="checked"{/if} /><label for="writeaccess4">Managers</label><br />
  <input type="radio" name="writeaccess" id="writeaccess5" value="4" {if $writeaccess==4}checked="checked"{/if} /><label for="writeaccess5">Directors</label><br />
  </blockquote>

  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this article?');" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>  
{elseif $action == "postcomment" }
  <form method="post" action="articles.php?action=newcomment">
  <input type="hidden" name="article" value="{$articleid}" />
  {$core->HTMLEditor("text", "", "200")}
  <br />
  <input type="submit" name="submit" value="Post Comment" />
  </form>
    
  <br />
{/if}
  
{include file='footer.tpl'}
