{include file="header.tpl" layout=3 title=" | Home" script="home.js"}

{if $IGB}
  {if !empty($calendar)}
    {foreach from=$calendar item=event}
      <div class="newsheader">
      {$event->Title}
      <span class="subtitle">{$event->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($item->Date)}&nbsp;&nbsp;(ET {$core->GMTFormat($item->Date)})</span>
      </div>
      <div class="newsbody">{$event->Text}</div>
    {/foreach}
  {/if}

  {if !empty($calendar) && !empty($news)}<p>&nbsp;</p>{/if}
  
  {if !empty($news)}
    {foreach from=$news item=newsitem}
      <div class="newsheader">
      {$newsitem->Title}
      <span class="subtitle">{$newsitem->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($newsitem->Date)}</span>
      </div>
      <div class="newsbody">{$newsitem->Text}</div>
    {/foreach}
  {/if}
{else}
  <div class="welcome">
  <h2>{$welcome->Title}</h2>
  {$welcome->Text}
  </div>

  {if !empty($news)}
    {foreach from=$news item=newsitem}
      {if $newsitem->Date > $user->LastLogin}
      <div class="recentnews">
      {else}
      <div class="news">
      {/if}
      <h2>{$newsitem->Title}</h2>
      <h3>{$newsitem->AuthorName}&nbsp;on&nbsp;{$core->GMTToLocal($newsitem->Date)}</h3>
      {$newsitem->Text}
      </div>
    {/foreach}
  {/if}
  <p>&nbsp;</p>
  {if $gamenews || $devblogs || $rpnews}
    <div class="evenews">

    {if $gamenews}
      <h2>News From Eve Online</h2>
      <div class='throbber' id='evenews_placeholder'>&nbsp;</div>
    {/if}

    {if $devblogs}
      <h2>Dev Blogs</h2>
      <div class='throbber' id='devblogs_placeholder'>&nbsp;</div>
    {/if}

    {if $rpnews}
      <h2>Role-Playing News</h2>
      <div class='throbber' id='rpnews_placeholder'>&nbsp;</div>
    {/if}
    
    </div>
  {/if}
{/if}

{if !($IGB)}
<!-- AJAX magic -->
<script type="text/javascript">
ajaxTraining({$showtranqstatus}, {$showtraining}, {$showevenews}, {$showdevblogs}, {$showrpnews});
</script>
{/if}

{include file='footer.tpl' layout=3}
