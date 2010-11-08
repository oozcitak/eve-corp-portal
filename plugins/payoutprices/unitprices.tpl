{include file='header.tpl' title=' | Unit Prices' script='prices.js'}

<!-- Section Navigation Buttons -->
<div class="header">
<a class="header" href="index.php">Unit Prices</a>
<a class="header" href="index.php?action=refine">Refining Parameters</a>
<a class="header" href="index.php?action=payout">Edit Payout Prices</a>
<a class="header" href="index.php?action=operationalitems">Edit Operational Items</a>
</div>
<br />
<!-- End Section Navigation Buttons -->

{if $result == 3}
<div class='info'>Items are deleted.</div>
{/if}

{if $action == "home"}
  <h3>Unit Prices</h3>
  
  <!-- Section Navigation Buttons <p>All ISK calculations on the IGB portal are based on the mineral and ice product unit prices.  -->
  <!-- Section Navigation Buttons Click one of the links below to fetch prices from an online mineral index.  -->
  <!-- Section Navigation Buttons You can also manually edit the prices.</p>  -->

  {if !empty($indexdate)}
    {if $indextime == 0}
      <p>Unit prices were last updated manually on {$indexdate}.</p>
    {else}
      <p>Unit prices were last updated from QTC-{$indextime} on {$indexdate}.</p>
    {/if}
  {/if}
  
  <!-- Section Navigation Buttons <a href="index.php?action=qtc7">Fetch Unit Prices From QTC-7</a><br />  -->
  <!-- Section Navigation Buttons <a href="index.php?action=qtc30">Fetch Unit Prices From QTC-30</a><br />   -->
  <!-- Section Navigation Buttons <br />  -->
  <a href="index.php?action=edit">Edit Unit Prices</a><br /><br />
{elseif $action == "edit" || $action == "payout"}
  {if $action == "edit"}
    <h3>Edit Unit Prices</h3>
    <form method='post' action='index.php?action=editdone'>
    
    <p>You can change the unit prices of minerals and ice products here. Once you save the
    new unit prices, prices of all payout items will also be updated.</p>
    <p>You can also choose to use compiled market data provided by Eve Central.  There are several
    import options like "<i>buy order average</i>" or "<i>sell order median</i>" prices.  The infomation is updated
    daily.  Any 'operational item' that has the Eve Central market import turned on will supercede any
    manually entered values.</p>
  {else}
    <h3>Edit Payout Prices</h3>
    <form method='post' action='index.php?action=payoutdone'>

    <p>You can manually update the following payout prices, but keep in mind that whenever
    mineral unit prices or refining paremeters are updated, changes you make here will be overwritten
    with automatically calculated prices.</p>
    <p>You can also choose to use compiled market data provided by Eve Central.  There are several
    import options like "<i>buy order average</i>" or "<i>sell order median</i>" prices.  The infomation is updated
    daily.  Any 'operational item' that has the Eve Central market import turned on will supercede any
    manually entered values.</p>
  {/if}
  
  <table class='data'>
  {assign var='lastgroup' value=''}
  {foreach from=$dbprices item=dbprice}
  {if $lastgroup != $dbprice.Group}
    {assign var='lastgroup' value=$dbprice.Group}  
    <tr><th>
    {foreach from=$ogroupid item=ogvalue}
     {if $lastgroup == $ogroupGroupID.$ogvalue}
      {$ogroupName.$ogvalue}
     {/if}
    {/foreach}
    </th><th>Price</th></th><th>Eve Central Market Import</th></tr>
  {/if}
  <tr>
    <td>{$dbprice.Name}</td><td><input type="text" size="15" name="item{$dbprice.ID}" value="{$dbprice.Price}" onblur="javascript:CheckNumber(this);" /> ISK</td>
    <td>{if $dbprice.Auto != 0}
        <input type="radio" name="automacro{$dbprice.ID}" value="100" {if $dbprice.Auto == 1}{assign var=typecheck value=0}CHECKED{/if}>None &nbsp;
        <input type="radio" name="automacro{$dbprice.ID}" value="1" {if $dbprice.Auto > 1 && $dbprice.Auto < 6}{assign var=typecheck value=$dbprice.Auto-1}CHECKED{/if}>All
        <input type="radio" name="automacro{$dbprice.ID}" value="6" {if $dbprice.Auto > 5 && $dbprice.Auto < 11}{assign var=typecheck value=$dbprice.Auto-6}CHECKED{/if}>Buy
        <input type="radio" name="automacro{$dbprice.ID}" value="11" {if $dbprice.Auto > 10 && $dbprice.Auto < 16}{assign var=typecheck value=$dbprice.Auto-11}CHECKED{/if}>Sell
         &nbsp;&nbsp;
        <input type="radio" name="autotype{$dbprice.ID}" value="1" {if $typecheck == '1'}CHECKED{/if}>Average
        <input type="radio" name="autotype{$dbprice.ID}" value="2" {if $typecheck == '2'}CHECKED{/if}>Median
        {else}
        <input type="radio" name="automacro{$dbprice.ID}" value="100" CHECKED>None
        {/if}
    </td>
  </tr>
  {/foreach}
  </table>
  <input type='submit' name='submit' value='Save' />&nbsp;<input type='submit' name='submit' value='Cancel' />
  </form>
{elseif $action == "refine"}
  <h3>Refining Parameters</h3>

  <p>Ore submitted by players will be broken down into basic minerals using the following
  paramaters. Leave 'Station Tax' blank to calculate the tax based on standing. Once you save the
  parameters, prices of all payout items will also be updated.</p>
      
  <form method='post' action='index.php?action=refinedone'>
  <table>
  <tr><td>Refining</td><td><select name='refining' id='refining' onchange='javascript:UpdateRefiningSkill();'>
  {section name=refining loop=6}
    <option value="{$smarty.section.refining.index}" {if $refining == $smarty.section.refining.index} selected="selected"{/if}>
    {$smarty.section.refining.index}
    </option>
  {/section}
  </select></td></tr>
  <tr><td>Refinery Efficiency</td><td><select name='refinery_efficiency' id='refinery_efficiency' onchange='javascript:UpdateRefineryEfficienySkill();'>
  {section name=refinery_efficiency loop=6}
    <option value="{$smarty.section.refinery_efficiency.index}" {if $refinery_efficiency == $smarty.section.refinery_efficiency.index} selected="selected"{/if}>
    {$smarty.section.refinery_efficiency.index}
    </option>
  {/section}
  </select></td></tr>
  <tr><td>Ore Processing Skills</td><td><select name='ore_skills'>
  {section name=ore_skills loop=6}
    <option value="{$smarty.section.ore_skills.index}" {if $ore_skills == $smarty.section.ore_skills.index} selected="selected"{/if}>
    {$smarty.section.ore_skills.index}
    </option>
  {/section}  
  </select></td></tr>
  <tr><td>Ice Processing Skill</td><td><select name='ice_skill'>
  {section name=ice_skill loop=6}
    <option value="{$smarty.section.ice_skill.index}" {if $ice_skill == $smarty.section.ice_skill.index} selected="selected"{/if}>
    {$smarty.section.ice_skill.index}
    </option>
  {/section}    
  </select><br />&nbsp;</td></tr>
  <tr><td>Refining Equipment</td><td><input type='text' size='4' name='refining_equipment' value='{$refining_equipment}' onblur="javascript:CheckNumber(this);" /></td></tr>
  <tr><td>Standing</td><td><input type='text' size='4' name='station_standing' value='{$station_standing}' onblur="javascript:CheckNumber(this);" /></td></tr>
  <tr><td>Station Tax</td><td><input type='text' size='4' name='station_tax' value='{if $station_tax != -1}{$station_tax}{/if}' onblur="javascript:CheckNumber(this);" /> %<br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type='submit' name='submit' value='Save Parameters' />&nbsp;<input type='submit' name='submit' value='Cancel' /></td></tr>
  </table>
  </form>
{elseif $action == "operationalitems"}
  <h3>Operational Items</h3>

  <p>The list below shows all the operational items that currently exist for use on the corporate
  payout structure.  You can remove items from the system or add new ones.  Adding a new group will
  require some minor code adjustments in several applications to be fully operational.  Adding a new
  item here will allow the item's 'isk' value be inputed.  Either by automatic means or manual entry.</p>

  <table class="data" frame="hsides" rules="rows" align="justify" width="100%" height="100%" cellpadding = "0" border="1">
  <A HREF="index.php?action=newitem">Add New Item</A><br>
  <th>id</th><th>EveTypeID</th><th>Name</th><th>GroupID</th><th>Price</th><th>DisplayOrder</th><th>Action</th>
   {foreach from=$manage_id key=key item=value}
     <tr>
      <td align="left"><font color="#FFFFFF">
        {$value}
      </font>
      </td>

      <td align="left"><font color="#FFFFFF">
        {$manage_EveTypeID.$value}
      </font>
      </td>

      <td align="left"><font color="#FFFFFF">
        {$manage_Name.$value}
      </font>
      </td>

      <td align="left"><font color="#FFFFFF">
        {$manage_GroupID.$value}
      </font>
      </td>

      <td align="left"><font color="#FFFFFF">
        {$manage_Price.$value}
      </font>
      </td>

      <td align="left"><font color="#FFFFFF">
        {$manage_DisplayOrder.$value}
      </font>
      </td>

      <td align="left"><font color="#FFFFFF">
      <A HREF="index.php?action=removeitem&template={$key}" onclick="javascript:return confirm('Are you sure you want to delete this item?');">Delete</A>
      </font>
      </td>

     </tr>
   {/foreach}
{elseif $action == "newitem"}
  <h3>Operational Items</h3>

  <p>Use the checkbox's below to choose the market groups that the items you want to add would recide.
  You can select multiable market groups.</p>

  <form method="post" action="index.php?action={$action}p">
  <table>
  <tr><td>
  {assign var='lastgroup' value=''}
  {foreach from=$items item=item}
    {if $lastgroup != $item.GroupName}
      {if !empty($lastgroup)}
      <br>
      {/if}
      <h3>{$item.GroupName}</h3>
      {assign var='lastgroup' value=$item.GroupName}
    {/if}
    <input type="checkbox" name="item{$item.EveTypeID}" />{$item.Name}{if !empty($item.Race)} ({$item.Race}){/if}<br>

  {/foreach}
  </optgroup>
  </select>
  <br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Add" /></td></tr>
{elseif $action == "newitemp"}
  <h3>Operational Items</h3>

  <p>Use the checkbox's below to choose the items you want to add to operational items.
  You can select multiable items.  If a current item is already part of the operational
  items list, the new input values for that item will not overwrite the existing data.</p>

  <form method="post" action="index.php?action={$action}p">
  <input type="hidden" name="template" value="{$template}">
  <table>
  <tr><td>
  {assign var='lastgroup' value=''}
  {foreach from=$items item=item}
    {if $lastgroup != $item.GroupName}
      {if !empty($lastgroup)}
      <br>
      {/if}
      <h3>{$item.GroupName}</h3>
      {assign var='lastgroup' value=$item.GroupName}
    {/if}
    <input type="checkbox" name="item{$item.EveTypeID}" />{$item.Name}{if !empty($item.Race)} ({$item.Race}){/if}<br>

  {/foreach}
  </optgroup>
  </select>
  <br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Add" /></td></tr>
{elseif $action == "newitempp"}
  <h3>Operational Items</h3>

  <p>Specify the Group ID and Display Order settings for the item you want to add.</p>

  <form method="post" action="index.php?action=newitemsave">
  <table class="data" align="justify" width="50%" height="50%" cellpadding = "0" border="1">
  <th>Item</th><th>GroupID</th><th>Display Order</th>
  {assign var='lastgroup' value=''}
  {foreach from=$items item=item}
    {if $lastgroup != $item.GroupName}
      {if !empty($lastgroup)}
      {/if}
      {assign var='lastgroup' value=$item.GroupName}
    {/if}

     <tr>
      <td align="left"><font color="#FFFFFF">
        {$item.Name}{if !empty($item.Race)} ({$item.Race}){/if}
        <input type="hidden" name="iname{$item.EveTypeID}" value="{$item.Name}">
        <input type="hidden" name="item{$item.EveTypeID}" value="{$item.EveTypeID}">
      </font>
      </td>

      <td align="left" width="64px"><font color="#FFFFFF">
        <input type="text" name="groupid{$item.EveTypeID}" SIZE="1" MAXLENGTH="2" VALUE="10"/>
      </font>
      </td>

      <td align="left" width="32px"><font color="#FFFFFF">
        <input type="text" name="displayorder{$item.EveTypeID}" SIZE="1" MAXLENGTH="2" VALUE="0"/>
      </font>
      </td>
  {/foreach}
  <br />&nbsp;</td></tr>
  <tr><td colspan="2"><input type="submit" name="submit" value="Add Items" /></td></tr>



{/if}

{if $result == 1}
<div class='info'>Prices are saved.</div>
{elseif $result == 2}
<div class='info'>Refining parameters are saved.</div>
{/if}

{include file='footer.tpl'}