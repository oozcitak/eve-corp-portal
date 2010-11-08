{include file='header.tpl' title=' | Feedback'}

<h3>Feedback Form</h3>

{if $user->HasPortalRole("32")}
  {foreach from=$feedbacks item=feedback name=feed}
  <table>
  <tr><td>Posted On:</td><td>{$feedback.6}</td></tr>
  <tr><td>Username:</td><td>{$feedback.0}</td></tr>
  <tr><td>EMail:</td><td>{$feedback.1}</td></tr>
  <tr><td>API User ID:</td><td>{$feedback.2}</td></tr>
  <tr><td>API Key:</td><td>{$feedback.3}</td></tr>
  <tr><td>Notes:</td><td>{$feedback.4}<br />&nbsp;</td></tr>
  <tr><td colspan="2"><a class="header" href="feedback.php?delete={$feedback.5}">Delete This Entry</a></td></tr>
  </table>
  {if !($smarty.foreach.feed.last)}<hr size="1" />{/if}
  {foreachelse}
  <p>Feedback database is empty.</p>
  {/foreach}
{elseif $result == 1}
  <div class="info">
  Your feedback is received. An administrator will contact you as soon as possible.
  </div>
{else}
  <p>If you can not register to the portal, or you have problems logging in, please fill in the following form. 
  Enter your exact in-game character name as your username.</p>

  <p>If you forgot your password, you can request a new password <a href="newpassword.php">here</a>.</p>

  <form method="post" action="feedback.php">
    <table>
    <tr><td>Username: </td><td><input type="text" name="name" /></td></tr>
    <tr><td>Email: </td><td><input type="text" name="email" /><br />&nbsp;</td></tr>
    <tr><td>API User ID: </td><td><input type="text" name="apiuserid" /></td></tr>
    <tr><td>API Key: </td><td><input type="text" name="apikey" size="60" /><br />&nbsp;</td></tr>
    <tr><td>Notes: </td><td><textarea name="notes" rows="6" cols="40"></textarea><br />&nbsp;</td></tr>
    <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Submit" /></td></tr>
    </table>
  </form>
{/if}

{include file='footer.tpl'}
