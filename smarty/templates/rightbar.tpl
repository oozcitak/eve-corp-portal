<div class='rightbar'>  
  {if ($showtranqstatus == 1) || ($showtraining == 1)}
    <div class='rbcontent'>
      <div class='rbcontentheader'>Server Status</div>
	{if $showtranqstatus == 1}
          <div class='throbber' id='server_placeholder'>&nbsp;</div>
        {/if}      
        {if $showtraining == 1}
          <div class='throbber' id='sit_placeholder'>&nbsp;</div>
        {/if}
    </div>
    <br />
  {/if}
  {if $user->AccessRight()>1}
    {if $user->IsOOP}
      <div class='rbcontent'>
        <div class='rbcontentheader'>My RL Status</div>
        <p style="font-weight: bold; font-size: 16pt; text-transform: uppercase;">Out Of Pod</p>
        <p><span class="info">Until {$user->OOPUntil|date_format:"%B %e, %Y"}</span></p>
      </div>
      <br />
    {/if}
    <div class='rbcontent'>
      <div class='rbcontentheader'>Calendar</div>
      {if !empty($calendar)}
        {foreach from=$calendar item=item}
          <p><a href="{$baseurl}php/calendar.php#item{$item->ID}">{$item->Title}</a><br />{$core->GMTToLocal($item->Date)}&nbsp;&nbsp;(ET {$core->GMTFormat($item->Date)})</p>
        {/foreach}
      {else}
        <p>There are no upcoming events.</p>
          {/if}
    </div>
    <br />
    <div class='rbcontent'>
      <div class='rbcontentheader'>News</div>
      {if !empty($shortnews)}
        {$shortnews}
      {else}
        <p>There are no recent news.</p>
      {/if}
    </div>
    <br />
    {if !empty($hottopics)}
      <div class='rbcontent'>
        <div class='rbcontentheader'>Recent Forum Posts</div>
        {foreach name=hot from=$hottopics item=topic}
          {if $smarty.foreach.hot.index<=4}
          <p>
          <span {if $topic->ReadAccess >= 4}class="director"{elseif $topic->ReadAccess >= 3}class="manager"{/if}>
          <a {if $topic->IsUnread}style="color: #FFFF66;"{/if} href="{$baseurl}php/forums.php?topic={$topic->ID}&amp;page={$topic->PageCount}#item{$topic->LastReplyID}" title="{$topic->CategoryName}">{$topic->Title}</a><br />
          {$topic->LastPosterName}&nbsp;-&nbsp;{$topic->TimeElapsed} ago          </span>
          </p>
          {/if}
        {/foreach}
      </div>
      <br />
    {/if}
    {if !empty($pluginfeeds)}
      <div class='rbcontent'>
        <div class='rbcontentheader'>Info Box</div>
        {foreach from=$pluginfeeds item=feed}
          <p>{$feed.Title} ({$core->GMTToLocal($feed.Date)})</p>
        {/foreach}
        </div>
      <br />
    {/if}
    {if $onlinechars}
      <div class='rbcontent'>
        <div class='rbcontentheader'>Online Members</div>
        {$onlinechars}
      </div>
      <br />
    {/if}
    <div class="rbcontent">
      <div class='rbcontentheader'>Shout Box</div>
      {foreach from=$shouts item=shout}
        <p><b>{$shout->AuthorName}:</b>&nbsp;{$shout->Text}</p>
      {/foreach}
      <br />
      <div><form method="get" action="{$baseurl}php/home.php"><input type="text" name="shout" /><input type="submit" name="submit" value="Shout" /></form></div>
    </div>
    <br />
    <div class="rbcontent">
    <div class='rbcontentheader'>Visitor Map</div>
    <p style='text-align: center;'>
    <a href="http://www.ipligence.com/webmaps/s/?u=c157c30e41b105d6f17029773079876e&color=1&a=month"><img src="http://www.ipligence.com/webmaps/m/?u=c157c30e41b105d6f17029773079876e&size=verysmall&color=1&a=month" alt="ip-location" border="1"></a>
    </p>
    </div>
    <br />
  {/if}
</div>
