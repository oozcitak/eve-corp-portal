{include file='header.tpl' title=' | Orders' script='production.js'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="index.php">My Orders</a>
<a class="header" href="index.php?action=addship">Place Ship Order</a>
<a class="header" href="index.php?action=addrig">Place Rig Order</a>
{if $user->AccessRight() >=2 }
<a class="header" href="index.php?action=addmisc">Misc. Order</a>
<a class="header" href="index.php?action=queue">Production Queue</a>
{/if}
<a class="header" href="index.php?action=help">Help</a>
{if $user->AccessRight() >= 3 }
<a class="adminheader" href="index.php?action=store">In Stock Now</a>
<a class="adminheader" href="index.php?action=edithelp">Edit Help Text</a>
{/if}
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $action == "home"}  
  <h3>My Orders</h3>
  {if empty($orders)}
    <p>You do not have any outstanding orders.</p>
  {else}
    <table class='data'>
    <tr><th>Ordered On</th><th>Order</th><th>Cost</th><th>Manager</th><th>Status</th><th></th></tr>
    {foreach from=$orders item=order}
      <tr class="{cycle values='altrow1,altrow2'}">
        <td>{$order.Date}</td>
        <td>{$order.Count} x {$order.Name} ({$order.GroupName})</td>
        <td>{$order.Cost} ISK</td>
        <td>{$order.Manager}</td>
        <td>{$order.Status}</td>
        {if $order.StatusID == 0 || $order.StatusID == 1 || $order.StatusID == 2 || $order.StatusID == 4 || $order.StatusID == 5 || $order.StatusID == 6 || $order.StatusID == 10}
            <td><a href="index.php?cancel={$order.ID}" onclick="javascript:return confirm('Are you sure you want to delete this order?');">Delete</a></td>
        {/if}
      </tr>
    {/foreach}
    </table>
  {/if}
{elseif $action == "queue"}  
  <h3>Production Queue</h3>
  {if empty($orders)}
    <p>The production queue is empty.</p>
  {else}
    <table class='data'>
    <tr><th>Ordered On</th><th>Ordered By</th><th>Order</th><th>Manager</th><th>Status</th></tr>
    {foreach from=$orders item=order}
      <tr class="{cycle values='altrow1,altrow2'}">
        <td>{$order.Date}</td>
        <td>{$order.Owner}</td>
        <td>{$order.Count} x {$order.Name} ({$order.GroupName})</td>
        <td>{$order.Manager}</td>
        <td>{$order.Status}</td>
      </tr>
    {/foreach}
    </table>
  {/if}  
{elseif $action == "addship" || $action == "addrig" || $action == "store"}
  <h3>{if $action == "addship"}New Ship Order{elseif $action == "store"}Buy Items In Stock Now{else}New Rig Order{/if}</h3>
  {if empty($items)}
    <p>Item inventory is empty. Please contact an industrial manager.</p>
  {else}
    <form method="post" action="index.php?action=adddone" onsubmit="javascript:return CheckCount();" >
    <table>
    <tr><td>Item: </td><td><select name='item'>
    {assign var='lastgroup' value=''}    
    {foreach from=$items item=item}
      {if $lastgroup != $item.GroupName}
        {if !empty($lastgroup)}
        </optgroup>
        {/if}
        <optgroup label="{$item.GroupName}">
        {assign var='lastgroup' value=$item.GroupName}  
      {/if}
      <option value="{$item.ID}">{$item.Name} ({if $item.Price == 0}FREE{else}{$item.Price} ISK{/if}){if $action == "store"} [{$item.Stockpile} QTY]{/if}</option>
    {/foreach}
    </optgroup>
    </select>
    <br />&nbsp;</td></tr>
    <tr><td>Quantity: </td><td><input type="text" name="count" id="count" value="1" size="10" onblur="javascript:CheckNumber(this);" /><br />&nbsp;</td></tr>
    <tr><td>Priority: </td><td><select name="priority"><option value="0">Low</option><option value="5" selected="selected">Normal</option><option value="10">High</option></select><br />&nbsp;</td></tr>
    <tr><td>Notes: </td><td><input type="text" name="notes" size="60" /><br />&nbsp;</td></tr>
    <tr><td colspan="2"><input type="submit" name="submit" value="Place Order" /></td></tr>
    </table>
    </form>
  {/if}
{elseif $action == "addmisc"}
  <h3>Miscellaneous Orders</h3>
  <p>You can use this form if the item you want to order is not listed above. You can also use it to inform the production manager that we are low on ammo or modules at a POS.</p>
  <form method="post" action="index.php?action=addmiscdone" >
  <input type="text" name="notes" size="60" />
  <br /><br />
  <input type="submit" name="submit" value="Place Order" />
  </form>
  
  {if !empty($misc)}
    <br /><br />
    <table class='data'>
    <tr><th>Ordered On</th><th>Ordered By</th><th>Order</th></tr>
    {foreach from=$misc item=order}
      <tr class="{cycle values='altrow1,altrow2'}">
      <td>{$order.Date}</td>
      <td>{$order.Owner}</td>
      <td>{$order.Notes}</td>    
      </tr>
    {/foreach}    
    </table>
  {/if}
{elseif $action == "help"}
  <h3>Help</h3>
  {$helptext}
{elseif $action == "edithelp"}
  <h3>Edit Help Text</h3>
  <form method="post" action="index.php?action=edithelpdone">
  {$core->HTMLEditor("helptext", $helptext, "300")}
  <br />
  <input type="submit" name="submit" value="Save" />&nbsp;<input type="submit" name="submit" value="Cancel" />
  </form>
{/if}

{include file='footer.tpl'}
