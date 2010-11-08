{include file='header.tpl' title=' | Operation History' script='payout.js'}

{if $action == "home"}
  <h3>Operation History</h3>
  {if empty($ops)}
    <p>Operation history is empty.</p>
  {else}
    <p>Displaying last 50 operations.</p>
    
    <table class="data">
    <tr><th>Submitted On</th><th>Op Date</th><th>Leader</th><th>Players</th><th>Status</th><th></th></tr>
    {foreach from=$ops item=op}
      <tr class="{cycle values='altrow1,altrow2'}">
      <td>{$op.Date}</td>
      <td>{$op.OpDate|date_format:"%Y-%m-%d"}</td>
      <td>{$op.Leader}</td>
      <td>{$op.Players|truncate}</td>
      <td>{if $op.Status == 0}New{elseif $op.Status == 1}Resubmitted{elseif $op.Status == 2}Canceled{elseif $op.Status == 3}Rejected{elseif $op.Status == 4}Paid{/if}</td>
      <td>{if $op.CanEdit}<a href="index.php?view={$op.ID}">View</a> | <a href="index.php?edit={$op.ID}">{if $op.Status <= 1}Edit{else}Resubmit{/if}</a> | <a href="index.php?cancel={$op.ID}" onclick="javascript:return confirm('Are you sure you want to delete this operation?');">Delete</a>{else}<a href="index.php?view={$op.ID}">View</a>{/if}</td>
      </tr>
    {/foreach}
    </table>
  {/if}
{elseif $action == "view"}
  <h3>Operation Details</h3>
  <table>
  <tr><th>Op Date: </th><td>{$op.OpDate|date_format:"%Y-%m-%d"}</td></tr>
  <tr><th>Submitted: </th><td>{$op.Date}</td></tr>
  <tr><th>Leader: </th><td>{$op.Leader}</td></tr>
  <tr><th>Status: </th><td>{if $op.Status == 0}New{elseif $op.Status == 1}Resubmitted{elseif $op.Status == 2}Canceled{elseif $op.Status == 3}Rejected{elseif $op.Status == 4}Paid{elseif $op.Status == 5}Deleted{/if}</td></tr>
  {if $op.Status == 3}
  <tr><th>Reject Reason: </th><td>{$op.RejectReason}</td></tr>
  {/if}
  </table>
  
  <h3>Players</h3>

  <table id="placeholder" class="data">
  <tr id="headerrow"><th>Name</th><th>Time In</th><th>Time Out</th></tr>
  {foreach from=$op.Players item=player}
  <tr id="row{$player}" class="{cycle values='altrow1,altrow2'}">
  <td>{$op.PlayerNames.$player}</td><td>{$op.TimeIns.$player}</td><td>{$op.TimeOuts.$player}</td>
  </tr>
  {/foreach}
  </table>
  
  <h3>Submitted Items</h3>
  
  <table class="data">
  {assign var="lastgroup" value=""}
  {foreach from=$items item=item}
    {if $item.1 != $lastgroup}
      <tr><th>
      {foreach from=$ogroupid item=ogvalue}
        {if $item.1 == $ogroupid.$ogvalue}
        {$ogroupName.$ogvalue}
        {/if}
      {/foreach}
      </th><th></th></tr>
      {assign var="lastgroup" value=$item.1}
    {/if}
    <tr class="{cycle values='altrow1,altrow2'}">
    <td>{$item.2}</td><td>{$item.3}</td>
    </tr>
  {/foreach}
  </table>

  {if !empty($op.Notes)}
  <h3>Notes</h3>
  {$op.Notes}
  {/if}
  <br />
  {if $op.CanEdit}<a class="header" href="index.php?edit={$op.ID}">{if $op.Status <= 1}Edit{else}Resubmit{/if}</a>{/if}
{elseif $action == "edit"}
  <h3>Edit Operation</h3>
  <p>If you are submitting a rejected operation, please make sure to make the required corrections.</p>
  <table>
  <tr><th>Op Date: </th><td>{$op.OpDate|date_format:"%Y-%m-%d"}</td></tr>
  <tr><th>Submitted: </th><td>{$op.Date}</td></tr>
  <tr><th>Leader: </th><td>{$op.Leader}</td></tr>
  <tr><th>Status: </th><td>{if $op.Status == 0}New{elseif $op.Status == 1}Resubmitted{elseif $op.Status == 2}Canceled{elseif $op.Status == 3}Rejected{elseif $op.Status == 4}Paid{/if}</td></tr>
  {if $op.Status == 3}
  <tr><th>Reject Reason: </th><td>{$op.RejectReason}</td></tr>
  {/if}
  </table>

  <form method='post' action='index.php?action=editdone' onsubmit='javascript:return CheckAll();' >
  <input type="hidden" name="id" value="{$op.ID}" />

  <h3>Players</h3>
  <p>Add players and enter times. Times should be entered as HH:MM.</p>

  <select name='names' id='names'>
  {foreach from=$names key=id item=name}
    <option value="{$id}">{$name}</option>
  {/foreach}
  </select>&nbsp;
  <a href="#" onclick="javascript:AddPlayer();return false;" >Add Player</a><br />

  <table id="placeholder" class="data">
  <tr id="headerrow"><th>Name</th><th>Time In</th><th>Time Out</th><th>&nbsp;</th></tr>
  {foreach from=$op.Players item=player}
  <tr id="row{$player}" class="{cycle values='altrow1,altrow2'}">
  <td>{$op.PlayerNames.$player}</td><td><input type="text" id="timein{$player}" name="timein{$player}" value="{$op.TimeIns.$player}" size="10" onblur="javascript:CheckTime(this);" /></td><td><input type="text" id="timeout{$player}" name="timeout{$player}" value="{$op.TimeOuts.$player}" size="10" onblur="javascript:CheckTime(this);" /></td><td><a href="#" id="remove{$player}" onclick="javascript:RemovePlayer({$player});return false;">  Remove</a></td>
  </tr>
  {/foreach}
  </table>

  <h3>Submitted Items</h3>
  <p>Enter item quantities.</p>

  <table class="data">
  {assign var="lastgroup" value=""}
  {foreach from=$items item=item}
    {if $item.1 != $lastgroup}
      <tr><th>
      {foreach from=$ogroupid item=ogvalue}
        {if $item.1 == $ogroupid.$ogvalue}
        {$ogroupName.$ogvalue}
        {/if}
      {/foreach}
      </th><th></th></tr>
      {assign var="lastgroup" value=$item.1}
    {/if}
    {if $lastgroup == 4}
    <tr class="{cycle values='altrow1,altrow1,altrow1,altrow2,altrow2,altrow2'}">
    {else}
    <tr class="{cycle values='altrow1,altrow2'}">
    {/if}
    <td>{$item.2}</td><td><input type="text" size="4" name="item{$item.0}" value="{$item.3}" onblur="javascript:CheckInteger(this);" /></td>
    </tr>
  {/foreach}
  </table>

  <h3>Notes</h3>
  <textarea name="notes" rows="4" cols="40">{$op.Notes}</textarea>
  <br /><br />

  <input type='submit' name='submit' value='Done' />
  </form>
{/if}

{include file='footer.tpl'}