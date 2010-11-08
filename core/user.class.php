<?php

class User
{
  // Generic user info
  public $ID;
  public $Name;
  public $LastLogin;
  public $IsActive;
  public $IsGuest;
  public $IsAlly;
  public $IsRegistered;
  
  // EVE info
  public $Title;
  public $CharID;
  public $Alts;
  public $EVERoles;
  public $PortalRoles;
  public $IsOOP;
  public $OOPUntil;
  public $OOPNote;
  public $CorporationName;
  public $CorporationTicker;
  public $CorporationID;
  
  // Misc
  public $TimeZone;
  public $Email;
  public $IM;
  public $BirthDate;
  public $Location;
  public $DateFormat;
  public $PortalSettings;
  public $Signature;
  
  // EVE Roles
  const EVE_Director = "1";
  const EVE_Personnel_Manager = "128";
  const EVE_Accountant = "256";
  const EVE_Security_Manager = "512";
  const EVE_Factory_Manager = "1024";
  const EVE_Station_Manager = "2048";
  const EVE_Auditor = "4096";
  const EVE_Can_Take_Hangar_1 = "8192";
  const EVE_Can_Take_Hangar_2 = "16384";
  const EVE_Can_Take_Hangar_3 = "32768";
  const EVE_Can_Take_Hangar_4 = "65536";
  const EVE_Can_Take_Hangar_5 = "131072";
  const EVE_Can_Take_Hangar_6 = "262144";
  const EVE_Can_Take_Hangar_7 = "524288";
  const EVE_Can_Query_Hangar_1 = "1048576";
  const EVE_Can_Query_Hangar_2 = "2097152";
  const EVE_Can_Query_Hangar_3 = "4194304";
  const EVE_Can_Query_Hangar_4 = "8388608";
  const EVE_Can_Query_Hangar_5 = "16777216";
  const EVE_Can_Query_Hangar_6 = "33554432";
  const EVE_Can_Query_Hangar_7 = "67108864";
  const EVE_Can_Take_Accounts_1 = "134217728";
  const EVE_Can_Take_Accounts_2 = "268435456";
  const EVE_Can_Take_Accounts_3 = "536870912";
  const EVE_Can_Take_Accounts_4 = "1073741824";
  const EVE_Can_Take_Accounts_5 = "2147483648";
  const EVE_Can_Take_Accounts_6 = "4294967296";
  const EVE_Can_Take_Accounts_7 = "8589934592";
  const EVE_Can_Query_Accounts_1 = "17179869184";
  const EVE_Can_Query_Accounts_2 = "34359738368";
  const EVE_Can_Query_Accounts_3 = "68719476736";
  const EVE_Can_Query_Accounts_4 = "137438953472";
  const EVE_Can_Query_Accounts_5 = "274877906944";
  const EVE_Can_Query_Accounts_6 = "549755813888";
  const EVE_Can_Query_Accounts_7 = "1099511627776";
  const EVE_Equipment_Config = "2199023255552";
  
  // Portal Roles
  const MDYN_CEO = "1";
  const MDYN_Manager = "2";
  const MDYN_CanSubmitNews = "4";
  const MDYN_CanSubmitCalendar = "8";
  const MDYN_ForumModerator = "16";
  const MDYN_Administrator = "32";
  const MDYN_Developer = "64";
  const MDYN_HonoraryMember = "128";
  const MDYN_AllyLeader = "256";
  
  // Portal settings
  const ShowGameNews = 1;
  const ShowDevBlogs = 2;
  const ShowRPNews = 4;
  const ShowTQStatus = 8;
  const ShowCurrentSkill = 256;
  const CondensedForums = 16;
  const ContactInfoDirectors = 32;
  const ContactInfoPublic = 64;
  const ForwardMail = 128;
  
