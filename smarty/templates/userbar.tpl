{if !($IGB)}
  <div class='userbar'>
    <ul class='primary'>
      <li><a id="home" href='{$baseurl}php/home.php'><span>Home</span>&nbsp;</a></li>
      <li><a id="policies" href='{$baseurl}php/policies.php'><span>Policies</span>&nbsp;</a></li>
      <li><a id="forums" href='{$baseurl}php/forums.php'><span>Forums</span>&nbsp;</a></li>
      {if !$user->IsGuest}
        <li><a id="news" href='{$baseurl}php/news.php'><span>News</span>&nbsp;</a></li>
        <li><a id="calendar" href='{$baseurl}php/calendar.php'><span>Calendar</span>&nbsp;</a></li>
        {if !$user->IsAlly}
          <li><a id="quickinfo" href='{$baseurl}php/quickinfo.php'><span>Quick Info</span>&nbsp;</a></li>
        {/if}
      {/if}
      {if !empty($killboardurl) }<li><a id="killboard" href='{$killboardurl}'><span>Killboard</span>&nbsp;</a></li>{/if}
      {if !empty($allianceurl) }<li><a id="alliance" target="_blank" href='{$allianceurl}'><span>Alliance</span>&nbsp;</a></li>{/if}
    </ul>
      {if $user->Name == "Guest"}
        <form method='post' action='{$baseurl}php/login.php'>
        <ul class='secondary'>
        <li><input id="txtusername" type='text' size='12' name='username' /></li>
        <li><input id="txtpassword" type='password' size='12' name='password' /></li>
        <li><input type='submit' name='submit' value='Sign In' /></li>
        <li><a href='{$baseurl}php/register.php'>Register</a></li>
        <li><a href='{$baseurl}php/newpassword.php'>Forgot Password?</a></li>
        </ul>
        </form>
      {else}
        <ul class='secondary'>
        {if $unreadmails != 0}
        <li><a style="color: #ff6;" href='{$baseurl}php/mail.php'>Inbox ({$unreadmails})</a></li>
        {else}
        <li><a href='{$baseurl}php/mail.php'>Inbox</a></li>
        {/if}
        <li><a href='{$baseurl}php/notepad.php'>Notepad</a></li>
        <li><a href='{$baseurl}php/signups.php'>Sign-Ups</a></li>
        <li>|</li>
        <li>{$user->Name}</li>
        <li><a href='{$baseurl}php/profile.php'>Profile</a></li>
        <li><a href='{$baseurl}php/logout.php'>Sign Out</a></li>
        </ul>
      {/if}
  </div>
{elseif $user->Name != "Guest"}
<div class='homelinks'>
<a class='corelink' href='{$baseurl}php/home.php'>Home</a> | 
<a class='corelink' href='{$baseurl}php/quickinfo.php'>Quick Info</a> | 
{if $unreadmails != 0}
<a class='corelink' href='{$baseurl}php/mail.php'>Mail ({$unreadmails})</a> | 
{else}
<a class='corelink' href='{$baseurl}php/mail.php'>Mail</a> | 
{/if}
<a class='corelink' href='{$baseurl}php/notepad.php'>Notepad</a><br />
{assign var='first' value='1'}
{foreach name=plugins from=$plugins item=plugin}
{if $plugin->ShowIGB}
{if $first == '1'}{assign var='first' value='0'}{else}&nbsp;|&nbsp;{/if}
<a class='pluginlink' href='{$plugin->URL}'>{$plugin->Title}</a>
{/if}
{/foreach}
</div>
<p>&nbsp;</p>
{/if}
