{include file='header.tpl' title=' | News'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="news.php">Recent News</a>
<a class="header" href="news.php?action=archive">News Archive</a>
{if $canpost }
<a class="header" href="news.php?action=new">Submit News</a>
{/if}
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $result == 1}
  <div class="error"><p>Title and text cannot be empty.</p></div>
{/if}

{if $action == "home"}
  <h2>Recent News</h2>

  {if empty($news)}
    <p>There are no recent news.</p>
  {/if}
  {foreach name=news from=$news key=key item=newsitem}
      <a name="item{$newsitem->ID}"></a><h3>{$newsitem->Title}<span class="info">&nbsp;-&nbsp;by&nbsp;{$newsitem->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($newsitem->Date)}</span></h3>
      {$newsitem->Text}
      {if $newsitem->Author == $user->ID }
        <p>
        <a class="header" href="news.php?edit={$newsitem->ID}">Edit</a>
        <a class="header" href="news.php?delete={$newsitem->ID}">Delete</a>
        </p>
      {elseif $user->AccessRight() >= 4 || $isadmin == true }
        <p>
        <a class="adminheader" href="news.php?edit={$newsitem->ID}">Edit</a>
        <a class="adminheader" href="news.php?delete={$newsitem->ID}">Delete</a>
        </p>
      {/if}
      {if $smarty.foreach.news.last==FALSE }
        <hr size="0" />
      {/if}
  {/foreach}
{elseif $action == "archive"}
  <h2>News Archive</h2>
  {if empty($news)}
    <p>There are no news in the archive.</p>
  {/if}
  <p>
  {foreach from=$news key=key item=newsitem}
    <a href="news.php?read={$newsitem->ID}">{$newsitem->Title}</a><span class="info">&nbsp;-&nbsp;by&nbsp;{$newsitem->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($newsitem->Date)}</span><br />
  {/foreach}
  </p>
{elseif $action == "new" }
  <form method="post" action="news.php?action=newdone">
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  {$core->HTMLEditor("text", $text, "300")}
  <br />
  
  <b>Who can read this news?</b><br />
  <blockquote>
  <input type="radio" name="readaccess" id="readaccess1" value="0" {if $readaccess==0}checked="checked"{/if} /><label for="readaccess1">Guests</label><br />
  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />
  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />
  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />
  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />
  </blockquote>
    
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{elseif $action == "read"}
  <h2>News Archive</h2>
  <h3>{$title}<span class="info">&nbsp;-&nbsp;by&nbsp;{$author}&nbsp;on&nbsp;{$core->GMTToLocal($date)}</span></h3>
  {$text}
  <br />
{elseif $action == "edit" }
  <form method="post" action="news.php?action=editdone">
  <input type="hidden" name="id" value="{$id}" />
  Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
  {$core->HTMLEditor("text", $text, "300")}
  <br />
  
  <b>Who can read this news?</b><br />
  <blockquote>
  <input type="radio" name="readaccess" id="readaccess1" value="0" {if $readaccess==0}checked="checked"{/if} /><label for="readaccess1">Guests</label><br />
  <input type="radio" name="readaccess" id="readaccess2" value="1" {if $readaccess==1}checked="checked"{/if} /><label for="readaccess2">Alliance Members</label><br />
  <input type="radio" name="readaccess" id="readaccess3" value="2" {if $readaccess==2}checked="checked"{/if} /><label for="readaccess3">Corporation Members</label><br />
  <input type="radio" name="readaccess" id="readaccess4" value="3" {if $readaccess==3}checked="checked"{/if} /><label for="readaccess4">Managers</label><br />
  <input type="radio" name="readaccess" id="readaccess5" value="4" {if $readaccess==4}checked="checked"{/if} /><label for="readaccess5">Directors</label><br />
  </blockquote>
  
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Delete" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>  
{/if}

{include file='footer.tpl'}