  function __construct() 
  {
    $this->ID = 2;
    $this->Name = "Guest";
    $this->LastLogin = gmdate("M d Y H:i:s");
    $this->IsActive = true;
    $this->IsRegistered = false;
    $this->IsGuest = true;
    $this->IsAlly = false;
    $this->IsRegistered = false;
    // EVE info
    $this->Title = "";
    $this->CharID = 0;
    $this->Alts = array();
    $this->EVERoles = "0";
    $this->PortalRoles = "0";
    // Misc
    $this->TimeZone = "0";
    $this->Email = "";
    $this->IM = "";
    $this->BirthDate = "";
    $this->Location = "";    
    $this->DateFormat = "m/d/Y H:i";
    // Portal preferences
    $this->PortalSettings = User::ShowGameNews | User::ShowTQStatus;
  }

  // *******************************************************
  // Constructs a new user object from the given database row
  // *******************************************************  
  public static function FromSQLRow($row)
  {
    $user = new User();
    
    // Generic user info    
    $user->ID = $row["id"];
    $user->Name = User::SQLUnEscape($row["Name"]);
    $user->LastLogin = $row["LastLogin"];
    $user->IsActive = $row["IsActive"];
    $user->IsGuest = $row["IsGuest"];
    $user->IsRegistered = true;
    
    // EVE info
    $user->Title = User::SQLUnEscape($row["Title"]);
    $user->CharID = $row["CharID"];
    if(empty($row["Alts"]))
      $user->Alts = array();
    else
      $user->Alts = explode(",", User::SQLUnEscape($row["Alts"]));
    $user->EVERoles = $row["EVERoles"];
    $user->PortalRoles = $row["PortalRoles"];
    $user->IsAlly = $row["IsAlly"];
    $user->CorporationName = User::SQLUnEscape($row["CorporationName"]);
    $user->CorporationTicker = User::SQLUnEscape($row["CorporationTicker"]);
    $user->CorporationID = $row["CorporationID"];
    
    // Misc
    $user->TimeZone = $row["TimeZone"];
    $user->Email = User::SQLUnEscape($row["EMail"]);
    $user->IM = User::SQLUnEscape($row["IM"]);
    $user->BirthDate = $row["BirthDate"];
    $user->Location = User::SQLUnEscape($row["Location"]);
    
    // Portal preferences
    $user->DateFormat = $row["DateFormat"];
    $user->PortalSettings = $row["PortalSettings"];
    $user->Signature = User::SQLUnEscape($row["Signature"]);
    
    // OOP
    $user->OOPUntil = $row["OOPUntil"];
    $user->OOPNote = User::SQLUnEscape($row["OOPNote"]);
    $user->IsOOP = ($user->OOPUntil == "0000-00-00" ? false : true);

    return $user;
  }
  
  // *******************************************************
  // Determines if the user has the given EVE role
  // *******************************************************  
  public function HasEVERole($role)
  {
    if(empty($role)) return true;
    return (BigNumber::Compare(BigNumber::BitwiseAnd($this->EVERoles, $role), "0") != 0);
  }

  // *******************************************************
  // Determines if the user has the given portal role
  // *******************************************************  
  public function HasPortalRole($role)
  {
    if(empty($role)) return true;
    return (BigNumber::Compare(BigNumber::BitwiseAnd($this->PortalRoles, $role), "0") != 0);
  }

  // *******************************************************
  // Determines the access right of the user
  // -1 - Guest
  // 0 - Registered guest
  // 1 - Ally
  // 2 - Corp Member
  // 3 - Manager
  // 4 - Director or Portal Administrator
  // *******************************************************  
  public function AccessRight()
  {
    if($this->HasPortalRole(User::MDYN_HonoraryMember))
      return 2;

    if($this->Name == "Guest")
      return 0;

    if($this->IsGuest)
      if($this->IsRegistered) return 0; else return 0;
      
    if($this->IsAlly)
      return 1;
      
    if($this->IsCEO() || $this->IsDirector() || $this->HasPortalRole(User::MDYN_Administrator))
      return 4;
    elseif($this->IsManager())
      return 3;
    else
      return 2;
  }

  // *******************************************************
  // Determines if the user is a CEO or not
  // *******************************************************  
  public function IsCEO()
  {
    if($this->HasPortalRole(User::MDYN_CEO) || $this->Title == "CEO")
      return true;
    else
      return false;
  }
  
  // *******************************************************
  // Determines if the user is a director or not
  // *******************************************************  
  public function IsDirector()
  {
    if($this->IsCEO())
      return false;

    if($this->HasEVERole(User::EVE_Director))
      return true;
    else
      return false;
  }

