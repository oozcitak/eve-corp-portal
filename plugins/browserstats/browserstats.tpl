<!-- Include the standard page header -->
{include file='header.tpl' title=' | Browser Statistics'}

<!-- Throw in a heading and a paragraph of text. Smarty Templates are actually HTML files with special Smarty instructions. -->
<h3>Browser Statistics</h3>
<p>This is a sample plug-in written for plug-in developers.</p>

<!-- Display the stats -->
<!-- First check to see if we have any data -->
<!-- Remember that  $browserstats was assigned from index.php at line 41 -->
<!-- Also note how the Smarty Template function "if" accepts the PHP function empty() -->
{if empty($browserstats)}
  <!-- No stats yet (Actually this is not possible, we should have at least one row of data) Anyway :)-->
  <p>No stats to display.</p>
{else}
  <!-- Let us display the data in a table -->
  <table class="data">
  <tr>
    <th>Browser</th>
    <th>User Count</th>
  </tr>  
  <!-- "foreach" iterates through an array. $browserstats was an array assigned from our index.php -->
  <!-- from is the name of the template variable to iterate on -->
  <!-- key is the array key (note that there is no $ sign here) -->
  <!-- item is the actual array value (again no $ sign) -->
  <!-- "foreach"  has a lot more useful options. Check the Smarty documentation -->
  {foreach from=$browserstats key=browsername item=count}
    <!-- At the each iteration a new table row will be output -->
    <tr>
      <td>{$browsername}</td>
      <td>{$count}</td>
    </tr>
  {/foreach}
  </table>
{/if}

<!-- Include the standard page footer -->
{include file='footer.tpl'}
