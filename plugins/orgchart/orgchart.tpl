{include file='header.tpl' title=' | Corporate Organization Chart'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="index.php">Corporate Organization Chart</a>
<a class="header" href="index.php?action=corp">In-Game Corporation Information</a>
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "corp"}
  {if empty($result)}
    <div class="error">{$error}</div>
  {else}
    <table>
    {foreach from=$result key=key item=value}
      {if empty($value)}
      <tr><th>&nbsp;</th><td>&nbsp;</td></tr>
      {else}
      <tr><th>{$key}:&nbsp;</th><td>{$value}</td></tr>
      {/if}
    {/foreach}
    </table>
  {/if}
{else}
<div style='overflow: auto;'>
  <!-- CEO -->
  {if $users.0->Title == "CEO"}
  <div style="text-align: center;">
    <a href="{$baseurl}php/profile.php?user={$users.0->ID}"><img class="portrait" src="{$core->PortraitFromCharID($users.0->CharID, 256)}" width="160" height="160" /></a>
    <br />
    <span>{$users.0->Name}&nbsp;-&nbsp;CEO</span>
  </div>
  {/if}

  <!-- Directors -->
  <div style="text-align: center;">
  <table border="0" cellpadding="0" cellspacing="0">
  <tr>
  {foreach from=$users item=user}
    {if $user->IsDirector()}
      <td class="center">
      <a href="{$baseurl}php/profile.php?user={$user->ID}"><img class="portrait" src="{$core->PortraitFromCharID($user->CharID, 256)}" width="128" height="128" /></a>
      <br />
      <span>{$user->Name}<br />{$user->Title|default:'&nbsp;'}</span>
      </td>
    {/if}
  {/foreach}
  </tr>
  </table>
  </div>
    
  <!--Managers -->
  <div style="text-align: center;">
  <table border="0" cellpadding="0" cellspacing="0">
  <tr>
  {foreach from=$users item=user}
    {if $user->IsManager()}
      <td class="center">
      <a href="{$baseurl}php/profile.php?user={$user->ID}"><img class="portrait" src="{$core->PortraitFromCharID($user->CharID, 64)}" width="64" height="64" /></a>
      <br />
      <span>{$user->Name}<br />{$user->Title|default:'&nbsp;'}</span>
      </td>
    {/if}
  {/foreach}
  </tr>
  </table>
  </div>
</div>
{/if}

{include file='footer.tpl'}
