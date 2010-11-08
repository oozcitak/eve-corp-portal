{if !($IGB) }
</td>
{if $layout==3}
<td id="rightcell" width="220">
{include file='rightbar.tpl'}
</td>
{/if}
</tr>
</table>
</div>
{else}
<p>&nbsp;</p>
{/if}

<div class='footer'>
<hr size="1" />
&copy; Copyright Meridian Dynamics 2008. All rights reserved.<br />
Some images and text from <a href="http://eve-online.com">EVE Online</a> used on this site are copyright <a href="http://www.ccpgames.com/">CCP hf</a>.
{if $IGB}
<br />
In-game version of the portal offers a subset of out-of-game functionality. Click <a href="shellexec:http://{$smarty.server.SERVER_NAME}">here</a> to view the portal out-of-game.
{/if}
{php}
  global $perf_starttime;
  if(isset($perf_starttime))
  {
    $perf_endtime = microtime(true);
    $perf_interval = round($perf_endtime - $perf_starttime, 4);
    $this->assign('perf_interval', $perf_interval);
  }
{/php}
{if $perf_interval && $user->HasPortalRole(64) }
<br />
Portal core initialization took {$core->GetCoreInitTime()} seconds. Page processing took {$perf_interval} seconds. Portal core performed {$core->GetQueryCount()} database queries in {$core->GetQueryTime()} seconds and {$core->GetAPIQueryCount()} API queries.
{/if}
</div>

{if $IGB}
</div>
{/if}

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-4673837-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>

</body>
</html>
