{include file='header.tpl' title=' | Notepad'}

{if $IGB }<h2>Notepad</h2>{/if}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="notepad.php">View All Notes</a>
{if $IGB } | {/if}
<a class="header" href="notepad.php?action=new">New Note</a>
</div>
<br />
<!-- End Section Navigation Buttons -->

  {if $action == "home" }
    {if empty($titles)}
      <p>Your notepad is empty. Click the 'New Note' button above to create a new note.</p>
    {/if}
    {foreach from=$titles key=key item=title}
      <a href="notepad.php?read={$key}">{$title}</a><br />
    {/foreach}
  {elseif $action == "new" }
    <form method="post" action="notepad.php?action=newdone">
    Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
    {$core->HTMLEditor("text", $text, "300")}
    <br />
    <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
    </form>
  {elseif $action == "read" }
    <form method="post" action="notepad.php?action=editdone">
    <input type="hidden" name="id" value="{$id}" />
    Title: <input type="text" name="title" size="60" value="{$title}" /><br /><br />
    {$core->HTMLEditor("text", $text, "300")}
    <br />
    <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Delete" />&nbsp;<input type="submit" name="submit" value="Cancel" />
    </form>  
  {/if}

  {if $result == 1}
  <div class="error"><p>Title and text cannot be empty.</p></div>
  {/if}
  
{include file='footer.tpl'}
