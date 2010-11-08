{include file='header.tpl' title=' | Production Management'}
<a class="header" href="index.php">Entire Queue</a>
<a class="header" href="index.php?action=summary">New Order Summary</a>
<h3>Production Orders</h3>

{if $result == 1}
<div class='error'>Order has not been removed.</div>
{/if}

{if $action == "home"}
  {if empty($orders)}
    <p>The production queue is empty.</p>
  {else}
    <form method="post" action="index.php?action=change">
    <table class='data'>
    <tr><th></th><th>Ordered On</th><th>Ordered By</th><th>Order</th><th>Unit Price</th><th>Quantity</th><th>Total Cost</th><th>Priority</th><th>Manager</th><th>Status</th><th></th></tr>
    {foreach from=$orders item=order}
      {capture name=popuptext assign=popuptext}
        <b>Ordered On: </b>{$order.Date}<br />
        <b>Ordered By: </b>{$order.Owner}{if $order.IsAlly} (Alliance Member){/if}<br />
        <b>Priority: </b>{$order.Priority}<br />
        <hr size='1' />
        <b>Order: </b>{$order.Name} ({$order.GroupName})<br />
        <b>Quantity: </b>{$order.Count}<br />
        <b>Unit Price: </b>{$order.Price}{if $order.IsAlly} (Alliance Price){/if}<br />
        <b>Total Cost: </b>{$order.Cost}<br />
        <hr size='1' />
        <b>Manager: </b>{$order.Manager}<br />
        <b>Status: </b>{$order.Status}<br />
        {if !empty($order.Notes)}
        <hr size='1' />
        <b>Notes: </b><br />{$order.Notes}
        {/if}
      {/capture}
      <tr class="{cycle values='altrow1,altrow2'}" {popup text=$popuptext}>
        <td><input type="checkbox" name="item{$order.ID}" /></td>
        <td>{$order.Date}</td>
        <td>{$order.Owner}{if $order.IsAlly} (Ally){/if}</td>
        <td>{$order.Name} ({$order.GroupName})</td>
        <td>{$order.Price}</td>
        <td>{$order.Count}</td>
        <td>{$order.Cost}</td>
        <td>{$order.Priority}</td>
        <td>{$order.Manager}</td>
        <td>{$order.Status}</td>
        <td><a href="index.php?delete={$order.ID}">Delete</a></td>
      </tr>
    {/foreach}
    </table>
    <p>Total cost of listed orders: {$total} ISK.</p>
    Set Selected Orders To:
    <select name='status'>
    	<option value='1'>Need BPC</option>
    	<option value='2'>Need Materials</option>
    	<option value='3'>Producing</option>
    	<option value='4'>Contracted</option>
    	<option value='5'>Paid</option>
        <option value='6'>Rescinded</option>
        <option value='7'>Producing < 7 Days</option>
        <option value='8'>Producing < 14 Days</option>
        <option value='9'>Producing < 21 Days</option>
        <option value='10'>Queued Unk Eta</option>
    </select>
    <input type='submit' name='submit' value='Go' />
    </form>
  {/if}

  {if !empty($misc)}
    <h3>Miscellaneous Orders</h3>
    <table class='data'>
    <tr><th>Ordered On</th><th>Ordered By</th><th>Order</th><th></th></tr>
    {foreach from=$misc item=order}
      <tr class="{cycle values='altrow1,altrow2'}">
      <td>{$order.Date}</td>
      <td>{$order.Owner}</td>
      <td>{$order.Notes}</td>
      <td><a href="index.php?delete={$order.ID}">Delete</a></td>
      </tr>
    {/foreach}
    </table>
  {/if}
{elseif $action == "summary"}
  {if empty($glance)}
    <p>There are no new orders to process.</p>
  {else}
   <table class='data'>
   <tr><th>Order</th><th>Quantity</th></tr>
   {foreach from=$glance key=id item=value}
       <td>{$id}</td>
       <td>{$value}</td>
     </tr>
   {/foreach}
   </table>
  {/if}
{/if}

{include file='footer.tpl'}
