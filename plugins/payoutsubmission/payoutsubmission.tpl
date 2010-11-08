{include file='header.tpl' title=' | Submit Operation' script='payout.js'}

{if $action == "home"}
  <h3>Submit Operation for Payout (Step 1/3)</h3>

  <p>Select the items you want to submit.</p>

  <form method='post' action='index.php?action=times'>

  {foreach from=$ogroupid item=ogvalue}
    <input type="checkbox" name="group{$ogroupGroupID.$ogvalue}" id="group{$ogroupGroupID.$ogvalue}"{if $ogroupCheckbox.$ogvalue == 1} checked="checked"{/if} /><label for="group{$ogroupGroupID.$ogvalue}">{$ogroupName.$ogvalue}</label>&nbsp;&nbsp;<span class="info">{$ogroupSubtext.$ogvalue}</span><br />
  {/foreach}

    <br />
    <input type='submit' name='submit' value='Next &gt;&gt;' />
  </form>
{elseif $action == "times"}
  <h3>Submit Operation for Payout (Step 2/3)</h3>
  
  <form method='post' action='index.php?action=items' onsubmit='javascript:return CheckAllTimes();' >
    {foreach from=$ogroupid item=ogvalue}
      {assign var='grouptemp' value=$ogroupGroupID.$ogvalue}
      <input type="hidden" name="group{$ogroupGroupID.$ogvalue}" value="{$groupnumber.$grouptemp}" />
    {/foreach}

    <p>Modify the operation date if you are submitting a past operation.</p>
    Operation Date: <input type="text" name="opdate" value="{$opdate}" /><br /><br />
    
    <p>Add players and enter times. Times should be entered as HH:MM.</p>
    <select name='names' id='names'>
    {foreach from=$names key=id item=name}
      <option value="{$id}">{$name}</option>
    {/foreach}
    </select>&nbsp;
    <input type='submit' name='submit' value='Add Player' />
    
    <input type='hidden' name='count' value='{$count}' />
    <table class="data">
    {if $count > 0}
    <tr><th></th><th>Name</th><th>Time In</th><th>Time Out</th><th>&nbsp;</th></tr>
    {assign var='i' value='1'}
    {foreach from=$players item=player}
      <tr><td>{$i}.&nbsp;</td><td><input type="hidden" name="playerid{$i}" value="{$player.0}" />{$player.1}</td><td><input type="text" id="timein{$i}" name="timein{$i}" value="{$player.2}" size="10" /></td><td><input type="text" id="timeout{$i}" name="timeout{$i}" value="{$player.3}" size="10" /></td><td><input type="submit" name="submit" value="Remove Player {$i}" /></td></tr>
      {assign var='i' value=`$i+1`}
    {/foreach}
    {/if}
    </table>
    
    <input type='submit' name='submit' value='Next &gt;&gt;' />
  </form>
{elseif $action == "items"}
  <h3>Submit Operation for Payout (Step 3/3)</h3>
  <form method='post' action='index.php?action=done' onsubmit='javascript:return CheckAllItems();' >
    {foreach from=$ogroupid item=ogvalue}
      {assign var='grouptemp' value=$ogroupGroupID.$ogvalue}
      <input type="hidden" name="group{$ogroupGroupID.$ogvalue}" value="{$groupnumber.$grouptemp}" />
    {/foreach}

    <input type="hidden" name="opdate" value="{$opdate}" />
    <input type='hidden' name='count' value='{$count}' />
    {assign var='i' value='1'}
    {foreach from=$players item=player}
      <input type="hidden" name="playerid{$i}" value="{$player.0}" />
      <input type="hidden" name="timein{$i}" value="{$player.2}" />
      <input type="hidden" name="timeout{$i}" value="{$player.3}" />
      {assign var='i' value=`$i+1`}
    {/foreach}

    <table class="data">
    {assign var="lastgroup" value=""}
    {foreach from=$items item=item}
      {if $item.1 != $lastgroup}
        <tr><th>
       {foreach from=$ogroupid item=ogvalue}
        {if $item.1 == $ogroupGroupID.$ogvalue}
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
      <td>{$item.2}</td><td><input type="text" size="4" name="item{$item.0}" value="" onblur="javascript:CheckInteger(this);" /></td>
      </tr>
    {/foreach}
    </table>
    
    Notes:<br />
    <textarea name="notes" rows="4" cols="40"></textarea>
    <br /><br />
    <input type='submit' name='submit' value='Done' />
  </form>
{elseif $action == "done"}
  <h3>Submit Operation for Payout</h3>
  
  <div class="info">Operation successfully submitted for payout.</div>
  
  <p>Estimated value of this operation is {$opvalue} ISK <u>including</u> corporation cut. 
  Note that this is subject to change as the actual payouts will be recalculated based on unit prices on payment day.</p>
  
  <p>
  <b>Date: </b>{$opdate}<br />
  <b>Leader: </b>{$user->Name}
  </p>
  
  <table class="data">
  <tr><th>Name</th><th>Time In</th><th>Time Out</th></tr>
  {foreach from=$players item=player}
    <tr class="{cycle values='altrow1,altrow2'}">
    <td>{$player.1}</td><td>{$player.2}</td><td>{$player.3}</td>
    </tr>
  {/foreach}
  </table>

  <table class="data">
  {assign var="lastgroup" value=""}
  {foreach from=$items item=item}
    {if $item.1 != $lastgroup}
      <tr><th>
      {foreach from=$ogroupid item=ogvalue}
        {if $item.1 == $ogroupGroupID.$ogvalue}
        {$ogroupName.$ogvalue}
        {/if}
      {/foreach}
      </th><th></th></tr>
      {assign var="lastgroup" value=$item.1}
    {/if}
    <tr class="{cycle values='altrow1,altrow2'}">
    <td>{$item.0}</td><td>{$item.2}</td>
    </tr>
  {/foreach}
  </table>

  <p><b>Notes: </b>{$notes}</p>
{/if}

{if $result == 1}
<div class="error">Operation date should be entered as yyyy-mm-dd.</div>
{elseif $result == 2}
<div class="error">Times should be entered as HH:MM.</div>
{/if}

{include file='footer.tpl'}