<?php
  require_once('../../core/core.class.php');
  $core = new Core();

  if(@$_GET["action"] == "corp")
  {
    $raw = $core->APIQuery("http://api.eve-online.com/corp/CorporationSheet.xml.aspx");
    if(empty($raw))
    {
      $result = "";
      $error = "Could not connect to the EVE API Server.";
    }
    else
    {
      $xml = new SimpleXMLElement($raw);
      if ((int)$xml->error['code'] > 0) 
      {
        $result = "";
        $error = "Error: ".$xml->error;
      }
      else
      {
        $result["Corporation&nbsp;Name"] = $xml->result->corporationName;
        $result["Ticker"] = $xml->result->ticker;
        $result["Alliance&nbsp;Name"] = $xml->result->allianceName;
        $result["CEO"] = $xml->result->ceoName;
        $result["Headquarters"] = $xml->result->stationName;
        $result[] = "";
        $result["Tax&nbsp;Rate"] = $xml->result->taxRate."%";
        $result["Member&nbsp;Count"] = $xml->result->memberCount;
        $result["Member&nbsp;Limit"] = $xml->result->memberLimit;
        $result["Shares"] = $xml->result->shares;
        $result[] = "";
        $result["Description"] = $xml->result->description;
        $result["Web&nbsp;Site"] = empty($xml->result->url) ? "" : "<a href='".$xml->result->url."'>".$xml->result->url."</a>";
        
        $error = "";
      }
    }
    $core->assign("action", "corp");
    $core->assign("error", $error);
    $core->assign("result", $result);    
  }
  else
  {
    $users = $core->GetAllUsers(true, true);
    usort($users, "objcmp");    
    $core->assign("users", $users);
    
  }
  
  $core->display($core->PlugInPath.'orgchart/orgchart.tpl');
  
  // Sorts users by role then name
  function objcmp($a, $b)
  {
    if($a->IsCEO())
      return -1;
    elseif($b->IsCEO()) 
      return 1;
      
    if($a->IsDirector() && !$b->IsDirector())
      return -1;
    elseif($b->IsDirector() && !$a->IsDirector())
      return 1;

    if($a->IsManager() && !$b->IsManager())
      return -1;
    elseif($b->IsManager() && !$a->IsManager())
      return 1;
      
    return strcasecmp($a->Name, $b->Name);
  }
  
?>
