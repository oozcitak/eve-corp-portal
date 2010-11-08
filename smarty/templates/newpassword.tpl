{include file='header.tpl' title=' | Request New Password'}

{if $result != 8}
<form method="post" action="newpassword.php">
<p>Please fill the following form to get a new password. To get your API key <a href="http://myeve.eve-online.com/api/default.asp">click here</a>.</p>
<table>
<tr><td>Username: </td><td><input type="text" name="username" value="{$username}" /></td></tr>
<tr><td>API User ID: </td><td><input type="text" name="apiuserid" value="{$apiuserid}" /></td></tr>
<tr><td>API Key: </td><td><input type="text" name="apikey" size="60" value="{$apikey}" /><br />&nbsp;</td></tr>
<tr><td>New Password: </td><td><input type="password" name="password1" /><br /></td></tr>
<tr><td>Confirm New Password: </td><td><input type="password" name="password2" /><br />&nbsp;</td></tr>
<tr><td colspan="2"><input type="submit" name="submit" value="Submit" /></td></tr>
</table>
</form>
{/if}

{if $result == 1}  
<div class="error"><p>Please enter your API User ID and API Key.</p></div>
{elseif $result == 2}
<div class="error"><p>Could not connect to the API server. Please try again later.</p></div>
{elseif $result == 3}
<div class="error"><p>No corporation members were found in the given account. Please check your API key.</p></div>
{elseif $result == 4}
<div class="error"><p>Passwords cannot be blank. Please retype your new password.</p></div>
{elseif $result == 5}
<div class="error"><p>The passwords you typed do not match. Please retype the new password in BOTH boxes.</p></div>
{elseif $result == 6}
<div class="error"><p>No character exists with the given name. Please check your username and API key.</p></div>
{elseif $result == 7}
<div class="error"><p>Please enter your username.</p></div>
{elseif $result == 8}
<div class="info"><p>Your password is changed. You may now login with your username and password.</p></div>
{/if}

{include file='footer.tpl'}
