{include file='header.tpl' title=' | Operation History' script='payout.js'}

{if $action == "home"}
  <h3>Payout Management</h3>
  {if empty($ops)}
    <p>There are no operations awaiting payment.</p>
  {else}
    <p>There {if $opcount == 1}is one operation{else}are {$opcount} operations{/if} awaiting payment with a total value of {$total} ISK.</p>
    
    <form method="post" action="index.php?action=payout" onsubmit="javascript:return CheckAll();">
    <table>
    {foreach from=$ops item=op}
      {capture name=popuptext assign=popuptext}
        <b>Submitted: </b>{$op.Date}<br />
        <b>Op Date: </b>{$op.OpDate|date_format:"%Y-%m-%d"}
        <hr size='1' />
        <b>Leader: </b>{$op.Leader}<br />
        <b>Players: </b>{$op.Players}
        <hr size='1' />
        <b>Status: </b>{if $op.Status == 0}New{elseif $op.Status == 1}Resubmitted{elseif $op.Status == 2}Canceled{elseif $op.Status == 3}Rejected{elseif $op.Status == 4}Paid{/if}<br />
        <b>Value: </b>{$op.Value} ISK
        {if !empty($op.Notes)}
        <hr size='1' />
        <b>Notes: </b><br />{$op.Notes}
        {/if}
      {/capture}
      <tr class="{cycle values='altrow1,altrow2'}">
      <td><input type="checkbox" name="op{$op.ID}" id="op{$op.ID}" /></td>
      <td {popup text=$popuptext}>
      <b>Submitted: </b>{$op.Date}&nbsp;&nbsp;<a href="../payoutview/index.php?edit={$op.ID}">Edit</a><br />
      <b>Leader: </b>{$op.Leader}<br />
      <b>Value: </b>{$op.Value} ISK
      {if !empty($op.Notes)}<br /><b>Notes: </b><br />{$op.Notes}{/if}
      </td>
      </tr>
    {/foreach}
    </table>
    <p>
    <a href="#" onclick="javascript:SelectAll();return false;">Select All</a>
    | 
    <a href="#" onclick="javascript:SelectNone();return false;">Select None</a>
    </p>
    
    <div id="buttons">
    Corp Cut: <input type="text" name="corpcut" id="corpcut" value="40" size="4" onblur="javascript:CheckInteger(this);" />%&nbsp;&nbsp;
    <input type="submit" name="submit" value="Pay Selected Operations" />&nbsp;
    <input type="submit" name="submit" onclick="javascript:ShowReject();return false;" value="Reject Selected Operations" />
    </div>
    
    <div id="reject" style="display: none;">
      Reject Reason:
      <br />
      <textarea name="reject" rows="4" cols="40"></textarea>
      <br /><br />
      <input type="submit" name="submit" value="Reject" />&nbsp;<input type="submit" name="submit" onclick="javascript:HideReject();return false;" value="Cancel" />
      </div>
    </form>
  {/if}
{elseif $action == "payout"}
  <h3>Payout Parameters</h3>
  <p>Paying {if $opcount == 1}one operation{else}{$opcount} operations{/if}. Review payouts and click confirm <u>after</u> you make payments.</p>
  <p>
  Refining Skill Level: <b>{$refining}</b><br />
  Refinery Efficiency Skill Level: <b>{$refinery_efficiency}</b><br />
  Ore Processing Skill Levels: <b>{$ore_skills}</b><br />
  Ice Processing Skill Level: <b>{$ice_skill}</b><br />
  Refining Equipment: <b>{$refining_equipment}</b>
  </p>
  <p>Corp Cut: <b>{$corpcut}%</b></p>
  
  {if !empty($indexdate)}
    {if $indextime == 0}
      <p>Unit prices were last updated manually on <b>{$indexdate}</b>.</p>
    {else}
      <p>Unit prices were last updated from QTC-{$indextime} on <b>{$indexdate}</b>.</p>
    {/if}
  {/if}

  <h3>Player Totals</h3>
  {foreach from=$payouts item=payout}
    {$payout.1}: <b>{$payout.2} ISK</b><br />
  {/foreach}
  
  <h3>Summary</h3>
  Players: <b>{$playertotal} ISK</b><br />
  Corp: <b>{$corptotal} ISK</b><br />
  TOTAL: <b>{$grandtotal} ISK</b><br />
  
  <form method="post" action="index.php?action=payoutdone">
  {foreach from=$opids item=opid}
    <input type="hidden" name="op{$opid}" value="{$opid}" />
  {/foreach}
  <br />  
  <input type="submit" name="submit" value="Confirm" />
  </form>
{/if}

{include file='footer.tpl'}