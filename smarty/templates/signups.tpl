{include file='header.tpl' title=' | Sign-Ups'}

  <h3>Sign-Ups</h3>
  {if empty($calendar)}
    <p>You have not signed up for any events.</p>
  {else}
    {foreach from=$calendar item=item}
      <a name="item{$item->ID}"></a><h3>{$item->Title}&nbsp;-&nbsp;{$core->GMTToLocal($item->Date)}&nbsp;({$core->GMTFormat($item->Date)} EVE Time)<span class="info">&nbsp;-&nbsp;by&nbsp;{$item->AuthorName}</span></h3>
      {$item->Text}
      <br/>
      {if empty($item->Signups)}
        <span class="info">No characters have signed up for this event.</span>
      {else}
        <span class="info"><b>Sign-ups:&nbsp;</b>
        {foreach name=signups from=$item->Signups item=member}
          {$member}
          {if !($smarty.foreach.signups.last)},&nbsp;{/if}
        {/foreach}
        </span>
      {/if}
    {/foreach}
  {/if}
  
  {if !empty($subscriptions)}
    <h3>Forum Subscriptions</h3>
    <table class="data">
    <tr><th>Topic</th><th>Subscribed On</th><th>&nbsp;</th></tr>
    {foreach from=$subscriptions item=item}
      <tr class="{cycle values='altrow1,altrow2'}">
      <td><a href="forums.php?topic={$item.TopicID}">{$item.Title}</a></td>
      <td>{$core->GMTToLocal($item.Date)}</td>
      <td><a href="signups.php?unsubscribe={$item.TopicID}">Unsubscribe</a></td>
      </tr>
    {/foreach}
    </table>
  {/if}

{include file='footer.tpl'}