  // *******************************************************
  // Determines if the user is a manager or not
  // *******************************************************  
  public function IsManager()
  {
    if($this->IsCEO())
      return false;
    if($this->IsDirector())
      return false;

    if($this->HasPortalRole(User::MDYN_Manager) || $this->HasEVERole(User::EVE_Personnel_Manager) || $this->HasEVERole(User::EVE_Accountant) || $this->HasEVERole(User::EVE_Security_Manager) || $this->HasEVERole(User::EVE_Factory_Manager) || $this->HasEVERole(User::EVE_Station_Manager) || $this->HasEVERole(User::EVE_Auditor)) 
      return true;
    else
      return false;
  }
  
  // *******************************************************
  // Returns a human readable string displaying all EVE Roles
  // *******************************************************  
  public function StringFromEVERoles()
  {
    $ret = "";
    
    if($this->HasEVERole(User::EVE_Director)) $ret .= "Director, ";
    if($this->HasEVERole(User::EVE_Personnel_Manager)) $ret .= "Personnel Manager, ";
    if($this->HasEVERole(User::EVE_Accountant)) $ret .= "Accountant, ";
    if($this->HasEVERole(User::EVE_Security_Manager)) $ret .= "Security Manager, ";
    if($this->HasEVERole(User::EVE_Factory_Manager)) $ret .= "Factory Manager, ";
    if($this->HasEVERole(User::EVE_Station_Manager)) $ret .= "Station Manager, ";
    if($this->HasEVERole(User::EVE_Auditor)) $ret .= "Auditor, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_1)) $ret .= "Can Take from Hangar 1, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_2)) $ret .= "Can Take from Hangar 2, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_3)) $ret .= "Can Take from Hangar 3, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_4)) $ret .= "Can Take from Hangar 4, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_5)) $ret .= "Can Take from Hangar 5, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_6)) $ret .= "Can Take from Hangar 6, ";
    if($this->HasEVERole(User::EVE_Can_Take_Hangar_7)) $ret .= "Can Take from Hangar 7, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_1)) $ret .= "Can Query Hangar 1, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_2)) $ret .= "Can Query Hangar 2, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_3)) $ret .= "Can Query Hangar 3, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_4)) $ret .= "Can Query Hangar 4, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_5)) $ret .= "Can Query Hangar 5, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_6)) $ret .= "Can Query Hangar 6, ";
    if($this->HasEVERole(User::EVE_Can_Query_Hangar_7)) $ret .= "Can Query Hangar 7, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_1)) $ret .= "Can Take from Wallet 1, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_2)) $ret .= "Can Take from Wallet 2, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_3)) $ret .= "Can Take from Wallet 3, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_4)) $ret .= "Can Take from Wallet 4, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_5)) $ret .= "Can Take from Wallet 5, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_6)) $ret .= "Can Take from Wallet 6, ";
    if($this->HasEVERole(User::EVE_Can_Take_Accounts_7)) $ret .= "Can Take from Wallet 7, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_1)) $ret .= "Can Query Wallet 1, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_2)) $ret .= "Can Query Wallet 2, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_3)) $ret .= "Can Query Wallet 3, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_4)) $ret .= "Can Query Wallet 4, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_5)) $ret .= "Can Query Wallet 5, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_6)) $ret .= "Can Query Wallet 6, ";
    if($this->HasEVERole(User::EVE_Can_Query_Accounts_7)) $ret .= "Can Query Wallet 7, ";
    if($this->HasEVERole(User::EVE_Equipment_Config)) $ret .= "Can Config Equipment, ";
    
    if(substr($ret, -2) == ", ") $ret = substr($ret, 0, strlen($ret) - 2);
    if(empty($ret)) $ret = "No Additional EVE Roles Assigned";
    return $ret;
  }

  // *******************************************************
  // Corrects escaped strings
  // *******************************************************  
  private static function SQLUnEscape($string)
  {
    return str_ireplace(array('\"', "\'", "\\\\"), array('"', "'", "\\"), $string);
  }
  
}

?>
