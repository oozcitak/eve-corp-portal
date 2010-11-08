{include file='header.tpl' title=' | User Registration'}

<form method="post" action="register.php">
<input type="hidden" name="step" value="{$step+1}" />
<input type="hidden" name="apiuserid" value="{$apiuserid}" />
<input type="hidden" name="apikey" value="{$apikey}" />
<input type="hidden" name="charid" value="{$charid}" />
<input type="hidden" name="charname" value="{$charname}" />
<input type="hidden" name="corpname" value="{$corpname}" />
<input type="hidden" name="corpid" value="{$corpid}" />
<input type="hidden" name="corpticker" value="{$corpticker}" />

{if $step == 1}
  <p>Please enter your API key. Managers and directors need to enter their full access API keys. To get your API key <a href="http://myeve.eve-online.com/api/default.asp">click here</a>.</p>
  <table>
  <tr><td>API User ID: </td><td><input type="text" name="apiuserid" value="{$apiuserid}" /></td></tr>
  <tr><td>API Key: </td><td><input type="text" name="apikey" size="60" value="{$apikey}" /><br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Next >>" /></td></tr>
  </table>
{elseif $step == 2}
  <table>
  {foreach name=chars from=$characters item=char}
  <tr>
    <td style="vertical-align: middle">
      <input type="hidden" name="name_{$char.CharacterID}" value="{$char.Name}" />
      <input type="hidden" name="corp_{$char.CharacterID}" value="{$char.CorporationName}" />
      <input type="hidden" name="corpid_{$char.CharacterID}" value="{$char.CorporationID}" />
      <input type="hidden" name="corpticker_{$char.CharacterID}" value="{$char.CorporationTicker}" />
      <input type="radio" name="char" id="char_{$char.CharacterID}" value="{$char.CharacterID}" {if $smarty.foreach.chars.first} checked="checked" {/if}/>
    </td>
    <td style="vertical-align: middle"><label for="char_{$char.CharacterID}"><img class="portrait" src="{$core->PortraitFromCharID($char.CharacterID, 64)}" width="64" height="64" /></label></td>
    <td style="vertical-align: middle"><label for="char_{$char.CharacterID}">{$char.Name} of {$char.CorporationName} ({$char.CorporationTicker})</label></td>
  </tr>
  {/foreach}
  <tr><td colspan="3"><br />&nbsp;</td></tr>
  <tr><td colspan="3"><input type="submit" name="submit" value="Next >>" /></td></tr>
  </table>
{elseif $step == 3}
  <p>Please select your password.</p>
  <table>
  <tr><td>Password: </td><td><input type="password" name="password1" /></td></tr>
  <tr><td>Confirm Password: </td><td><input type="password" name="password2" /><br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Finish" /></td></tr>
  </table>
{elseif $step == 4}
  <div class="info"><p>Your account is successfully registered. You may now login with your username and password.</p></div>
{/if}

</form>

{if $result == 1}  
<div class="error"><p>Please enter your API User ID and API Key.</p></div>
{elseif $result == 2}
<div class="error"><p>Could not connect to the API server. Please try again later.</p></div>
{elseif $result == 3}
<div class="error"><p>No corporation members were found in the given account. Please check your API key.</p></div>
{elseif $result == 6}
<div class="error"><p>Passwords cannot be blank. Please retype your new password.</p></div>
{elseif $result == 7}
<div class="error"><p>The passwords you typed do not match. Please retype the new password in BOTH boxes.</p></div>
{elseif $result == 8}
<div class="error"><p>This character is already registered.</p></div>
{/if}

{include file='footer.tpl'}
