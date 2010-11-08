{include file='header.tpl' title=' | Login'}

{if $IGB}
<div>
<form method="post" action="login.php">
<input type="hidden" name="username" value="{$smarty.server.HTTP_EVE_CHARNAME}" />
<table>
<tr><td>Username: </td><td>{$smarty.server.HTTP_EVE_CHARNAME}</td></tr>
<tr><td>Password: </td><td><input type="password" name="password" size="10" /></td></tr>
</table>
<br />
<input type="submit" name="submit" value="Login" />
</form>
{if !empty($smarty.post.password)}
  <div class="error">
  <h1>Incorrect Username/Password Combination</h1>

  <p>
  Usernames and passwords are case sensitive. Please check your CAPS lock key.<br />
  If you do not have a password, click <a href="shellexec:http://{$smarty.server.HTTP_HOST}/php/register.php">here</a> and follow the directions.<br />
  If you forgot your password, click <a href="shellexec:http://{$smarty.server.HTTP_HOST}/php/newpassword.php">here</a> to request a new password.
  </p>
  </div>
{/if}
</div>
{else}
  <div class="error">
  <h1>Incorrect Username/Password Combination</h1>

  <p>Usernames and passwords are case sensitive. Please check your CAPS lock key.</p>
  <ul>
    <li>If you do not have a password, click <a href="register.php">here</a> and follow the directions.</li>
    <li>If you forgot your password, click <a href="newpassword.php">here</a> to request a new password.</li>
  </ul>
  </div>
{/if}

{include file='footer.tpl'}
