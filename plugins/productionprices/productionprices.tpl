{include file='header.tpl' title=' | Production Management' script='production.js'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="index.php">Show All</a>
<a class="header" href="index.php?action=homeships">Show Ships</a>
<a class="header" href="index.php?action=homerigs">Show Rigs</a>
<a class="adminheader" href="index.php?action=addship">Add Ship</a>
<a class="adminheader" href="index.php?action=addrig">Add Rig</a>
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "home" || $action == "homeships" || $action == "homerigs"}
  {if empty($shipprices) && empty($rigprices)}
    <h3>Item Prices</h3>
    <p>There are no production items in the database. Click one of the 'Add' buttons above to add a new production item.</p>
  {else}
    <form method="post" action="index.php?action=changebypercent">
    {if !empty($shipprices)}
    <h3>Ship Prices</h3>
    <table class='data'>
    {assign var='lastgroup' value=''}
    {foreach from=$shipprices item=dbprice}
      {if $lastgroup != $dbprice.GroupName}
        {assign var='lastgroup' value=$dbprice.GroupName}
        <tr><th></th><th>{$lastgroup}</th><th>Price</th><th>Alliance Price</th><th>Based On BreakDown</th><th></th></tr>
      {/if}
      <tr>
        <td><input type="checkbox" name="item{$dbprice.ID}" {if $action == "homeships"}CHECKED{/if}/></td>
        <td>{$dbprice.Name} ({$dbprice.Race})</td>
        <td>{$dbprice.Price} ISK{if $dbprice.Price == 0} (FREE){/if}</td>
        <td>{if $dbprice.AlliancePrice == 0}N/A{else}{$dbprice.AlliancePrice} ISK{/if}</td>
        {assign var='eveindex' value=$dbprice.EveTypeID}
        <td>{$eveprices.$eveindex} ISK</td>
        <td><a href="index.php?edit={$dbprice.ID}">Edit</a> | <a onclick="javascript:return confirm('Are you sure you want to delete this item?');" href="index.php?delete={$dbprice.ID}">Delete</a></td>
      </tr>
    {/foreach}
    </table>
    {/if}
    {if !empty($rigprices)}
    <h3>Rig Prices</h3>
    <table class='data'>
    {assign var='lastgroup' value=''}
    {foreach from=$rigprices item=dbprice}
      {if $lastgroup != $dbprice.GroupName}
        {assign var='lastgroup' value=$dbprice.GroupName}
        <tr><th></th><th>{$lastgroup}</th><th>Price</th><th>Alliance Price</th><th>Based On BreakDown</th><th></th></tr>
      {/if}
      <tr>
        <td><input type="checkbox" name="item{$dbprice.ID}" {if $action == "homerigs"}CHECKED{/if}/></td>
        <td>{$dbprice.Name}</td>
        <td>{$dbprice.Price} ISK{if $dbprice.Price == 0} (FREE){/if}</td>
        <td>{if $dbprice.AlliancePrice == 0}N/A{else}{$dbprice.AlliancePrice} ISK{/if}</td>
        {assign var='eveindex' value=$dbprice.EveTypeID}
        <td>{$eveprices.$eveindex} ISK</td>
        <td><a href="index.php?edit={$dbprice.ID}">Edit</a> | <a onclick="javascript:return confirm('Are you sure you want to delete this item?');" href="index.php?delete={$dbprice.ID}">Delete</a></td>
      </tr>
    {/foreach}
    </table>
    {/if}

  <table class='data'>
  <th>Markup By Percent</th><th>%</th></tr>
  <tr><td>Corp Price</td><td><input size="3" type="text" name="input_corppercent"></td><tr>
  <td>Alliance Price</td><td><input size="3" type="text" name="input_allypercent"></td><tr>
  <td><input type="submit" value="Adjust Prices"></td><tr>
  {/if}
{elseif $action == "addship" || $action == "addrig"}
  <h3>Add Item</h3>
  <p>Setting "Alliance Price" to 0 removes the item from the alliance ship orders page.</p>
  <form method="post" action="index.php?action={$action}done" onsubmit="javascript:return CheckPrice();" >
  <table>
  <tr><td>Item: </td><td><select name='item' id='item' onchange='javascript:ajaxPrice(this);'>
  {assign var='lastgroup' value=''}
  {foreach from=$items item=item}
    {if $lastgroup != $item.GroupName}
      {if !empty($lastgroup)}
      </optgroup>
      {/if}
      <optgroup label="{$item.GroupName}">
      {assign var='lastgroup' value=$item.GroupName}  
    {/if}
    <option value="{$item.EveTypeID}">{$item.Name}{if !empty($item.Race)} ({$item.Race}){/if}</option>    
  {/foreach}
  </optgroup>
  </select>
  <br />&nbsp;</td></tr>
  <tr><td>Price: </td><td><input type="text" name="price" id="price" value="0" size="10" onblur="javascript:CheckNumber(this);" /> ISK</td></tr>
  <tr><td>Alliance Price: </td><td><input type="text" name="allyprice" id="allyprice" value="0" size="10" onblur="javascript:CheckNumber(this);" /> ISK<br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Add" /></td></tr>
  </table>
  </form>
  <div class='throbber' id='price_placeholder'>&nbsp;</div>
  <script type="text/javascript">
  ajaxPrice(ObjFromID('item'));
  </script>
{elseif $action == "edit"}
  <h3>{$name} ({$groupname})</h3>
  <p>Setting "Alliance Price" to 0 removes the item from the alliance ship orders page.</p>
  <form method="post" action="index.php?action=editdone" onsubmit="javascript:return CheckPrice();" >
  <input type="hidden" name="id" value="{$id}" />
  <table>
  <tr><td>Price: </td><td><input type="text" name="price" id="price" value="{$price}" size="10" onblur="javascript:CheckNumber(this);" /> ISK</td></tr>
  <tr><td>Alliance Price: </td><td><input type="text" name="allyprice" id="allyprice" value="{$allyprice}" size="10" onblur="javascript:CheckNumber(this);" /> ISK<br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Save" /></td></tr>
  </table>
  </form>
	<br />
	<table class="data">
	<tr><th colspan="2">Breakdown</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>
	{foreach from=$eveprices item=item}
	<tr>
	<td><img src="{$core->IconFromTypeID($item.Icon, 32)}" width="32" height="32" /></td>
	<td style="vertical-align: middle">{$item.Name}</td>
	<td style="vertical-align: middle; text-align: right;">{$item.Quantity}</td>
	<td style="vertical-align: middle; text-align: right;">{if $item.UnitPrice == 0}???{else}{$item.UnitPrice} ISK{/if}</td>
	<td style="vertical-align: middle; text-align: right;">{if $item.UnitPrice == 0}???{else}{$item.Cost} ISK{/if}</td>
	</tr>
	{/foreach}
	<tr><th colspan="4">Item Price Based On Breakdown</th><th>{$totaleveprice} ISK</th></tr>
	</table>
{/if}

{include file='footer.tpl'}