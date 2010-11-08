<?php
// The Core class derives from the Smarty class
require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."smarty".DIRECTORY_SEPARATOR."Smarty.class.php");
  
// Custom classes
require_once("user.class.php");
require_once("forum.class.php");
require_once("feed.class.php");
require_once("eveserver.class.php");
require_once("article.class.php");
require_once("newsitem.class.php");
require_once("calendar.class.php");
require_once("shout.class.php");
require_once("notepad.class.php");
require_once("math.class.php");
require_once("plugin.class.php");
require_once("log.class.php");
require_once("cron.class.php");
require_once("mail.class.php");

// FCK editor
require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."fckeditor".DIRECTORY_SEPARATOR."fckeditor.php");

class Core extends Smarty
{
  // File system path to plug-ins
  public $PlugInPath;
  
  // Database links
  protected $CoreLink;
  protected $PluginLink;
  protected $EveLink;
  // Core database parameters
  protected $CoreDBServer;
  protected $CoreDBName;
  protected $CoreDBUser;
  protected $CoreDBPass;
  // Plugin database parameters
  protected $PluginDBServer;
  protected $PluginDBName;
  protected $PluginDBUser;
  protected $PluginDBPass;
  // EVE database parameters
  protected $EveDBServer;
  protected $EveDBName;
  protected $EveDBUser;
  protected $EveDBPass;
  
  // Perf monitor
  protected $CoreInit;
  protected $QueryCount;
  protected $QueryTime;
  protected $APIQueryCount;
  
  // *******************************************************
  // Constructor
  // *******************************************************  
  function __construct()
  {
    $init_starttime = microtime(true);
  
    // Core database parameters
    $this->CoreDBServer = "localhost";
    $this->CoreDBName = "portal_core";
    $this->CoreDBUser = "portal_core";
    $this->CoreDBPass = "portal_core";

    // Plugin database parameters
    $this->PluginDBServer = "localhost";
    $this->PluginDBName = "portal_plugins";
    $this->PluginDBUser = "portal_plugins";
    $this->PluginDBPass = "portal_plugins";
          
    // EVE database parameters
    $this->EveDBServer = "localhost";
    $this->EveDBName = "portal_eve";
    $this->EveDBUser = "portal_eve";
    $this->EveDBPass = "portal_eve";
        
    // Smarty settings
    parent::__construct();
    $path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."smarty".DIRECTORY_SEPARATOR;
    $this->template_dir = $path."templates";
    $this->compile_dir = $path."templates_c";
    $this->config_dir = $path."config";
    
    // Auto-login user
    if(isset($_COOKIE["mdyn_portal"]))
      $this->AutoLogin();

    // Update page visit
    $this->UpdateLastPageVisit();
    
    // Plug-in path
    $this->PlugInPath = realpath(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins").DIRECTORY_SEPARATOR;
    
    // Global template variables
    parent::assign("core", $this);
    parent::assign("user", $this->CurrentUser());
    parent::assign("baseurl", "http://".$_SERVER["SERVER_NAME"]."/");
    parent::assign("IGB", $this->IsIGB());
    parent::assign("unreadmails", $this->UnreadMailCount());
    
    // Alliance web site
    parent::assign("allianceurl", $this->GetSetting("AllianceURL"));
    parent::assign("killboardurl", $this->GetSetting("KillboardURL"));
    
    // Plug-ins
    parent::assign("plugins", $this->ReadPlugIns());
    
    if($this->IsIGB())
    {
      // Trust required
      if($_SERVER['HTTP_EVE_TRUSTED'] == 'no')
      {
        header('eve.trustMe:http://' . $_SERVER['HTTP_HOST'] . '/php/::This site needs trust.');
        echo "<html><body>";
        echo "<p>This site needs trust to function.</p>";
        echo "</body></html>";
        exit;
      }
      
      // Guest access is not allowed in-game
      if((substr($_SERVER["REQUEST_URI"], -9) != "login.php") && $this->CurrentUser()->Name == "Guest")
        $this->Goto("../php/login.php");      
    }
    
    $this->QueryCount = 0;
    $this->APIQueryCount = 0;
    $init_endtime = microtime(true);
    $this->CoreInit = round($init_endtime - $init_starttime, 4);
  }

  // *******************************************************
  // MAIL  MAIL  MAIL  MAIL  MAIL MAIL MAIL MAIL MAIL 
  // MAIL  MAIL  MAIL  MAIL  MAIL MAIL MAIL MAIL MAIL 
  // MAIL  MAIL  MAIL  MAIL  MAIL MAIL MAIL MAIL MAIL 
  // *******************************************************  
  // *******************************************************
  // Reads the current user's mailbox
  // *******************************************************  
  public function ReadMailBox($isinbox, $folder = "", $start = 0, $count = 20, $sort = "date")
  {
    $names = $this->GetAllUserNames();
    $mailbox = array();
    $enckey = "h34rt0fg0ld".$this->CurrentUser()->ID;
    $query = "SELECT mail.*, users.Name AS FromName, AES_DECRYPT(mail.Text, '".$enckey."') AS RealText FROM mail INNER JOIN users ON mail.From = users.ID WHERE UserID = ".$this->CurrentUser()->ID;
    if($isinbox)
      $query .= " AND IsInbox = TRUE";
    else
      $query .= " AND IsInbox = FALSE";
    if(!empty($folder))
      $query .= " AND Folder = '".$this->SQLEscape($folder)."'";
    if($sort == "sender")
      $query .= " ORDER BY FromName ASC, Date DESC";
    elseif($sort == "subject")
      $query .= " ORDER BY Title ASC, Date DESC";
    else
      $query .= " ORDER BY Date DESC";
    $query .= " LIMIT ".$start.",".$count;
    $result = $this->CoreSQL($query);
    while($row = mysql_fetch_assoc($result))
    {
      $mail = new Mail();
      $mail->ID = $row["id"];
      $mail->UserID = $row["UserID"];
      $mail->Date = $row["Date"];
      
      $mail->From = $row["From"];
      $mail->To = explode(",", $row["To"]);
      $mail->CC = explode(",", $row["CC"]);
      $mail->BCC = explode(",", $row["BCC"]);
      
      $mail->FromName = $names[$mail->From];
      $mail->ToName = $this->IDToNames($mail->To, $names);
      $mail->CCName = $this->IDToNames($mail->CC, $names);
      $mail->BCCName = $this->IDToNames($mail->BCC, $names);

      $mail->Title = $this->SQLUnEscape($row["Title"]);
      // Message digest on the the message list
      $mail->Text = strip_tags($this->SQLUnEscape($row["RealText"]));
      if(strlen($mail->Text) > 400)
        $mail->Text = substr($mail->Text, 0, 400)." <b>...</b>";

      $mail->IsRead = $row["IsRead"];
      $mail->IsInbox = $row["IsInbox"];
      $mail->Folder = $this->SQLUnEscape($row["Folder"]);
      
      $mailbox[] = $mail;
    }
    mysql_free_result($result);
    return $mailbox;
  }

  // *******************************************************
  // Searches the current user's mailbox
  // *******************************************************  
  public function SearchMailBox($search, $isinbox, $folder = "")
  {
    $names = $this->GetAllUserNames();
    $mailbox = array();
    $enckey = "h34rt0fg0ld".$this->CurrentUser()->ID;
    $query = "SELECT *, AES_DECRYPT(Text, '".$enckey."') AS RealText FROM mail WHERE UserID = ".$this->CurrentUser()->ID;
    $first = true;
    $keywords = explode(" ", $search);
    foreach($keywords as $keyword)
    {
      if($first)
      {
        $first = false;
        $query .= " AND (";
      }
      else
        $query .= " OR";
      $query .= " (Title LIKE '%".$this->SQLEscape($keyword)."%' OR AES_DECRYPT(Text, '".$enckey."') LIKE '%".$this->SQLEscape($keyword)."%')";
    }
    $query .= ")";
    if($isinbox)
      $query .= " AND IsInbox = TRUE";
    else
      $query .= " AND IsInbox = FALSE";
    if(!empty($folder))
      $query .= " AND Folder = '".$this->SQLEscape($folder)."'";
    $query .= " ORDER BY Date DESC";
    $query .= " LIMIT 20";

    $result = $this->CoreSQL($query);
    while($row = mysql_fetch_assoc($result))
    {
      $mail = new Mail();
      $mail->ID = $row["id"];
      $mail->UserID = $row["UserID"];
      $mail->Date = $row["Date"];

      $mail->From = $row["From"];
      $mail->To = explode(",", $row["To"]);
      $mail->CC = explode(",", $row["CC"]);
      $mail->BCC = explode(",", $row["BCC"]);

      $mail->FromName = $names[$mail->From];
      $mail->ToName = $this->IDToNames($mail->To, $names);
      $mail->CCName = $this->IDToNames($mail->CC, $names);
      $mail->BCCName = $this->IDToNames($mail->BCC, $names);

      $mail->Title = $this->SQLUnEscape($row["Title"]);

      $text = strip_tags($this->SQLUnEscape($row["RealText"]));
      $maxlen = 400;
      foreach($keywords as $keyword)
      {
        $i = stripos($text, $keyword);
        if($i !== false)
        {
          $startstr = substr($text, 0, $i);
          $key = substr($text, $i, strlen($keyword));
          $endstr = substr($text, $i + strlen($keyword));
          if(strlen($startstr) > $maxlen) $startstr = "<b>...&nbsp;</b>".substr($startstr, -$maxlen);
          if(strlen($endstr) > $maxlen) $endstr = substr($endstr, 0, $maxlen)."<b>&nbsp;...</b>";
          $text = $startstr."<span class='highlight'>".$key."</span>".$endstr;
        }
      }
      if(strlen(strip_tags($text)) > 2 * $maxlen + 40) $text = substr(strip_tags($text), 0, 2 * $maxlen)."<b>...&nbsp;</b>";
      $mail->Text = $text;

      $mail->IsRead = $row["IsRead"];
      $mail->IsInbox = $row["IsInbox"];
      $mail->Folder = $this->SQLUnEscape($row["Folder"]);

      $mailbox[] = $mail;
    }
    mysql_free_result($result);
    return $mailbox;
  }

  // *******************************************************
  // Reads the given mail
  // *******************************************************
  public function ReadMail($id)
  {
    $names = $this->GetAllUserNames();
    $enckey = "h34rt0fg0ld".$this->CurrentUser()->ID;
    $result = $this->CoreSQL("SELECT *, AES_DECRYPT(Text, '".$enckey."') AS RealText FROM mail WHERE UserID = ".$this->CurrentUser()->ID." AND id = ".$id);
    if($row = mysql_fetch_assoc($result))
    {
      $mail = new Mail();
      $mail->ID = $row["id"];
      $mail->UserID = $row["UserID"];
      $mail->Date = $row["Date"];

      $mail->From = $row["From"];
      $mail->To = empty($row["To"]) ? array() : explode(",", $row["To"]);
      $mail->CC = empty($row["CC"]) ? array() : explode(",", $row["CC"]);
      $mail->BCC = empty($row["BCC"]) ? array() : explode(",", $row["BCC"]);

      $mail->FromName = $names[$mail->From];
      $mail->ToName = $this->IDToNames($mail->To, $names);
      $mail->CCName = $this->IDToNames($mail->CC, $names);
      $mail->BCCName = $this->IDToNames($mail->BCC, $names);

      $mail->Title = $this->SQLUnEscape($row["Title"]);
      $mail->Text = $this->SQLUnEscape($row["RealText"]);

      $mail->IsRead = $row["IsRead"];
      $mail->IsInbox = $row["IsInbox"];
      $mail->Folder = $this->SQLUnEscape($row["Folder"]);
      
      return $mail;
    }

    return false;
  }

  // *******************************************************
  // Deletes the given mail. ID can be a single database ID or an array
  // of IDs.
  // *******************************************************  
  public function DeleteMail($id)
  {
    if(is_array($id))
      $this->CoreSQL("DELETE FROM mail WHERE FIND_IN_SET(id, '".implode(",", $id)."')");
    else
      $this->CoreSQL("DELETE FROM mail WHERE id = ".$id);
  }
  
  // *******************************************************
  // Moves the given mail to the given folder. ID can be a single 
  // database ID or an array of IDs.
  // *******************************************************  
  public function MoveMail($id, $folder)
  {
    if(is_array($id))
      $this->CoreSQL("UPDATE mail SET Folder = '".$this->SQLEscape($folder)."' WHERE FIND_IN_SET(id, '".implode(",", $id)."')");
    else
      $this->CoreSQL("UPDATE mail SET Folder = '".$this->SQLEscape($folder)."' WHERE id = ".$id);
  }

  // *******************************************************
  // Marks the given mail as Read/Unread. ID can be a single 
  // database ID or an array of IDs.
  // *******************************************************  
  public function MarkMailRead($id, $isread = true)
  {
    if(is_array($id))
      $this->CoreSQL("UPDATE mail SET IsRead = ".($isread == true ? 1 : 0)." WHERE FIND_IN_SET(id, '".implode(",", $id)."')");
    else
      $this->CoreSQL("UPDATE mail SET IsRead = ".($isread == true ? 1 : 0)." WHERE id = ".$id);
  }

  // *******************************************************
  // Returns the number of messages in the current user's mailbox
  // *******************************************************  
  public function MailBoxCount($isinbox, $folder)
  {
    $query = "SELECT COUNT(*) FROM mail WHERE UserID = ".$this->CurrentUser()->ID;
    if($isinbox)
      $query .= " AND IsInbox = TRUE";
    else
      $query .= " AND IsInbox = FALSE";
    if(!empty($folder))
      $query .= " AND Folder = '".$this->SQLEscape($folder)."'";
    $result = $this->CoreSQL($query);
    return mysql_result($result, 0);
  }

  // *******************************************************
  // Returns the number of unread messages in the current user's mailbox
  // *******************************************************  
  public function UnreadMailCount($isinbox = true, $folder = "")
  {
    $query = "SELECT COUNT(*) FROM mail WHERE IsRead = FALSE AND UserID = ".$this->CurrentUser()->ID;
    if($isinbox)
      $query .= " AND IsInbox = TRUE";
    else
      $query .= " AND IsInbox = FALSE";
    if(!empty($folder))
      $query .= " AND Folder = '".$this->SQLEscape($folder)."'";
    $result = $this->CoreSQL($query);
    return mysql_result($result, 0);
  }
  
  // *******************************************************
  // Maps IDs to array values
  // *******************************************************  
  private function IDToNames($ids, $values)
  {
    $result = array();
    foreach($ids as $id)
    {
      foreach($values as $key => $value)
      {
        if($id == $key)
        {
          $result[$id] = $value;
          break;
        }
      }
    }
    return implode(", ", $result);
  }
  
  // *******************************************************
  // Sends a portal mail
  // *******************************************************  
  public function SendMail($title, $text, $to, $cc = "", $bcc = "")
  {
    if(is_array($to)) $to = implode(",", $to);
    if(!empty($cc) && is_array($cc)) $cc = implode(",", $cc);
    if(!empty($bcc) && is_array($bcc)) $bcc = implode(",", $bcc);
    
    // Expand distribution lists
    $users = $this->GetAllUsers(false, true);
    $all = array();
    $corp = array();
    $man = array();
    $dir = array();
    foreach($users as $user)
    {
      $all[] = $user->ID;
      if(!$user->IsGuest)
        $corp[] = $user->ID;
      if($user->IsManager())
        $man[] = $user->ID;
      if($user->IsDirector() || $user->IsCEO())
        $dir[] = $user->ID;
    }
    
    // Temporary function to remove lists (negative IDs)
    $lambda = create_function('$a', 'return ($a > 0);');
    $to = explode(",", $to);
    
    if(in_array(-1, $to))
      $to = array_merge($to, $all);
    if(in_array(-2, $to))
      $to = array_merge($to, $corp);
    if(in_array(-3, $to))
      $to = array_merge($to, $man);
    if(in_array(-4, $to))
      $to = array_merge($to, $dir);
    $to = array_filter($to, $lambda);
    $to = implode(",", array_unique($to));

    $cc = explode(",", $cc);
    if(in_array(-1, $cc))
      $cc = array_merge($cc, $all);
    if(in_array(-2, $cc))
      $cc = array_merge($cc, $corp);
    if(in_array(-3, $cc))
      $cc = array_merge($cc, $man);
    if(in_array(-4, $cc))
      $cc = array_merge($cc, $dir);
    $cc = array_filter($cc, $lambda);
    $cc = implode(",", array_unique($cc));
    
    $bcc = explode(",", $bcc);
    if(in_array(-1, $bcc))
      $bcc = array_merge($bcc, $all);
    if(in_array(-2, $bcc))
      $bcc = array_merge($bcc, $corp);
    if(in_array(-3, $bcc))
      $bcc = array_merge($bcc, $man);
    if(in_array(-4, $bcc))
      $bcc = array_merge($bcc, $dir);
    $bcc = array_filter($bcc, $lambda);
    $bcc = implode(",", array_unique($bcc));
    
    // Combine all fields to get all recipients
    $allto = implode(",", array_unique(array_filter(explode(",", $to) + explode(",", $cc) + explode(",", $bcc))));

    // Encode special chars
    $title = htmlspecialchars($title);
    $text = $text;
    
    // Save to receivers' inboxes
    $this->CoreSQL("CALL SendMail(".$this->CurrentUser()->ID.", '".$allto."', '".$to."', '".$cc."', '".$bcc."', '".$this->GMTTime()."', '".$this->SQLEscape($title)."', '".$this->SQLEscape($text)."', 'h34rt0fg0ld')");

    // Forward to real e-mail addresses
    $text = wordwrap($text, 60);
    $text = str_replace("\n.", "\n..", $text);
    // Header
    $headers  = "MIME-Version: 1.0"."\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1"."\r\n";
    $headers .= "From: ".$this->CurrentUser()->Name." <noreply@eve-portal.com>"."\r\n";
    // List of users
    $result = $this->CoreSQL("SELECT id, Name, EMail, PortalSettings FROM users WHERE IsActive = TRUE AND FIND_IN_SET(id, '".$allto."') <> 0 AND TRIM(EMail) <> ''");
    while($row = mysql_fetch_assoc($result))
    {
      if($row["PortalSettings"] & User::ForwardMail)
      {
        mail($this->SQLUnEscape($row["Name"])." <".$this->SQLUnEscape($row["EMail"]).">", $title, $text, $headers);
      }
    }
  }
  
  // *******************************************************
  // CRON  CRON  CRON  CRON  CRON CRON CRON CRON CRON 
  // CRON  CRON  CRON  CRON  CRON CRON CRON CRON CRON 
  // CRON  CRON  CRON  CRON  CRON CRON CRON CRON CRON 
  // *******************************************************  
  // *******************************************************
  // Returns all cron jobs
  // *******************************************************  
  public function ReadAllCronJobs()
  {
    $jobs = array();
    $root = dirname(dirname(__FILE__));
    $result = $this->CoreSQL("SELECT cron.*, users.Name AS DeveloperName FROM cron INNER JOIN users ON users.ID = cron.Developer");
    while($row = mysql_fetch_assoc($result))
    {
      $cron = new Cron();
      $cron->ID = $row["id"];
      $cron->Title = $this->SQLUnEscape($row["Title"]);
      $cron->ScheduleType = $row["ScheduleType"];
      $cron->Developer = $row["Developer"];
      $cron->DeveloperName = $this->SQLUnEscape($row["DeveloperName"]);
      $cron->Source = $this->SQLUnEscape($row["Source"]);
      if(substr($cron->Source, 0, 1) != "/") $cron->Source = "/".$cron->Source;
      $cron->FullPath = $root.$cron->Source;
      $cron->FileExists = file_exists($cron->FullPath);
      $cron->LastRun = $row["LastRun"];
      $jobs[] = $cron;
    }
    mysql_free_result($result);
    return $jobs;
  }
  
  // *******************************************************
  // Returns all cron jobs with the given schedule type
  // *******************************************************
  public function ReadCronJobsForType($scheduletype)
  {
    $jobs = array();
    $root = dirname(dirname(__FILE__));
    $result = $this->CoreSQL("SELECT id, Title, Developer, Source FROM cron WHERE ScheduleType=".$scheduletype);
    while($row = mysql_fetch_assoc($result))
    {
      $cron = new Cron();
      $cron->ID = $row["id"];
      $cron->Title = $this->SQLUnEscape($row["Title"]);
      $cron->Developer = $row["Developer"];
      $cron->Source = $this->SQLUnEscape($row["Source"]);
      if(substr($cron->Source, 0, 1) != "/") $cron->Source = "/".$cron->Source;
      $cron->FullPath = $root.$cron->Source;
      $cron->FileExists = file_exists($cron->FullPath);
      $jobs[] = $cron;
    }
    mysql_free_result($result);
    return $jobs;
  }

  // *******************************************************
  // Returns all cron jobs scheduled by the current user
  // *******************************************************  
  public function ReadCronJobs()
  {
    $jobs = array();
    $root = dirname(dirname(__FILE__));
    $result = $this->CoreSQL("SELECT * FROM cron WHERE Developer=".$this->CurrentUser()->ID);
    while($row = mysql_fetch_assoc($result))
    {
      $cron = new Cron();
      $cron->ID = $row["id"];
      $cron->Title = $this->SQLUnEscape($row["Title"]);
      $cron->ScheduleType = $row["ScheduleType"];
      $cron->Source = $this->SQLUnEscape($row["Source"]);
      if(substr($cron->Source, 0, 1) != "/") $cron->Source = "/".$cron->Source;
      $cron->FullPath = $root.$cron->Source;
      $cron->FileExists = file_exists($cron->FullPath);
      $cron->LastRun = $row["LastRun"];
      $jobs[] = $cron;
    }
    mysql_free_result($result);
    return $jobs;
  }

  // *******************************************************
  // Returns the cron job with the given ID
  // *******************************************************  
  public function ReadCronJob($id)
  {
    $root = dirname(dirname(__FILE__));
    $result = $this->CoreSQL("SELECT cron.*, users.Name AS DeveloperName FROM cron INNER JOIN users ON users.ID = cron.Developer WHERE cron.id=".$id);
    $row = mysql_fetch_assoc($result);
    if($row === false) return false;
    
    $cron = new Cron();
    $cron->ID = $row["id"];
    $cron->Title = $this->SQLUnEscape($row["Title"]);
    $cron->ScheduleType = $row["ScheduleType"];
    $cron->Developer = $row["Developer"];
    $cron->DeveloperName = $this->SQLUnEscape($row["DeveloperName"]);
    $cron->Source = $this->SQLUnEscape($row["Source"]);
    if(substr($cron->Source, 0, 1) != "/") $cron->Source = "/".$cron->Source;
    $cron->FullPath = $root.$cron->Source;
    $cron->FileExists = file_exists($cron->FullPath);
    $cron->LastRun = $row["LastRun"];

    mysql_free_result($result);
    return $cron;
  }

  // *******************************************************
  // Creates a new cron job for the current user
  // *******************************************************  
  public function NewCronJob($title, $type, $source)
  {
    $this->CoreSQL("INSERT INTO cron (Title, ScheduleType, Source, Developer) VALUES ('".$this->SQLEscape($title)."', ".$type.", '".$this->SQLEscape($source)."', ".$this->CurrentUser()->ID.")");
    $this->Log("CRON(".$title.") Created.");
  }

  // *******************************************************
  // Runs the given cron job
  // *******************************************************  
  public function RunCronJob($id)
  {
    $root = dirname(dirname(__FILE__));
    $result = $this->CoreSQL("SELECT Source FROM cron WHERE id=".$id);
    $row = mysql_fetch_assoc($result);
    if($row === false) return false;
    
    $filename = $this->SQLUnEscape($row["Source"]);
    mysql_free_result($result);
    
    if(substr($filename, 0, 1) != "/") $filename = "/".$filename;
    $path = $root.$filename;
    if(!file_exists($path)) return false;
    
    $source = file_get_contents($path);
    // Strip PHP tags
    if(substr($source, 0, 5) == "<?php")
      $source = substr($source, 5);
    if(substr($source, -2) == "?>")
      $source = substr($source, 0, strlen($source) - 2);
    // Evaluate the code
    ob_start();
    eval($source);
    $output = ob_get_clean();
    // Save output and last run time
    $this->CoreSQL("UPDATE cron SET LastRun='".$this->GMTTime()."', LastError='".$this->SQLEscape($output)."' WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Edits the given cron job
  // *******************************************************  
  public function EditCronJob($id, $title, $type, $source)
  {
    $this->CoreSQL("UPDATE cron SET Title='".$this->SQLEscape($title)."', ScheduleType=".$type.", Source='".$this->SQLEscape($source)."', LastRun='0000-00-00 00:00:00' AND LastError='' WHERE id=".$id." LIMIT 1");
  }
  
  // *******************************************************
  // Assigns the given developer to the given cron job
  // *******************************************************  
  public function AssignCronDeveloper($id, $developer)
  {
    $this->CoreSQL("UPDATE cron SET Developer=".$developer." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Saves the last run time of the given cron job
  // *******************************************************  
  public function SaveCronLastRunTime($id)
  {
    $this->CoreSQL("UPDATE cron SET LastRun='".$this->GMTTime()."' WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Saves the return value of the cron job
  // *******************************************************  
  public function SaveCronResult($id, $result)
  {
    $this->CoreSQL("UPDATE cron SET LastError='".$this->SQLEscape($result)."' WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Deletes the cron job with the given ID
  // *******************************************************  
  public function DeleteCronJob($id)
  {
    $result = $this->CoreSQL("SELECT Title FROM cron WHERE id=".$id." LIMIT 1");
    $title = $this->SQLUnEscape(mysql_result($result, 0));
    $this->CoreSQL("DELETE FROM cron WHERE id=".$id." LIMIT 1");
    $this->Log("CRON(".$title.") Deleted.");
  }

  // *******************************************************
  // PLUG-INS  PLUG-INS  PLUG-INS  PLUG-INS  PLUG-INS 
  // PLUG-INS  PLUG-INS  PLUG-INS  PLUG-INS  PLUG-INS 
  // PLUG-INS  PLUG-INS  PLUG-INS  PLUG-INS  PLUG-INS 
  // *******************************************************  
  // *******************************************************
  // Returns all readable plug-ins
  // *******************************************************  
  public function ReadPlugIns()
  {
    $plugins = array();
    $result = $this->CoreSQL("SELECT plugins.*, users.Name AS DeveloperName FROM plugins INNER JOIN users ON plugins.Developer=users.ID WHERE Developer=".$this->CurrentUser()->ID." OR (`Release`>0 AND ReadAccess<=".$this->CurrentUser()->AccessRight().") ORDER BY `Order` ASC, `Title` ASC");
    while($row = mysql_fetch_assoc($result))
    {
      $plugin = new PlugIn();
      $plugin->ID = $row["id"];
      $plugin->Name = $this->SQLUnEscape($row["Name"]);
      $plugin->Title = $this->SQLUnEscape($row["Title"]);
      $plugin->Developer = $row["Developer"];
      $plugin->DeveloperName = $row["DeveloperName"];
      $plugin->Release = $row["Release"];
      $plugin->ReadAccess = $row["ReadAccess"];
      $plugin->URL = "http://".$_SERVER["SERVER_NAME"]."/plugins/".$plugin->Name."/index.php";
      $plugin->ShowIGB = $row["ShowIGB"];
      $plugin->ShowAdmin = $row["ShowAdmin"];
      $plugin->FileExists = file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin->Name.DIRECTORY_SEPARATOR."index.php");
      if($plugin->FileExists || $plugin->Developer == $this->CurrentUser()->ID || $this->CurrentUser()->AccessRight() == 4)
      {
        $plugins[] = $plugin;
      }
    }
    mysql_free_result($result);
    return $plugins;
  }
  
  // *******************************************************
  // Returns all plug-ins
  // *******************************************************  
  public function ReadAllPlugIns()
  {
    $plugins = array();
    $result = $this->CoreSQL("SELECT plugins.*, users.Name AS DeveloperName FROM plugins INNER JOIN users ON plugins.Developer=users.ID ORDER BY `Order` ASC, `Title` ASC");
    while($row = mysql_fetch_assoc($result))
    {
      $plugin = new PlugIn();
      $plugin->ID = $row["id"];
      $plugin->Name = $this->SQLUnEscape($row["Name"]);
      $plugin->Title = $this->SQLUnEscape($row["Title"]);
      $plugin->Developer = $row["Developer"];
      $plugin->DeveloperName = $this->SQLUnEscape($row["DeveloperName"]);
      $plugin->Release = $row["Release"];
      $plugin->ReadAccess = $row["ReadAccess"];
      $plugin->URL = "http://".$_SERVER["SERVER_NAME"]."/plugins/".$plugin->Name."/index.php";
      $plugin->ShowIGB = $row["ShowIGB"];
      $plugin->ShowAdmin = $row["ShowAdmin"];
      $plugin->FileExists = file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin->Name.DIRECTORY_SEPARATOR."index.php");
      if($plugin->FileExists || $plugin->Developer == $this->CurrentUser()->ID || $this->CurrentUser()->AccessRight() == 4)
      {
        $plugins[] = $plugin;
      }
    }
    mysql_free_result($result);
    return $plugins;
  }

  // *******************************************************
  // Returns the plug-in with the given id
  // *******************************************************  
  public function ReadPlugIn($id)
  {
    $result = $this->CoreSQL("SELECT plugins.*, users.Name AS DeveloperName FROM plugins INNER JOIN users ON plugins.Developer=users.ID WHERE plugins.id=".$id);
    if(mysql_num_rows($result) == 0) return false;
    $row = mysql_fetch_assoc($result);
    mysql_free_result($result);

    $plugin = new PlugIn();
    $plugin->ID = $row["id"];
    $plugin->Name = $this->SQLUnEscape($row["Name"]);
    $plugin->Title = $this->SQLUnEscape($row["Title"]);
    $plugin->Developer = $row["Developer"];
    $plugin->DeveloperName = $row["DeveloperName"];
    $plugin->Release = $row["Release"];
    $plugin->ReadAccess = $row["ReadAccess"];
    $plugin->URL = "http://".$_SERVER["SERVER_NAME"]."/plugins/".$plugin->Name."/index.php";
    $plugin->ShowIGB = $row["ShowIGB"];
    $plugin->ShowAdmin = $row["ShowAdmin"];
    $plugin->FileExists = file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$plugin->Name.DIRECTORY_SEPARATOR."index.php");
    
    if($plugin->FileExists || $plugin->Developer == $this->CurrentUser()->ID || $this->CurrentUser()->AccessRight() == 4)
      return $plugin;
    else
      return false;
  }

  // *******************************************************
  // Creates a new plug-in
  // *******************************************************  
  public function NewPlugIn($name, $title, $readaccess, $createfolder = false, $showigb = false, $showadmin = false)
  {
    $this->CoreSQL("INSERT INTO plugins (Name, Title, Developer, ReadAccess, ShowIGB, ShowAdmin) VALUES('".$this->SQLEscape($name)."','".$this->SQLEscape($title)."',".$this->CurrentUser()->ID.",".$readaccess.",".($showigb ? 1 : 0).",".($showadmin ? 1 : 0).")");
    if($createfolder)
    {
      $plugindir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$name;
      mkdir($plugindir, 0770, true);
      
      $contents ="\n<?php\nrequire_once('../../core/core.class.php');";
      $contents .= "\n\$core = new Core();";
      if($readaccess != 0)
      {
        $contents .= "\n\n//Access control";
        $contents .= "\nif(\$core->CurrentUser()->AccessRight() < ".$readaccess.") \$core->Goto('../../php/access.php');";
      }
      $contents .= "\n\n\$core->assign('welcomemessage', 'Hello World');";
      $contents .= "\n\$core->display(\$core->PlugInPath.\"".$name."/".$name.".tpl\");";
      $contents .= "\n?>";
      file_put_contents($plugindir.DIRECTORY_SEPARATOR."index.php", $contents);
      
      $contents = "\n{include file='header.tpl' title='Meridian Dynamics | ".$title."'}";
      $contents .= "\n\n<h3>{\$welcomemessage}</h3>";
      $contents .= "\n\n<p>If you do not have an FTP password, please contact an administrator.</p>";
      $contents .= "\n\n{include file='footer.tpl'}";
      file_put_contents($plugindir.DIRECTORY_SEPARATOR.$name.".tpl", $contents);
    }
    $this->Log("PLUGIN(".$name.") Created.");
  }

  // *******************************************************
  // Edits a plug-in
  // *******************************************************  
  public function EditPlugIn($id, $title, $release, $readaccess, $showigb, $showadmin)
  {
    $this->CoreSQL("UPDATE plugins SET Title='".$this->SQLEscape($title)."', `Release`=".$release.", ReadAccess=".$readaccess.", `ShowIGB`=".($showigb ? 1 : 0).", `ShowAdmin`=".($showadmin ? 1 : 0)." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Assigns a developer to the given plug-in
  // *******************************************************  
  public function AssignPlugInDeveloper($id, $developer)
  {
    $this->CoreSQL("UPDATE plugins SET Developer=".$developer." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Deletes a plug-in
  // *******************************************************  
  public function DeletePlugIn($id, $removefolder = false)
  {
    $result = $this->CoreSQL("SELECT Name FROM plugins WHERE id=".$id);
    $name = $this->SQLUnEscape(mysql_result($result, 0));
    if($removefolder == true)
    {
      $plugindir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$name;
      $this->RemoveDirectory($plugindir);
    }
    $this->CoreSQL("DELETE FROM plugins WHERE id=".$id." LIMIT 1");
    $this->Log("PLUGIN(".$name.") Deleted.");
  }

  // *******************************************************
  // Removes the given directory
  // *******************************************************  
  private function RemoveDirectory($directory)
  {
    // Remove trailing slash
    if(substr($directory,-1) == DIRECTORY_SEPARATOR) $directory = substr($directory, 0, -1);
    
    // Check if the path is valid
    if(!file_exists($directory) || !is_dir($directory)) return;
    if(!is_readable($directory)) return;
    
    // Open the directory
    $handle = opendir($directory);
    
    // Scan directory contents
    while(($item = readdir($handle)) !== FALSE)
    {
      if($item != '.' && $item != '..')
      {
        $path = $directory.DIRECTORY_SEPARATOR.$item;
        if(is_dir($path)) 
          $this->RemoveDirectory($path);
        else
          unlink($path);
      }
    }
    
    // Close the directory handle
    closedir($handle);
    
    // Remove the directory
    rmdir($directory);
  }
 
  // *******************************************************
  // Determines whether a plug-in with the given name exists
  // *******************************************************  
  public function PlugInNameExists($name)
  {
    $result = $this->CoreSQL("SELECT COUNT(*) FROM plugins WHERE Name='".$this->SQLEscape($name)."'");
    if(mysql_result($result, 0) != 0) return true;
      
    $plugindir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR.$name;
    if(file_exists($plugindir)) return true;
    
    return false;
  }
  
  // *******************************************************
  // Reads all XML feeds by all plug-ins
  // *******************************************************  
  public function ReadPlugInFeedbacks()
  {
    global $core;
    $core = $this;
    $path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR;
    $feeds = array();
    $dir = opendir($path);
    while(($file = readdir($dir)) !== false) 
    {
      $plugindir = $path.$file;
      $feedname = $plugindir.DIRECTORY_SEPARATOR.$file.".xml.php";
      if(is_dir($plugindir) && file_exists($feedname))
      {
        ob_start();
        include $feedname;
        $raw = ob_get_clean();
        $xml = new SimpleXMLElement($raw);
        foreach($xml->item as $item)
        {
          if(isset($item->date))
            $date = $item->date;
          else
            $date = $this->GMTTime();
          
          if(isset($item->title))
            $title = $item->title;
          else
            $title = "";
            
          $check = true;
          if(isset($item->user))
            if($this->CurrentUser()->ID != $item->user) $check = false;
          if(isset($item->group))
            if($this->CurrentUser()->AccessRight() < $item->group) $check = false;
          if(empty($title))
            $check = false;
          if($check)
            $feeds[strtotime($item->date)] = array("Date" => $date, "Title" => $title);
        }
      }
    }
    // Article comments
    $names = $this->GetAllUserNames();
    $result = $this->CoreSQL("SELECT t1.id,t2.Date,t2.Author FROM articles AS t1 INNER JOIN articles_comments AS t2 ON t1.id=t2.Article WHERE t1.Author=".$this->CurrentUser()->ID." AND t2.Author!=".$this->CurrentUser()->ID." LIMIT 20");
    while($row = mysql_fetch_assoc($result))
    {
      $feeds[strtotime($row["Date"])] = array("Date" => $row["Date"], "Title" => $names[$row["Author"]]." commented on an <a href='articles.php?read=".$row["id"]."'>article</a> by you.");
    }
    if($this->CurrentUser()->HasPortalRole(User::MDYN_Administrator))
    {
      $result = $this->CoreSQL("SELECT Name,Date FROM feedback LIMIT 20");
      while($row = mysql_fetch_assoc($result))
      {
        $feeds[strtotime($row["Date"])] = array("Date" => $row["Date"], "Title" => $this->SQLUnEscape($row["Name"])." posted a <a href='feedback.php'>feedback</a>.");
      }
    }
    //header('Content-Type: ');
    closedir($dir);
    krsort($feeds);
    return array_values(array_slice($feeds, 0, 20));
  }
  
  // *******************************************************
  // SETTINGS  SETTINGS  SETTINGS  SETTINGS  SETTINGS 
  // SETTINGS  SETTINGS  SETTINGS  SETTINGS  SETTINGS 
  // SETTINGS  SETTINGS  SETTINGS  SETTINGS  SETTINGS 
  // *******************************************************  

  // *******************************************************
  // Returns a portal setting
  // *******************************************************  
  public function GetSetting($name)
  {
    // Global portal settings
    $result = $this->CoreSQL("SELECT `Value` FROM settings WHERE `Name`='".$this->SQLEscape($name)."'");
    if($row = mysql_fetch_assoc($result))
      return $this->SQLUnEscape($row["Value"]);
    else
      return "";
  }
  
  // *******************************************************
  // Sets a portal setting
  // *******************************************************  
  public function SetSetting($name, $value)
  {
    // Global portal settings
    $result = $this->CoreSQL("SELECT COUNT(*) FROM settings WHERE `Name`='".$this->SQLEscape($name)."'");
    if(mysql_result($result, 0) == 1)
      $this->CoreSQL("UPDATE settings SET `Value`='".$this->SQLEscape($value)."' WHERE `Name`='".$this->SQLEscape($name)."'");
    else
      $this->CoreSQL("INSERT INTO settings (`Name`, `Value`) VALUES ('".$this->SQLEscape($name)."', '".$this->SQLEscape($value)."')");
  }

  // *******************************************************
  // Returns the corporation name
  // *******************************************************  
  public function CorporationName()
  {
    return $this->GetSetting["CorporationName"];
  }

  // *******************************************************
  // Returns the alliance name
  // *******************************************************  
  public function AllianceName()
  {
    return $this->GetSetting["AllianceName"];
  }

  // *******************************************************
  // EVE FUNCTIONS  EVE FUNCTIONS  EVE FUNCTIONS 
  // EVE FUNCTIONS  EVE FUNCTIONS  EVE FUNCTIONS 
  // EVE FUNCTIONS  EVE FUNCTIONS  EVE FUNCTIONS 
  // *******************************************************  

  // *******************************************************
  // Reads the game news feed
  // *******************************************************
  public function ReadGameNews()
  {
    return Feed::Read(dirname(__FILE__).DIRECTORY_SEPARATOR."feedcache".DIRECTORY_SEPARATOR."evenews.xml");
  }
  
  // *******************************************************
  // Reads the roleplaying news feed
  // *******************************************************  
  public function ReadRPNews()
  {
    return Feed::Read(dirname(__FILE__).DIRECTORY_SEPARATOR."feedcache".DIRECTORY_SEPARATOR."rpnews.xml");
  }

  // *******************************************************
  // Reads the game dev blog
  // *******************************************************  
  public function ReadDevBlogs()
  {
    return Feed::Read(dirname(__FILE__).DIRECTORY_SEPARATOR."feedcache".DIRECTORY_SEPARATOR."devblogs.xml");
  }

  // *******************************************************
  // Returns tranquility status
  // *******************************************************  
  public function Tranquility()
  {
    return new EVEServer();
  }

  // *******************************************************
  // API  API  API  API  API   API API API API API API API API
  // API  API  API  API  API   API API API API API API API API
  // API  API  API  API  API   API API API API API API API API
  // *******************************************************  
  
  // *******************************************************
  // Performs an API query
  // Additional parameters are given by adding parameter name, value 
  // pairs to the function call. For example:
  // $core->APIQuery("http://api.eve-online.com/char/KillLog.xml.aspx", "beforeKillID", "63");
  // *******************************************************  
  public function APIQuery($url)
  {
    $result = $this->CoreSQL("SELECT APIUserID, AES_DECRYPT(`APIKey`, '??tr3m0r!!".$this->CurrentUser()->ID."') AS APIKeyDec FROM users WHERE id = ".$this->CurrentUser()->ID);
    $row = mysql_fetch_assoc($result);
    $apiuserid = $row["APIUserID"];
    $apikey = $row["APIKeyDec"];
    $charid = $this->CurrentUser()->CharID;
    mysql_free_result($result);
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 1)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query, but allows you to perform the query on a specified portal id account.
  // Additional parameters are given by adding parameter name, value
  // pairs to the function call. For example:
  // $core->APIQuery("http://api.eve-online.com/char/KillLog.xml.aspx", "beforeKillID", "63");
  // *******************************************************
  public function APIQueryAsPortal($url, $portid)
  {
    $result = $this->CoreSQL("SELECT APIUserID, AES_DECRYPT(`APIKey`, '??tr3m0r!!".$portid."') AS APIKeyDec FROM users WHERE id = ".$portid);
    $row = mysql_fetch_assoc($result);
    $apiuserid = $row["APIUserID"];
    $apikey = $row["APIKeyDec"];
    $charid = $this->GetUserFromID($portid)->CharID;
    mysql_free_result($result);
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 2)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query, but allows you to perform the query on a specified portal id account but also allows you to set the charid to use on the portal account.
  // Additional parameters are given by adding parameter name, value
  // pairs to the function call. For example:
  // $core->APIQuery("http://api.eve-online.com/char/KillLog.xml.aspx", $charid, "beforeKillID", "63");
  // *******************************************************
  public function APIQueryAsPortalAsChar($url, $portid, $charid)
  {
    $result = $this->CoreSQL("SELECT APIUserID, AES_DECRYPT(`APIKey`, '??tr3m0r!!".$portid."') AS APIKeyDec FROM users WHERE id = ".$portid);
    $row = mysql_fetch_assoc($result);
    $apiuserid = $row["APIUserID"];
    $apikey = $row["APIKeyDec"];
    mysql_free_result($result);
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 3)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
        echo $postdata;
    }
    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query, but allows a custom charid to be used.
  // Additional parameters are given by adding parameter name, value
  // pairs to the function call. For example:
  // $core->APIQuery("http://api.eve-online.com/char/KillLog.xml.aspx", $charid, "beforeKillID", "63");
  // *******************************************************
  public function APIQueryAsChar($url, $charid)
  {
    $result = $this->CoreSQL("SELECT APIUserID, AES_DECRYPT(`APIKey`, '??tr3m0r!!".$this->CurrentUser()->ID."') AS APIKeyDec FROM users WHERE id = ".$this->CurrentUser()->ID);
    $row = mysql_fetch_assoc($result);
    $apiuserid = $row["APIUserID"];
    $apikey = $row["APIKeyDec"];
    mysql_free_result($result);
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 2)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query. API key must be passed in parameter list.
  // Additional parameters are given by adding parameter name, value
  // pairs to the function call. For example:
  // $core->APIQuery("http://api.eve-online.com/char/KillLog.xml.aspx", "beforeKillID", "63");
  // *******************************************************
  public function APIQueryGeneric($url)
  {
    $postdata = "";
    if(func_num_args() > 1)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= ($i > 1 ? "&" : "").$args[$i]."=".$args[$i + 1];
    }

    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query with director credentials
  // *******************************************************
  public function APIQueryAsDirector($url)
  {
    $charid = $this->GetSetting("DirectorAPICharID");
    $apiuserid = $this->GetSetting("DirectorAPIUserID");
    $apikey = $this->GetSetting("DirectorAPIKey");
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 1)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query with the secondary director credentials
  // *******************************************************
  public function APIQueryAsSecondaryDirector($url)
  {
    $charid = $this->GetSetting("SecondaryDirectorAPICharID");
    $apiuserid = $this->GetSetting("SecondaryDirectorAPIUserID");
    $apikey = $this->GetSetting("SecondaryDirectorAPIKey");
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 1)
    {
      $args = func_get_args();
      for($i = 1; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, 0);
  }

  // *******************************************************
  // Performs an API query - Extended version
  // *******************************************************
  public function APIQueryEx($url, $cache)
  {
    $result = $this->CoreSQL("SELECT APIUserID, AES_DECRYPT(`APIKey`, '??tr3m0r!!".$this->CurrentUser()->ID."') AS APIKeyDec FROM users WHERE id = ".$this->CurrentUser()->ID);
    $row = mysql_fetch_assoc($result);
    $apiuserid = $row["APIUserID"];
    $apikey = $row["APIKeyDec"];
    $charid = $this->CurrentUser()->CharID;
    mysql_free_result($result);
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 2)
    {
      $args = func_get_args();
      for($i = 2; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, $cache);
  }
  
  // *******************************************************
  // Performs an API query with director credentials - Extended version
  // *******************************************************  
  public function APIQueryAsDirectorEx($url, $cache)
  {
    $charid = $this->GetSetting("DirectorAPICharID");
    $apiuserid = $this->GetSetting("DirectorAPIUserID");
    $apikey = $this->GetSetting("DirectorAPIKey");
    $postdata = "CharacterID=".$charid."&userID=".$apiuserid."&apiKey=".$apikey;
    if(func_num_args() > 2)
    {
      $args = func_get_args();
      for($i = 2; $i < count($args); $i = $i + 2)
        $postdata .= "&".$args[$i]."=".$args[$i + 1];
    }
    return $this->APIQueryInternal($url, $postdata, $cache);
  }

  // *******************************************************
  // Performs an API query with the given url
  // $cache determines how caching is handled
  // 0: Returns cache until it expires. Fetches live data when cache expires.
  //    Returns cache on connection errors, empty string if cached data is not found.
  // 1: Always fetches live data bypassing the cache. Returns empty string
  //    if live data is not found.
  // 2: Always returns cached data, never fetches live data even if cache expires.
  //    Returns empty string if cached data is not found.
  // *******************************************************
  private function APIQueryInternal($url, $postdata, $cache = 0)
  {
    $this->APIQueryCount = $this->APIQueryCount + 1;

    $cachedir = dirname(__FILE__).DIRECTORY_SEPARATOR."apicache";
    $cachefile = $cachedir.DIRECTORY_SEPARATOR.md5($url."?".$postdata);
    
    // Check the cache
    if(file_exists($cachefile) && ($cache != 1))
    {
      $raw = file_get_contents($cachefile);
			if(substr($raw, 0, 5) != "<?xml")
			  return "";
				
      if($cache == 2) 
			  return $raw;
			
      try
      {
        $xml = new SimpleXMLElement($raw);
      }
      catch(Exception $e)
      {
        return "";
      }
      $cachedUntil = strtotime($xml->cachedUntil);
      $current = strtotime($this->GMTTime());
      $expired = ($current > $cachedUntil) ? true : false;
      if(!$expired)
        return $raw;
      else
        if($cache == 2) 
          return "";
    }
    else
    {
      if($cache == 2) 
        return "";
    }
    
    // Cache expired; fetch the XML
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $raw = curl_exec($ch);
    curl_close($ch);
    
    if(($raw === FALSE) || (substr($raw, 0, 5) != "<?xml"))
    {
      if(file_exists($cachefile) && ($cache == 0))
        return $cache;
      else
        return "";
    }
    try
    {
      $xml = new SimpleXMLElement($raw);
     }
    catch(Exception $e)
    {
      if($cache == 1) 
        return "";
        
      if(file_exists($cachefile))
        return file_get_contents($cachefile);
    }
    
    // Do not cache if an error is returned
    if ((int)$xml->error['code'] > 0)
      return $raw;

    // Cache the result
    if(!file_exists($cachedir))
      mkdir($cachedir);
    file_put_contents($cachefile, $raw);

    return $raw;
  }

  // *******************************************************
  // Returns the Corporation ID of the given user
  // *******************************************************  
  public function GetCorpIDFromAPI($userid)
  {
    $result = $this->CoreSQL("SELECT CharID, APIUserID, AES_DECRYPT(`APIKey`, '??tr3m0r!!".$userid."') AS APIKeyDec FROM users WHERE id = ".$userid);
    $row = mysql_fetch_assoc($result);
    $apiuserid = $row["APIUserID"];
    $apikey = $row["APIKeyDec"];
    $charid = $row["CharID"];
    mysql_free_result($result);
    
    $raw = $this->APIQueryGeneric("http://api.eve-online.com/account/Characters.xml.aspx", "CharacterID", $charid, "userID" ,$apiuserid, "apiKey", $apikey);
    if(empty($raw)) return 0;
    
    $xml = new SimpleXMLElement($raw);
    if ((int)$xml->error['code'] > 0) return 0;

    foreach($xml->result->rowset->row as $row)
    {
      if((int)$row["characterID"] == (int)$charid)
        return $row["corporationID"];
    }
    
    return 0;
  }
  
  // *******************************************************
  // Returns the character ID of the CEO from the API
  // *******************************************************
  public function GetCEOFromAPI()
  {
    $raw = $this->APIQueryAsDirector("http://api.eve-online.com/corp/CorporationSheet.xml.aspx");
    if(empty($raw)) return;

    $xml = new SimpleXMLElement($raw);
    if ((int)$xml->error['code'] > 0) return;

    return $xml->result->ceoID;
  }
  
  // *******************************************************
  // Returns the skill in training
  // *******************************************************  
  public function GetSkillInTraining()
  {
    if($this->CurrentUser()->IsGuest) return "";
    $raw = $this->APIQuery("http://api.eve-online.com/char/SkillInTraining.xml.aspx");
    if($raw == FALSE) return "";
    
    $xml = new SimpleXMLElement($raw);
    
    if ((int)$xml->error['code'] > 0) return "Error ".$xml->error['code'].": ".$xml->error;
    
    $training = ($xml->result->skillInTraining == 1) ? true : false;
    $starttime = $xml->result->trainingStartTime;
    $endtime = $xml->result->trainingEndTime;
    $skillid = $xml->result->trainingTypeID;
    $tolevel = (int)$xml->result->trainingToLevel;
    $cacheduntil = $xml->cachedUntil;

    if($training && (strtotime($endtime) - strtotime($this->GMTTime()) > 0))
    {
      $romans = array(1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V");
      $result = $this->EveSQL("SELECT typeName FROM invTypes WHERE typeID=".$skillid);
      if(mysql_num_rows($result) == 0) 
        $skillname = "Unknown Skill (TypeID = ".$skillid.")";
      else
        $skillname = mysql_result($result, 0);
      return "<p id='sit_main'>".$skillname." ".$romans[$tolevel]."<br /><span id='sit_timer'>".$this->SecondsToTime(strtotime($endtime) - strtotime($this->GMTTime()))."</span>&nbsp;(".$this->GMTToLocal($endtime).")</p><p><img src='../img/level".$tolevel."_act.gif' /></p><p><span class='info'>(Cached until ".$this->GMTToLocal($cacheduntil).")</span></p>";
    }
    else
      return "<p>There is no skill in training!</p><p><span class='info'>(Skill training information is cached for 15 minutes. Next update on ".$this->GMTToLocal($cacheduntil).").</span></p>";
  }
  
  // *******************************************************
  // Reads all corp members with the in game API
  // *******************************************************  
  public function GetAllCorpMembers()
  {
    $raw = $this->APIQueryAsDirector("http://api.eve-online.com/corp/MemberTracking.xml.aspx");
    if($raw == FALSE) return array();
    $xml = new SimpleXMLElement($raw);
    if ((int)$xml->error['code'] > 0) return array();

    $members = array();
    foreach($xml->result->rowset->row as $atts)
    {
      $members[] = array("CharID" => $atts["characterID"], "Name" => $atts["name"], "StartDate" => $atts["startDateTime"], "Title" => $atts["title"], "LastGameLogin" => $atts["logonDateTime"], "Location" => $atts["location"], "Ship" => $atts["shipType"], "GameRoles" => $atts["roles"]);
    }

    // This part runs the Secondary Corp credentials.  API can be from another corp to allow member access.
    $checksccharid = $this->GetSetting("SecondaryDirectorAPICharID");
    if($checksccharid <> 0)
    {
        $raw2 = $this->APIQueryAsSecondaryDirector("http://api.eve-online.com/corp/MemberTracking.xml.aspx");
        if($raw2 == FALSE) return array();
        $xml2 = new SimpleXMLElement($raw2);
        if ((int)$xml2->error['code'] > 0) return array();

        $members2 = array();
        foreach($xml2->result->rowset->row as $atts2)
        {
          $members[] = array("CharID" => $atts2["characterID"], "Name" => $atts2["name"], "StartDate" => $atts2["startDateTime"], "Title" => $atts2["title"], "LastGameLogin" => $atts2["logonDateTime"], "Location" => $atts2["location"], "Ship" => $atts2["shipType"], "GameRoles" => $atts2["roles"]);
        }
    }
    return $members;
  }
  
  // *******************************************************
  // Gets member corporation game IDs and inserts them into the portal database
  // *******************************************************  
  public function UpdateAllianceMembers()
  {
    // Get alliance name from portal database
    $alliancename = $this->GetSetting("AllianceName");
    if(empty($alliancename)) return false;
    
    // Fetch alliance list from EVE API
    $raw = $this->APIQuery("http://api.eve-online.com/eve/AllianceList.xml.aspx");
    if(empty($raw)) return false;
    $xml = new SimpleXMLElement($raw);
    if ((int)$xml->error['code'] > 0) return false;
    
    // Save blocked list
    $result = $this->CoreSQL("SELECT * FROM corporations WHERE IsBlocked = 1");
    $blocked = array();
    while($row = mysql_fetch_assoc($result))
    {
      $blocked[] = $row["id"];
    }
    
    foreach($xml->result->rowset->row as $alliance)
    {
      if($alliance["name"] == $alliancename)
      {
        // Delete old list
        $this->CoreSQL("DELETE FROM corporations");
        
        $corps = array();
        $exec = (int)$alliance["executorCorpID"];
        foreach($alliance->rowset->row as $corporation)
        {
          $corps[] = $corporation["corporationID"];
        }
        foreach($corps as $corpid)
        {
          $raw = $this->APIQuery("http://api.eve-online.com/corp/CorporationSheet.xml.aspx", "corporationID", $corpid);
          if(empty($raw)) return false;
          $xml = new SimpleXMLElement($raw);
          if ((int)$xml->error['code'] > 0) return false;
          
          // Insert corp details
          $corp = $xml->result;
          $query = "INSERT INTO corporations (`Name`, `CorporationID`, `Ticker`, `CEOID`, `CEOName`, `IsExecutor`) VALUES (";
          $query .= "'".$this->SQLEscape($corp->corporationName)."',";
          $query .= $corp->corporationID.",";
          $query .= "'".$this->SQLEscape($corp->ticker)."',";
          $query .= $corp->ceoID.",";
          $query .= "'".$this->SQLEscape($corp->ceoName)."',";
          $query .= ((int)$corp->corporationID == $exec ? "1" : "0").")";
          $this->CoreSQL($query);
        }
        
        // Update blocked list
        $this->SetBlockedCorporations($blocked);
        return true;
      }
    }
    return false;
  }
  
  // *******************************************************
  // Returns member corporations from the portal database
  // *******************************************************  
  public function GetAllianceMembers($allowedonly = false)
  {
    // Delete old list
    $result = $this->CoreSQL("SELECT * FROM corporations ".($allowedonly ? "WHERE IsBlocked = 0" : "")." ORDER BY IsExecutor DESC, `Name` ASC");
    $corps = array();
    while($row = mysql_fetch_assoc($result))
    {
      $corps[] = array("ID" => $row["id"], 
                    "Name" => $this->SQLUnEscape($row["Name"]), 
                    "CorporationID" => $row["CorporationID"], 
                    "Ticker" =>  $this->SQLUnEscape($row["Ticker"]),
                    "CEOID" => $row["CEOID"], 
                    "CEOName" =>  $this->SQLUnEscape($row["CEOName"]),
                    "IsExecutor" => $row["IsExecutor"], 
                    "IsBlocked" => $row["IsBlocked"]);
    }
    return $corps;
  }

  // *******************************************************
  // Sets the given corp IDs to "blocked" all other corps will be set to
  // unblocked
  // *******************************************************  
  public function SetBlockedCorporations($corps)
  {
    $corps = implode(",",$corps);
    // Clear blocked flag
    $this->CoreSQL("UPDATE corporations SET IsBlocked = (FIND_IN_SET(`id`, '".$corps."') <> 0)");
  }
  
  // *******************************************************
  // USERS  USERS  USERS  USERS  USERS   USERS USERS
  // USERS  USERS  USERS  USERS  USERS   USERS USERS
  // USERS  USERS  USERS  USERS  USERS   USERS USERS
  // *******************************************************  

  // *******************************************************
  // Returns the current user
  // *******************************************************  
  public function CurrentUser()
  {
    @session_start();
    if(isset($_SESSION["user"]))
      return $_SESSION["user"];
    else
    {
      $user = new User();
      return $user;
    }
  }

  // *******************************************************
  // Checks user privileges
  // *******************************************************
  public function AccessCheck($eve_roles = "", $portal_roles = "")
  {
    @session_start;
    $_SESSION["lastpage"] = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

    $user = $this->CurrentUser();
    if($user->IsGuest) return false;
    if(!$user->IsRegistered) return false;
    if($user->IsAlly) return false;

    if(!empty($eve_roles) || !empty($portal_roles))
    {
      if(!is_array($eve_roles)) $eve_roles = array($eve_roles);
      if(!is_array($portal_roles)) $portal_roles = array($portal_roles);

      $check = false;
      foreach($eve_roles as $eve_role)
        if($user->HasEVERole($eve_role)) $check = true;
      foreach($portal_roles as $portal_role)
        if($user->HasPortalRole($portal_role)) $check = true;

      if(!$check) return false;
    }

    unset($_SESSION["lastpage"]);
    return true;
  }

  // *******************************************************
  // Returns the given user's portrait
  // *******************************************************
  public function PortraitFromCharID($charid, $size = 64)
  {
    $path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."portraits".DIRECTORY_SEPARATOR;
    if(!file_exists($path))
      mkdir($path);
    $url = "http://".$_SERVER["SERVER_NAME"]."/portraits/";
    if(file_exists($path.$charid."_".$size.".png")) return $url.$charid."_".$size.".png";
    $file = @file_get_contents("http://img.eve.is/serv.asp?s=".$size."&c=".$charid);
    if($file === FALSE) return $url."0.png";
    if(file_put_contents($path.$charid."_".$size.".png", $file) == FALSE) return $url."0.png";
    return $url.$charid."_".$size.".png";
  }

  // *******************************************************
  // Deletes the current user's portraits (all sizes)
  // *******************************************************
  public function DeletePortrait()
  {
    $path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."portraits".DIRECTORY_SEPARATOR.$this->CurrentUser()->CharID."_*.png";
    foreach (glob($path) as $file)
    {
      unlink($file);
    }
  }

  // *******************************************************
  // Returns a list of user's characters
  // *******************************************************
  public function GetUserCharacters($apiuserid, $apikey, $alts = false)
  {
    $url = "http://api.eve-online.com/account/Characters.xml.aspx?userID=".$apiuserid."&apiKey=".$apikey;
    $raw = @file_get_contents($url);
    if($raw == FALSE) return FALSE;
	  if(substr($raw, 0, 5) != "<?xml") return FALSE;

    $chars = array();
    $xml = new SimpleXMLElement($raw);
    if ((int)$xml->error['code'] > 0) return FALSE;

    foreach($xml->result->rowset->row as $row)
    {
      if(($alts && ($row["characterID"] != $this->CurrentUser()->CharID)) || !$alts)
      {
        $ticker = "";
        $raw2 = $this->APIQueryGeneric("http://api.eve-online.com/corp/CorporationSheet.xml.aspx", "userID", $apiuserid, "apiKey", $apikey, "CharacterID", $row["characterID"]);
        if(!empty($raw2))
        {
          $xml2 = new SimpleXMLElement($raw2);
          if ((int)$xml2->error['code'] == 0)
          {
            $ticker = $xml2->result->ticker;
          }
        }
        $chars[] = array("CharacterID" => $row["characterID"], "Name" => $row["name"], "CorporationName" => $row["corporationName"], "CorporationID" => $row["corporationID"], "CorporationTicker" => $ticker);
      }
    }

    return $chars;
  }

  // *******************************************************
  // Registers a new user account
  // *******************************************************
  public function RegisterNewUser($apiuserid, $apikey, $charid, $charname, $password, $corpid, $corpname, $corpticker)
  {
    // Is guest, ally or member?
    $thiscorpname = $this->GetSetting("CorporationName");
    if($corpname == $thiscorpname)
    {
      $isguest = 0;
      $isally = 0;
    }
    else
    {
      $isguest = 1;
      $isally = 0;
      $corps = $this->GetAllCorporations();
      foreach($corps as $corp)
      {
        if($corp["CorporationID"] == $corpid)
        {
          $isguest = 0;
          $isally = 1;
          break;
        }
      }
    }

    // Check if there is an existing user
    $result = $this->CoreSQL("SELECT COUNT(*) FROM users WHERE CharID=".$charid);
    if(mysql_result($result, 0) != 0)
    {
      $this->Log("Error registering new user: ".$charname.". A user with the same CharID (".$charid.") already exists.");
      return false;
    }

    $query = "INSERT INTO users (Name, CharID, Password, APIUserID, IsGuest, IsAlly, CorporationName, CorporationTicker, CorporationID) VALUES (";
    $query .= "'".$this->SQLEscape($charname)."',";
    $query .= $charid.",";
    $query .= "'".md5($password)."',";
    $query .= $apiuserid.",";
    $query .= $isguest.",";
    $query .= $isally.",";
    $query .= "'".$this->SQLEscape($corpname)."',";
    $query .= "'".$this->SQLEscape($corpticker)."',";
    $query .= $corpid.")";
    $this->CoreSQL($query);
    $result = $this->CoreSQL("SELECT id FROM users WHERE CharID=".$charid." LIMIT 1");
		if(mysql_num_rows($result) != 1) { $this->Log("Error registering new user: ".$charname.". Could not insert user info into the database."); return false; }
		$id = mysql_result($result, 0);
		mysql_free_result($result);
		$this->CoreSQL("UPDATE users SET APIKey = AES_ENCRYPT('".$apikey."', '??tr3m0r!!".$id."') WHERE id = ".$id);
		$this->Log("New user successfully registered: ".$charname.".");
		return true;
  }

  // *******************************************************
  // Registers an alt
  // *******************************************************
  public function RegisterAlt($charname)
  {
    $result = $this->CoreSQL("SELECT Alts FROM users WHERE id=".$this->CurrentUser()->ID." LIMIT 1");
    $alts = mysql_result($result, 0);
    mysql_free_result($result);

    $alts = implode(",", array_filter(array_unique(explode(",", $alts.",".$charname))));
    $this->CoreSQL("UPDATE users SET Alts='".$this->SQLEscape($alts)."' WHERE id = ".$this->CurrentUser()->ID." LIMIT 1");

    @session_start();
    $user = $_SESSION["user"];
    $user->Alts = explode(",", $alts);

    $_SESSION["user"] = $user;
		$this->Log("New alt successfully registered: ".$charname.".");
  }

  // *******************************************************
  // Logs the given user in
  // *******************************************************
  public function Login($username, $password)
  {
    $result = $this->CoreSQL("SELECT * FROM users WHERE (Name = '".$this->SQLEscape($username)."' OR FIND_IN_SET('".$this->SQLEscape($username)."', Alts) ) AND Password='".md5($password)."' AND IsActive = TRUE");

    if(mysql_num_rows($result) == 0) return false;
    $row = mysql_fetch_assoc($result);

    $user = User::FromSQLRow($row);

    @session_start();
    $_SESSION["user"] = $user;
    mysql_free_result($result);

    $this->CoreSQL("UPDATE users SET LastLogin='".$this->GMTTime()."' WHERE id=".$user->ID." LIMIT 1");
    $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? ".".$_SERVER['SERVER_NAME'] : false;
    setcookie("mdyn_portal", md5("?!pl4c3b0!?".$this->SQLEscape($user->Name)), time()+60*60*24*300, "/", $domain);

    return true;
  }

  // *******************************************************
  // Automatically logs the last user
  // *******************************************************
  protected function AutoLogin()
  {
    @session_start();
    if(isset($_SESSION["user"])) return true;

    $key = $_COOKIE["mdyn_portal"];

    $result = $this->CoreSQL("SELECT * FROM users WHERE MD5(CONCAT('?!pl4c3b0!?', Name))='".$key."' AND IsActive = TRUE");

    if(mysql_num_rows($result) == 0)
    {
      $this->Logout();
      return false;
    }
    $row = mysql_fetch_assoc($result);
    $user = User::FromSQLRow($row);

    $_SESSION["user"] = $user;
    mysql_free_result($result);

    $this->CoreSQL("UPDATE users SET LastLogin='".$this->GMTTime()."' WHERE id=".$user->ID." LIMIT 1");
    $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? ".".$_SERVER['SERVER_NAME'] : false;
    setcookie("mdyn_portal", md5("?!pl4c3b0!?".$this->SQLEscape($user->Name)), time()+60*60*24*300, "/", $domain);

    return true;
  }

  // *******************************************************
  // Updates portal roles of given users
  // *******************************************************
  public function UpdateAllUserRoles($users)
  {
    foreach($users as $user)
    {
      $this->CoreSQL("UPDATE users SET PortalRoles=".$user->PortalRoles." WHERE id=".$user->ID." LIMIT 1");
      if($user->ID == $this->CurrentUser()->ID)
      {
        @session_start();
        $cuser = $_SESSION["user"];
        $cuser->PortalRoles = $user->PortalRoles;
        $_SESSION["user"] = $cuser;
      }
    }
  }

  // *******************************************************
  // Bans/unbans user accounts
  // *******************************************************
  public function UpdateBannedUsers($users)
  {
    foreach($users as $user)
    {
      $this->CoreSQL("UPDATE users SET IsActive=".($user->IsActive ? 1 : 0)." WHERE id=".$user->ID." LIMIT 1");
    }
  }

  // *******************************************************
  // Logs the current user out
  // *******************************************************
  public function Logout()
  {
    @session_start();
    unset($_SESSION["user"]);
    setcookie("mdyn_portal", "", time() - 3600);
    $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? ".".$_SERVER['SERVER_NAME'] : false;
    setcookie("mdyn_portal", "", time() - 3600, "/", $domain);
    unset($_COOKIE["mdyn_portal"]);
  }

  // *******************************************************
  // Updates roles and titles of all users
  // *******************************************************  
  public function UpdateAllUsers()
  {
    $members = $this->GetAllCorpMembers(); // From API. Returns an array(CharID, Name, StartDate, Title, LastGameLogin, Location, Ship, Roles)
    $ceoid = $this->GetCEOFromAPI(); // From API. CEO character ID
    $corps = $this->GetAllCorporations(true); // From DB. Returns an array(CorporationID, Name, Ticker) of allowed corps.
    $users = $this->GetAllUsers(false, false); // From DB. Registered users.

    if(empty($members) || empty($ceoid))
      return false;
    else
    {
      $res = array();
      foreach($users as $user)
      {
        $corpid = $this->GetCorpIDFromAPI($user->ID);
        $corpmember = false;
        foreach($members as $member)
        {
          if(($member["CharID"] == $user->CharID))
          {
            $corpmember = true;
            break;
          }
        }
        if($corpmember)
        {
          $user->IsAlly = false;
          if($user->IsGuest == true || $user->IsAlly == true)
          {
            $user->IsGuest = false;
            $res[] = array($user->Name, "Promoted to Member");
          }

          if($user->IsCEO() && ($user->CharID != $ceoid))
          {
            if($user->HasPortalRole(User::MDYN_CEO) == true) $user->PortalRoles = BigNumber::Subtract($user->PortalRoles, User::MDYN_CEO);
            $user->Title = $member["Title"];
            $res[] = array($user->Name, "Demoted from CEO");
          }
          elseif(!$user->IsCEO() && ($user->CharID == $ceoid))
          {
            if($user->HasPortalRole(User::MDYN_CEO) == false) $user->PortalRoles = BigNumber::Add($user->PortalRoles, User::MDYN_CEO);
            $user->Title = $member["Title"];
            $res[] = array($user->Name, "Promoted to CEO");
          }
          
          if($user->Title != $member["Title"])
          {
            $user->Title = $member["Title"];
            if(empty($user->Title))
              $res[] = array($user->Name, "Title Removed");
            else
              $res[] = array($user->Name, "Title Changed to ".$user->Title);
          }
          
          if(BigNumber::Compare($user->EVERoles, $member["GameRoles"]) != 0)
          {
            if($user->HasEVERole(User::EVE_Director) == true && BigNumber::Compare(BigNumber::BitwiseAnd($member["GameRoles"], User::EVE_Director), "0") == 0)
              $res[] = array($user->Name, "Demoted from Director");
            elseif($user->HasEVERole(User::EVE_Director) == false && BigNumber::Compare(BigNumber::BitwiseAnd($member["GameRoles"], User::EVE_Director), "0") != 0)
              $res[] = array($user->Name, "Promoted to Director");
            else
              $res[] = array($user->Name, "Roles Changed");

            $user->EVERoles = $member["GameRoles"];
          }
        }
        else
        {
          $rolestokeep = "0";
          if($user->HasPortalRole(User::MDYN_HonoraryMember))
          {
            $rolestokeep = BigNumber::Add($rolestokeep, User::MDYN_HonoraryMember);
          }
          elseif($user->HasPortalRole(User::MDYN_AllyLeader))
          {
            $rolestokeep = BigNumber::Add($rolestokeep, User::MDYN_AllyLeader);
          }
          $user->Title = "";
          $user->EVERoles = "0";
          $user->PortalRoles = $rolestokeep;
          
          $ally = false;
          foreach($corps as $corp)
          {
            if($corp["CorporationID"] == $corpid)
            {
              $ally = true;
              break;
            }
          }
          if($ally)
          {
            if($user->IsAlly == false)
            {
              $user->IsGuest = false;
              $user->IsAlly = true;
              $res[] = array($user->Name, "Changed to Ally");
            }
          }
          else
          {
            if($user->IsAlly == true)
            {
              $user->IsGuest = true;
              $user->IsAlly = false;
              $res[] = array($user->Name, "Demoted to Guest from Ally");
            }
          }
          
          if((!$ally) && ($user->IsGuest == false))
          {
            $user->IsGuest = true;
            $user->IsAlly = false;
            $res[] = array($user->Name, "Demoted to Guest");
          }
        }
        
        // Check corporation
        $raw = $this->APIQuery("http://api.eve-online.com/corp/CorporationSheet.xml.aspx", "CorporationID", $corpid);
        if(!empty($raw))
        {
          $xml = new SimpleXMLElement($raw);
          $corpname = $xml->result->corporationName;
          $corpticker = $xml->result->ticker;
          if(($corpid != $user->CorporationID) || ($corpname != $user->CorporationName) || ($corpticker != $user->CorporationTicker))
          {
            $user->CorporationID = $corpid;
            $user->CorporationName = $corpname;
            $user->CorporationTicker = $corpticker;          
            $res[] = array($user->Name, "Updated Corporation Info");
          }
        }
      }

      // Update database
      foreach($users as $user)
      {
        if($user->ID != 3) // This is to prevent updating the Corporate Dummy Account.
        {
            $this->CoreSQL("UPDATE users SET CorporationID=".$user->CorporationID.", CorporationName='".$this->SQLEscape($user->CorporationName)."', CorporationTicker='".$this->SQLEscape($user->CorporationTicker)."', IsAlly=".($user->IsAlly ? 1 : 0).", IsGuest=".($user->IsGuest ? 1 : 0).", Title='".$this->SQLEscape($user->Title)."', EVERoles=".$user->EVERoles.", PortalRoles=".$user->PortalRoles." WHERE id=".$user->ID." LIMIT 1");
        }
        if($user->ID == $this->CurrentUser()->ID)
        {
          @session_start();
          $cuser = $_SESSION["user"];
          $cuser->IsAlly = $user->IsAlly;
          $cuser->IsGuest = $user->IsGuest;
          $cuser->Title = $user->Title;
          $cuser->EVERoles = $user->EVERoles;
          $cuser->PortalRoles = $user->PortalRoles;
          $cuser->CorporationID = $user->CorporationID;
          $cuser->CorporationName = $user->CorporationName;
          $cuser->CorporationTicker = $user->CorporationTicker;
      
          $_SESSION["user"] = $cuser;
        }
      }
      return $res;
    }  
  }
  
  // *******************************************************
  // Updates the date/time the current user last visited a page
  // *******************************************************  
  public function UpdateLastPageVisit()
  {
    $this->CoreSQL("UPDATE users SET LastPageVisit='".$this->GMTTime()."' WHERE id=".$this->CurrentUser()->ID." LIMIT 1");
  }
  
  // *******************************************************
  // Returns a list of the names of registered users
  // *******************************************************  
  public function GetRegisteredUserNames()
  {
    $result = $this->CoreSQL("SELECT id, Name FROM users WHERE IsActive = TRUE ORDER BY Name ASC");
    
    $chars = array();
    while($row = mysql_fetch_assoc($result))
    {
      $chars[$row["id"]] = $this->SQLUnEscape($row["Name"]);
    }
    
    mysql_free_result($result);

    return $chars;
  }
  
  // *******************************************************
  // Returns a list of the names of all users
  // *******************************************************  
  public function GetAllUserNames()
  {
    $result = $this->CoreSQL("SELECT id, Name FROM users WHERE Name <> 'Guest' ORDER BY Name ASC");
    
    $chars = array();
    while($row = mysql_fetch_assoc($result))
    {
      $chars[$row["id"]] = $this->SQLUnEscape($row["Name"]);
    }
    
    mysql_free_result($result);

    return $chars;
  }
  
  // *******************************************************
  // Returns a list of allied corporations
  // *******************************************************  
  public function GetAllCorporations()
  {
    $result = $this->CoreSQL("SELECT id, CorporationID, Name, Ticker FROM corporations WHERE IsBlocked = 0");
    
    $corps = array();
    while($row = mysql_fetch_assoc($result))
    {
      $corps[$row["id"]] = array("CorporationID" => $row["CorporationID"], "Name" => $this->SQLUnEscape($row["Name"]), "Ticker" => $this->SQLUnEscape($row["Ticker"]));
    }

    mysql_free_result($result);

    return $corps;
  }

  // *******************************************************
  // Returns a list of all users
  // *******************************************************
  public function GetAllUsers($corponly = false, $activeonly = false)
  {
    $query = "SELECT * FROM users";
    if($corponly == true && $activeonly == false)
      $query .= " WHERE id != 3 AND IsGuest = FALSE AND IsAlly = FALSE";
    elseif($corponly == false && $activeonly == true)
      $query .= " WHERE id != 3 AND IsActive = TRUE";
    elseif($corponly == true && $activeonly == true)
      $query .= " WHERE id != 3 AND IsGuest = FALSE AND IsAlly = FALSE AND IsActive = TRUE";
    elseif($corponly == false && $activeonly == false)
      $query .= " WHERE id != 3";
    $query .= " ORDER BY Name ASC";
    $result = $this->CoreSQL($query);

    $users = array();
    while($row = mysql_fetch_assoc($result))
    {
      $user = User::FromSQLRow($row);
      if($user->Name != "Guest")
        $users[] = $user;
    }

    mysql_free_result($result);

    return $users;
  }

  // *******************************************************
  // Returns a list of all users. Does not show Dummy Account
  // *******************************************************
  public function GetAllUsersSpecial($corponly = false, $activeonly = false)
  {
    $query = "SELECT * FROM users";
    if($corponly == true && $activeonly == false)
      $query .= " WHERE IsGuest = FALSE AND IsAlly = FALSE";
    elseif($corponly == false && $activeonly == true)
      $query .= " WHERE IsActive = TRUE";
    elseif($corponly == true && $activeonly == true)
      $query .= " WHERE IsGuest = FALSE AND IsAlly = FALSE AND IsActive = TRUE";
    $query .= " ORDER BY Name ASC";
    $result = $this->CoreSQL($query);

    $users = array();
    while($row = mysql_fetch_assoc($result))
    {
      $user = User::FromSQLRow($row);
      if($user->Name != "Guest")
        $users[] = $user;
    }

    mysql_free_result($result);

    return $users;
  }

  // *******************************************************
  // Returns a list of user name password hashes
  // *******************************************************  
  public function GetLoginDB()
  {
    $result = $this->CoreSQL("SELECT id, Name, Password FROM users WHERE IsGuest = FALSE AND IsActive = TRUE");
    
    $users = array();
    while($row = mysql_fetch_assoc($result))
    {
      $users[] = array("ID" => $row["id"], "Name" => $this->SQLUnEscape($row["Name"]), "PasswordHash" => $row["Password"]);
    }
    
    mysql_free_result($result);
    return $users;
  }
  
  // *******************************************************
  // Determines whether a character with the given Character ID exists
  // *******************************************************
  public function CharacterIDExists($charid)
  {
    $result = $this->CoreSQL("SELECT id FROM users WHERE CharID = ".$charid);
    
    if(mysql_num_rows($result) == 0) return false;

    mysql_free_result($result);
    return true;
  }
  
  // *******************************************************
  // Returns the user with the given ID
  // *******************************************************  
  public function GetUserFromID($id)
  {
    $result = $this->CoreSQL("SELECT * FROM users WHERE id = ".$id);
    
    if(mysql_num_rows($result) == 0) return false;
    $row = mysql_fetch_assoc($result);
    
    $user = User::FromSQLRow($row);
    mysql_free_result($result);

    return $user;
  }

  // *******************************************************
  // Returns the user with the given Character ID
  // *******************************************************  
  public function GetUserFromCharID($charid)
  {
    $result = $this->CoreSQL("SELECT * FROM users WHERE CharID = ".$charid);
    
    if(mysql_num_rows($result) == 0) return false;
    $row = mysql_fetch_assoc($result);
    
    $user = User::FromSQLRow($row);
    mysql_free_result($result);

    return $user;
  }

  // *******************************************************
  // Returns the names of users who were online at most 5 mins ago
  // *******************************************************  
  public function GetOnlineCharacters()
  {
    $cutoffdate = date("Y-m-d H:i:s", mktime(gmdate("H"), gmdate("i") - 5, gmdate("s"), gmdate("m"), gmdate("d"), gmdate("Y")));
    $result = $this->CoreSQL("SELECT * FROM users WHERE LastPageVisit>='".$cutoffdate."' AND Name!='Guest'");
    
    if(mysql_num_rows($result) == 0) return array();
    $users = array();
    while($row = mysql_fetch_assoc($result))
    {
      $users[$row["id"]] = $this->SQLUnEscape($row["Name"]);
    }
    mysql_free_result($result);

    return $users;
  }

  // *******************************************************
  // Edits user info
  // *******************************************************  
  public function EditUserInfo($timezone, $email, $im, $birthdate, $location)
  {
    if(preg_match("/\d\d\d\d-\d\d-\d\d/", $birthdate) == 0) $birthdate = "0000-00-00";

    @session_start();
    $user = $_SESSION["user"];
    $user->TimeZone = $timezone;
    $user->Email = $email;
    $user->IM = $im;
    $user->BirthDate = $birthdate;
    $user->Location = $location;
    
    $this->CoreSQL("UPDATE users SET TimeZone=".$timezone.", Email='".$this->SQLEscape($email)."', IM='".$this->SQLEscape($im)."', Birthdate='".$birthdate."', Location='".$this->SQLEscape($location)."' WHERE id=".$user->ID." LIMIT 1");
    
    $_SESSION["user"] = $user;
  }

  // *******************************************************
  // Edits user API info
  // *******************************************************  
  public function EditUserAPIInfo($apiuserid, $apikey)
  {
    $this->CoreSQL("UPDATE users SET APIUserID=".$apiuserid.", APIKey=AES_ENCRYPT('".$apikey."', '??tr3m0r!!".$this->CurrentUser()->ID."') WHERE id=".$this->CurrentUser()->ID." LIMIT 1");
  }

  // *******************************************************
  // Edits user portal settings
  // *******************************************************  
  public function EditUserPortalSettings($settings, $dateformat)
  {
    @session_start();
    $user = $_SESSION["user"];
    $user->PortalSettings = $settings;
    $user->DateFormat = $dateformat;
    
    $this->CoreSQL("UPDATE users SET PortalSettings=".$settings.", DateFormat='".$dateformat."' WHERE id=".$user->ID." LIMIT 1");
    
    $_SESSION["user"] = $user;
  }

  // *******************************************************
  // Edits user info
  // *******************************************************  
  public function EditUserRLStatus($isoop, $oopuntil, $oopnote)
  {
    if(!$isoop)
    {
      $oopuntil = "0000-00-00";
      $oopnote = "";
    }
    
    @session_start();
    $user = $_SESSION["user"];
    $user->IsOOP = $isoop;
    $user->OOPUntil = $oopuntil;
    $user->OOPNote = $oopnote;
    
    $this->CoreSQL("UPDATE users SET OOPUntil='".$oopuntil."', OOPNote='".$this->SQLEscape($oopnote)."' WHERE id=".$user->ID." LIMIT 1");
    
    $_SESSION["user"] = $user;
  }
  
  // *******************************************************
  // Edits users password
  // *******************************************************  
  public function EditUserPassword($password)
  {
    $this->CoreSQL("UPDATE users SET Password='".md5($password)."' WHERE id=".$this->CurrentUser()->ID." LIMIT 1");
  }

  // *******************************************************
  // Edits users alts
  // *******************************************************  
  public function EditUserAlts($alts)
  {
    $this->CoreSQL("UPDATE users SET Alts='".implode(",", $this->SQLEscape($alts))."' WHERE id=".$this->CurrentUser()->ID." LIMIT 1");
  }

  // *******************************************************
  // Edits user signature
  // *******************************************************  
  public function EditUserSignature($signature)
  {
    @session_start();
    $user = $_SESSION["user"];
    $user->Signature = $signature;
    
    $this->CoreSQL("UPDATE users SET Signature='".$this->SQLEscape($signature)."' WHERE id=".$user->ID." LIMIT 1");
    
    $_SESSION["user"] = $user;
  }
  
  // *******************************************************
  // SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL
  // SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL 
  // SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL  SQL 
  // *******************************************************  

  // *******************************************************
  // Runs a SQL query on the core database
  // *******************************************************  
  public function CoreSQL($query)
  {
    $starttime = microtime(true);
    
    $this->QueryCount = $this->QueryCount + 1;
    // Connect to database
    $this->CoreLink = mysql_connect($this->CoreDBServer, $this->CoreDBUser, $this->CoreDBPass) or die('Could not connect: ' . mysql_error());
    mysql_select_db($this->CoreDBName, $this->CoreLink) or die('Could not select database: '.$this->CoreDBName);

    // Run the query
    $result = @mysql_query($query, $this->CoreLink) or die('Query failed: ' . mysql_error());
    
    $endtime = microtime(true);
    $this->QueryTime = $this->QueryTime + round($endtime - $starttime, 4);
    
    return $result;
  }

  // *******************************************************
  // Runs a SQL query on the plugin database
  // *******************************************************  
  public function SQL($query)
  {
    $starttime = microtime(true);
    $this->QueryCount = $this->QueryCount + 1;
    // Connect to database
    $this->PluginLink = mysql_connect($this->PluginDBServer, $this->PluginDBUser, $this->PluginDBPass) or die('Could not connect: ' . mysql_error());
    mysql_select_db($this->PluginDBName, $this->PluginLink) or 
die('Could not select 
database: '.$this->PluginDBName);
    
    // Run the query
    $result = @mysql_query($query, $this->PluginLink) or die('Query failed: ' . mysql_error());
    
    $endtime = microtime(true);
    $this->QueryTime = $this->QueryTime + round($endtime - $starttime, 4);
    
    return $result;
  }

  // *******************************************************
  // Runs a SQL query on the eve database
  // *******************************************************  
  public function EveSQL($query)
  {
    $starttime = microtime(true);
    $this->QueryCount = $this->QueryCount + 1;
    // Connect to database
    if(!$this->EveLink)
    {
      $this->EveLink = mysql_connect($this->EveDBServer, $this->EveDBUser, $this->EveDBPass) or die('Could not connect: ' . mysql_error());
      mysql_select_db($this->EveDBName) or die('Could not select database: '.$this->EveDBName);
    }
    
    // Run the query
    $result = @mysql_query($query, $this->EveLink) or die('Query failed: ' . mysql_error());
    
    $endtime = microtime(true);
    $this->QueryTime = $this->QueryTime + round($endtime - $starttime, 4);
    
    return $result;
  }

  // *******************************************************
  // Escapes the given string for use in SQL queries
  // *******************************************************  
  public function SQLEscape($string)
  {
    $this->CoreSQL("SELECT 1+1");
    return mysql_real_escape_string($string);
  }

  // *******************************************************
  // Corrects escaped strings
  // *******************************************************  
  public function SQLUnEscape($string)
  {
    return str_ireplace(array('\"', "\'", "\\\\"), array('"', "'", "\\"), $string);
  }
    
  // *******************************************************
  // ARTICLES  ARTICLES  ARTICLES  ARTICLES  ARTICLES  
  // ARTICLES  ARTICLES  ARTICLES  ARTICLES  ARTICLES  
  // ARTICLES  ARTICLES  ARTICLES  ARTICLES  ARTICLES  
  // *******************************************************  

  // *******************************************************
  // Reads an article from the core database
  // *******************************************************  
  public function ReadArticle($id)
  {
    $result = $this->CoreSQL("SELECT articles.*, users.Name AS AuthorName, users.Signature AS Signature FROM articles INNER JOIN users ON articles.Author=users.ID WHERE articles.id=".$id." AND (Author=".$this->CurrentUser()->ID." OR ReadAccess<=".$this->CurrentUser()->AccessRight().")");
    
    if(mysql_num_rows($result) == 0) return false;
    $row = mysql_fetch_assoc($result);
    
    $article = new Article();
    $article->ID = $row["id"];
    $article->Date = $this->GMTToLocal($row["Date"]);
    $article->Title = $this->SQLUnEscape($row["Title"]);
    $article->Author = $row["Author"];
    $article->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
    $article->AuthorSignature = $this->SQLUnEscape($row["Signature"]);
    $article->ReadAccess = $row["ReadAccess"];
    $article->WriteAccess = $row["WriteAccess"];
    $article->Text = $this->SQLUnEscape($row["Text"]);
    $article->IsHidden = $row["IsHidden"];

    mysql_free_result($result);
    
    $result = $this->CoreSQL("SELECT articles_comments.*, users.Name AS AuthorName, users.Signature AS Signature FROM articles_comments INNER JOIN users ON articles_comments.Author=users.ID WHERE Article=".$id);
    $comments = array();
    while($row = mysql_fetch_assoc($result))
      $comments[] = array("ID" => $row["id"], "Date" => $this->GMTToLocal($row["Date"]), "Author" => $row["Author"], "AuthorName" => $this->SQLUnEscape($row["AuthorName"]), "Signature" => $this->SQLUnEscape($row["Signature"]), "Text" => $this->SQLUnEscape($row["Comment"]));
    $article->Comments = $comments;
    
    return  $article;
  }

  // *******************************************************
  // Returns the number of articles in the current user can access
  // *******************************************************  
  public function GetArticleCount()
  {
    $result = $this->CoreSQL("SELECT COUNT(*) FROM articles WHERE IsHidden=FALSE AND (Author=".$this->CurrentUser()->ID." OR ReadAccess<=".$this->CurrentUser()->AccessRight().")");
    
    $count = mysql_result($result, 0);
    
    mysql_free_result($result);
    return $count;
  }
  
  // *******************************************************
  // Returns an array of article titles
  // *******************************************************  
  public function GetArticleTitles()
  {
    $result = $this->CoreSQL("SELECT articles.id, articles.Title, articles.Date, users.Name AS AuthorName FROM articles INNER JOIN users ON articles.Author = users.id WHERE IsHidden=FALSE AND (Author=".$this->CurrentUser()->ID." OR ReadAccess<=".$this->CurrentUser()->AccessRight().") ORDER BY Title ASC");
    
    $articles = array();
    while($row = mysql_fetch_assoc($result))
    {
      $articles[] = array("ID" => $row["id"], "Date" => $this->GMTToLocal($row["Date"]), "Title" => $this->SQLUnEscape($row["Title"]), "AuthorName" => $this->SQLUnEscape($row["AuthorName"]));
    }
    
    mysql_free_result($result);
    return $articles;
  }
  
  // *******************************************************
  // Deletes an article
  // *******************************************************  
  public function DeleteArticle($id)
  {
    $result = $this->CoreSQL("SELECT Title FROM articles WHERE id=".$id." LIMIT 1");
    $title = $this->SQLUnEscape(mysql_result($result, 0));
    $this->CoreSQL("DELETE FROM articles WHERE (Author=".$this->CurrentUser()->ID." OR WriteAccess<=".$this->CurrentUser()->AccessRight().") AND IsHidden=FALSE AND id=".$id." LIMIT 1");
    $this->CoreSQL("DELETE FROM articles_comments WHERE Article=".$id);
		$this->Log("Article deleted: ".$title.".");
  }

  // *******************************************************
  // Inserts an article
  // *******************************************************  
  public function NewArticle($title, $text, $readaccess, $writeaccess)
  {
    $this->CoreSQL("INSERT INTO articles (Date, Author, Title, Text, ReadAccess, WriteAccess) VALUES ('".$this->GMTTime()."', ".$this->CurrentUser()->ID.", '".$this->SQLEscape($title)."', '".$this->SQLEscape($text)."', ".$readaccess.", ".$writeaccess.")");
		$this->Log("New article: ".$title.".");
  }

  // *******************************************************
  // Edits the given article
  // *******************************************************  
  public function EditArticle($id, $title, $text, $readaccess, $writeaccess)
  {
    $this->CoreSQL("UPDATE articles SET Title='".$this->SQLEscape($title)."', Text='".$this->SQLEscape($text)."', ReadAccess=".$readaccess.", WriteAccess=".$writeaccess." WHERE (Author=".$this->CurrentUser()->ID." OR WriteAccess<=".$this->CurrentUser()->AccessRight().") AND id=".$id." LIMIT 1");
  }
  
  // *******************************************************
  // Inserts an article
  // *******************************************************  
  public function NewArticleComment($article, $text)
  {
    $this->CoreSQL("INSERT INTO articles_comments (Date, Article, Author, Comment) VALUES ('".$this->GMTTime()."', ".$article.", ".$this->CurrentUser()->ID.", '".$this->SQLEscape($text)."')");
  }
  
  // *******************************************************
  // Inserts an article
  // *******************************************************  
  public function DeleteArticleComment($id)
  {
    $this->CoreSQL("DELETE FROM articles_comments WHERE id=".$id." LIMIT 1");
  }
  
  // *******************************************************
  // NEWS  NEWS  NEWS  NEWS  NEWS
  // NEWS  NEWS  NEWS  NEWS  NEWS
  // NEWS  NEWS  NEWS  NEWS  NEWS
  // *******************************************************  

  // *******************************************************
  // Reads a news item from the core database
  // *******************************************************  
  public function ReadNewsItem($id)
  {
    $result = $this->CoreSQL("SELECT news.*, users.Name FROM news INNER JOIN users ON news.Author = users.id WHERE news.id = ".$id." LIMIT 1");
    
    if(mysql_num_rows($result) == 0) return array();
    $row = mysql_fetch_assoc($result);
    
    $newsitem = new NewsItem();
    $newsitem->ID = $row["id"];
    $newsitem->Date = $row["Date"];
    $newsitem->Title = $this->SQLUnEscape($row["Title"]);
    $newsitem->Author = $row["Author"];
    $newsitem->AuthorName = $this->SQLUnEscape($row["Name"]);
    $newsitem->ReadAccess = $row["ReadAccess"];
    $newsitem->Text = $this->SQLUnEscape($row["Text"]);
    
    mysql_free_result($result);
    return $newsitem;
  }

  // *******************************************************
  // Inserts a news item to the core database
  // *******************************************************  
  public function InsertNewsItem($title, $text, $readaccess)
  {
    $this->CoreSQL("INSERT INTO news (Date, Author, Title, Text, ReadAccess) VALUES ('".$this->GMTTime()."', ".$this->CurrentUser()->ID.", '".$this->SQLEscape($title)."', '".$this->SQLEscape($text)."', ".$readaccess.")");    
		$this->Log("New news item: ".$title.".");
  }

  // *******************************************************
  // Edits the given news item
  // *******************************************************  
  public function EditNewsItem($id, $title, $text, $readaccess)
  {
    $this->CoreSQL("UPDATE news SET Title='".$this->SQLEscape($title)."', Text='".$this->SQLEscape($text)."', ReadAccess=".$readaccess." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Deletes a news item
  // *******************************************************
  public function DeleteNewsItem($id)
  {
    $result = $this->CoreSQL("SELECT Title FROM news WHERE id=".$id." LIMIT 1");
    $title = $this->SQLUnEscape(mysql_result($result, 0));
    $this->CoreSQL("DELETE FROM news WHERE id=".$id." LIMIT 1");
		$this->Log("News item deleted: ".$title.".");
  }

  // *******************************************************
  // Reads recent news from the core database
  // *******************************************************  
  public function ReadNews()
  {
    $result = $this->CoreSQL("SELECT news.*, users.Name FROM news INNER JOIN users ON news.Author = users.id WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." ORDER BY Date DESC LIMIT ".$this->GetSetting("NewsLimit"));

    if(mysql_num_rows($result) == 0) return array();
    $news = array();
    while($row = mysql_fetch_assoc($result))
    {
      $newsitem = new NewsItem();
      $newsitem->ID = $row["id"];
      $newsitem->Date = $row["Date"];
      $newsitem->Title = $this->SQLUnEscape($row["Title"]);
      $newsitem->Author = $row["Author"];
      $newsitem->AuthorName = $this->SQLUnEscape($row["Name"]);
      $newsitem->ReadAccess = $row["ReadAccess"];
      $newsitem->Text = $this->SQLUnEscape($row["Text"]);
      $news[] = $newsitem;
    }
    mysql_free_result($result);
    return  $news;
  }
  
  // *******************************************************
  // Reads all news from the core database
  // *******************************************************  
  public function ReadAllNews()
  {
    $result = $this->CoreSQL("SELECT news.*, users.Name FROM news INNER JOIN users ON news.Author = users.id WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." ORDER BY Date DESC ");
    
    if(mysql_num_rows($result) == 0) return array();
    $news = array();
    while($row = mysql_fetch_assoc($result))
    {
      $newsitem = new NewsItem();
      $newsitem->ID = $row["id"];
      $newsitem->Date = $row["Date"];
      $newsitem->Title = $this->SQLUnEscape($row["Title"]);
      $newsitem->Author = $row["Author"];
      $newsitem->AuthorName = $this->SQLUnEscape($row["Name"]);
      $newsitem->ReadAccess = $row["ReadAccess"];
      $newsitem->Text = $this->SQLUnEscape($row["Text"]);
      $news[] = $newsitem;
    }
    mysql_free_result($result);
    return  $news;
  }

  // *******************************************************
  // CALENDAR  CALENDAR  CALENDAR  CALENDAR  CALENDAR
  // CALENDAR  CALENDAR  CALENDAR  CALENDAR  CALENDAR
  // CALENDAR  CALENDAR  CALENDAR  CALENDAR  CALENDAR
  // *******************************************************  

  // *******************************************************
  // Inserts a calendar entry to the core database
  // *******************************************************  
  public function InsertCalendarEntry($date, $title, $text, $readaccess)
  {
    $this->CoreSQL("INSERT INTO calendar (Date, Author, Title, Text, ReadAccess) VALUES ('".$date."', ".$this->CurrentUser()->ID.", '".$this->SQLEscape($title)."', '".$this->SQLEscape($text)."', ".$readaccess.")");    
		$this->Log("New calendar entry: ".$title.".");
  }

  // *******************************************************
  // Edits the given calendar event
  // *******************************************************  
  public function EditCalendarEntry($id, $date, $title, $text, $readaccess)
  {
    $this->CoreSQL("UPDATE calendar SET Date='".$date."', Title='".$this->SQLEscape($title)."', Text='".$this->SQLEscape($text)."', ReadAccess=".$readaccess." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Inserts a calendar entry to the core database
  // *******************************************************  
  public function DeleteCalendarEntry($id)
  {
    $result = $this->CoreSQL("SELECT Title FROM calendar WHERE id=".$id." LIMIT 1");
    $title = $this->SQLUnEscape(mysql_result($result, 0));
    $this->CoreSQL("DELETE FROM calendar WHERE id=".$id." LIMIT 1");
		$this->Log("Calendar entry deleted: ".$title.".");
  }
  
  // *******************************************************
  // Signs-ups the current user to the given calendar entry
  // *******************************************************  
  public function SignUpToCalendarEntry($id)
  {
    $result = $this->CoreSQL("SELECT signups FROM calendar WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND Date>='".$this->GMTTime()."' AND id=".$id." LIMIT 1");
    $signups = mysql_result($result, 0);
    mysql_free_result($result);
    
    $signups = implode(",", array_filter(array_unique(explode(",", $signups.",".$this->CurrentUser()->ID))));
    
    $this->CoreSQL("UPDATE calendar SET Signups='".$signups."' WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Reads upcoming calendar entries from the core database
  // *******************************************************
  public function ReadCalendar()
  {
    $users = $this->GetRegisteredUserNames();
    $result = $this->CoreSQL("SELECT calendar.*, users.Name FROM calendar INNER JOIN users ON calendar.Author = users.id WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND Date>='".$this->GMTTime()."' ORDER BY Date ASC");

    if(mysql_num_rows($result) == 0) return array();
    $items = array();
    while($row = mysql_fetch_assoc($result))
    {
      $item = new Calendar();
      $item->ID = $row["id"];
      $item->Date = $row["Date"];
      $item->Title = $this->SQLUnEscape($row["Title"]);
      $item->Author = $row["Author"];
      $item->AuthorName = $this->SQLUnEscape($row["Name"]);
      $item->ReadAccess = $row["ReadAccess"];
      $item->Text = $this->SQLUnEscape($row["Text"]);
      $signups = array();
      foreach(explode(",",$row["Signups"]) as $signup)
        if(!empty($signup)) $signups[] = $users[$signup];
      $item->Signups = $signups;
      $items[] = $item;
    }
    mysql_free_result($result);
    return $items;
  }

  // *******************************************************
  // Reads all calendar entries from the core database
  // *******************************************************
  public function ReadCalendarAll()
  {
    $users = $this->GetRegisteredUserNames();
    $result = $this->CoreSQL("SELECT calendar.*, users.Name FROM calendar INNER JOIN users ON calendar.Author = users.id WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." ORDER BY Date ASC");

    if(mysql_num_rows($result) == 0) return array();
    $items = array();
    while($row = mysql_fetch_assoc($result))
    {
      $item = new Calendar();
      $item->ID = $row["id"];
      $item->Date = $row["Date"];
      $item->Title = $this->SQLUnEscape($row["Title"]);
      $item->Author = $row["Author"];
      $item->AuthorName = $this->SQLUnEscape($row["Name"]);
      $item->ReadAccess = $row["ReadAccess"];
      $item->Text = $this->SQLUnEscape($row["Text"]);
      $signups = array();
      foreach(explode(",",$row["Signups"]) as $signup)
        if(!empty($signup)) $signups[] = $users[$signup];
      $item->Signups = $signups;
      $items[] = $item;
    }
    mysql_free_result($result);
    return $items;
  }

  // *******************************************************
  // Reads and upcoming calendar entry from the core database
  // *******************************************************  
  public function ReadCalendarEntry($id)
  {
    $users = $this->GetRegisteredUserNames();
    $result = $this->CoreSQL("SELECT calendar.*, users.Name FROM calendar INNER JOIN users ON calendar.Author = users.id WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND Date>='".$this->GMTTime()."' AND calendar.id=".$id." LIMIT 1");

    $row = mysql_fetch_assoc($result);
    
    $item = new Calendar();
    $item->ID = $row["id"];
    $item->Date = $row["Date"];
    $item->Title = $this->SQLUnEscape($row["Title"]);
    $item->Author = $row["Author"];
    $item->AuthorName = $this->SQLUnEscape($row["Name"]);
    $item->ReadAccess = $row["ReadAccess"];
    $item->Text = $this->SQLUnEscape($row["Text"]);
    $signups = array();
    foreach(explode(",",$row["Signups"]) as $signup)
      if(!empty($signup)) $signups[] = $users[$signup];
    $item->Signups = $signups;
    
    mysql_free_result($result);
    return $item;
  }

  // *******************************************************
  // Reads upcoming calendar entries the user has signed up to
  // *******************************************************  
  public function ReadCalendarSignups()
  {
    $users = $this->GetRegisteredUserNames();
    $result = $this->CoreSQL("SELECT calendar.*, users.Name FROM calendar INNER JOIN users ON calendar.Author = users.id WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND Date>='".$this->GMTTime()."' AND FIND_IN_SET('".$this->CurrentUser()->ID."', Signups)>0 ORDER BY Date ASC");

    if(mysql_num_rows($result) == 0) return array();
    $items = array();
    while($row = mysql_fetch_assoc($result))
    {
      $item = new Calendar();
      $item->ID = $row["id"];
      $item->Date = $row["Date"];
      $item->Title = $this->SQLUnEscape($row["Title"]);
      $item->Author = $row["Author"];
      $item->AuthorName = $this->SQLUnEscape($row["Name"]);
      $item->ReadAccess = $row["ReadAccess"];
      $item->Text = $this->SQLUnEscape($row["Text"]);
      $signups = array();
      foreach(explode(";",$row["Signups"]) as $signup)
        if(!empty($signup)) $signups[] = $users[$signup];
      $item->Signups = $signups;
      $items[] = $item;
    }
    mysql_free_result($result);
    return $items;
  }
  
  // *******************************************************
  // SHOUTS  SHOUTS  SHOUTS  SHOUTS  SHOUTS  
  // SHOUTS  SHOUTS  SHOUTS  SHOUTS  SHOUTS  
  // SHOUTS  SHOUTS  SHOUTS  SHOUTS  SHOUTS  
  // *******************************************************  

  // *******************************************************
  // Reads shouts from the core database
  // *******************************************************  
  public function ReadShouts()
  {
    $result = $this->CoreSQL("SELECT shouts.*, users.Name FROM shouts INNER JOIN users ON shouts.Author = users.id ORDER BY shouts.id DESC LIMIT 15");
    
    if(mysql_num_rows($result) == 0) return array();
    $shouts = array();
    while($row = mysql_fetch_assoc($result))
    {
      $shout = new Shout();
      $shout->ID = $row["id"];
      $shout->Date = $row["Date"];
      $shout->Author = $row["Author"];
      $shout->AuthorName = $this->SQLUnEscape($row["Name"]);
      $shout->Text = $this->SQLUnEscape($row["Text"]);
      $shouts[] = $shout;
    }
    mysql_free_result($result);
    return $shouts;
  }

  // *******************************************************
  // Save a shout to the core database
  // *******************************************************  
  public function SaveShout($text)
  {
    $this->CoreSQL("INSERT INTO shouts (Author, Date, Text) VALUES (".$this->CurrentUser()->ID.",'".$this->GMTTime()."','".$this->SQLEscape($text)."')");
    
    // Check for double post
    $result = $this->CoreSQL("SELECT id,Text FROM shouts WHERE Author='".$this->CurrentUser()->ID."' ORDER BY id DESC LIMIT 2");
    if(mysql_num_rows($result) != 2) return;
    $row = mysql_fetch_assoc($result);
    $id = $row["id"];
    $text1 = $row["Text"];
    $row = mysql_fetch_assoc($result);
    $text2 = $row["Text"];
    mysql_free_result($result);
    
    if($text1 == $text2)
      $this->CoreSQL("DELETE FROM shouts WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Deletes the given shout
  // *******************************************************  
  public function DeleteShout($id)
  {
    $this->CoreSQL("DELETE FROM shouts WHERE id=".$id);
  }

  // *******************************************************
  // UTILITY  UTILITY  UTILITY  UTILITY  UTILITY  
  // UTILITY  UTILITY  UTILITY  UTILITY  UTILITY  
  // UTILITY  UTILITY  UTILITY  UTILITY  UTILITY  
  // *******************************************************  

  // *******************************************************
  // Returns the icon with the given type id
  // *******************************************************  
	public function IconFromTypeID($id, $size = "64")
	{
    $path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."eveicons".DIRECTORY_SEPARATOR;
    $url = "http://".$_SERVER["SERVER_NAME"]."/eveicons/";
    return $url.$size."_".$size."/icon".$id.".png";
	}
  
  // *******************************************************
  // Navigates to the given URL
  // *******************************************************  
  public function Goto($url)
  {
    header("Location: ".$url);
    exit;
  }

  // *******************************************************
  // Determines if the browser is the in-game browser
  // *******************************************************  
  public function IsIGB()
  {
    return isset($_SERVER['HTTP_EVE_TRUSTED']);
  }

  // *******************************************************
  // Returns the current GMT
  // *******************************************************  
  public function GMTTime()
  {
    return gmdate("Y-m-d H:i:s");
  }

  // *******************************************************
  // Converts the given GMT date time to current user's local time
  // *******************************************************  
  public function GMTToLocal($date)
  {
    $tz = $this->CurrentUser()->TimeZone;
    $df = $this->CurrentUser()->DateFormat;
    
    return date($df, strtotime($date) + ($tz * 60 * 60));
  }

  // *******************************************************
  // Converts the given GMT date time to the given time zone
  // *******************************************************  
  public function GMTToLocalTZ($date, $timezone)
  {
    $df = $this->CurrentUser()->DateFormat;
    
    return date($df, strtotime($date) + ($timezone * 60 * 60));
  }

  // *******************************************************
  // Formats the given GMT date time using current user's time settings
  // *******************************************************  
  public function GMTFormat($date)
  {
    $df = $this->CurrentUser()->DateFormat;
    
    return date($df, strtotime($date));
  }

  // *******************************************************
  // Converts the given local time to GMT date time
  // *******************************************************  
  public function LocalToGMT($date)
  {
    $tz = $this->CurrentUser()->TimeZone;
    $df = $this->CurrentUser()->DateFormat;
    
    return date($df, strtotime($date) - ($tz * 60 * 60));
  }
  
  // *******************************************************
  // Formats the given seconds as days/hours/minutes/seconds
  // *******************************************************  
  public function SecondsToTime($time, $showseconds = false)
  {
    if($time <= 0) return "";
    
    $days = floor($time / 86400);
    $hours = floor($time / 3600) - $days * 24;
    $mins = floor($time / 60) - $hours * 60 - $days * 1440;
    $secs = floor($time - $mins * 60 - $hours * 3600 - $days * 86400);
    
    $str = "";
    if($days != 0) $str .= $days."D ";
    if($hours != 0) $str .= $hours."H ";
    if($mins != 0) $str .= $mins."M ";
    if((($secs != 0) && $showseconds) || empty($str)) $str .= $secs."S";
    
    return trim($str);
  }

  // *******************************************************
  // Calculates the time difference between the given GMT times and 
  // returns it as days/hours/minutes/seconds
  // *******************************************************  
  public function TimeDifference($time, $showseconds = false, $basetime = "")
  {
    if(!is_numeric($time)) $time = strtotime($time);
    if(empty($basetime)) $basetime = $this->GMTTime();
    if(!is_numeric($basetime)) $basetime = strtotime($basetime);

    return $this->SecondsToTime($basetime - $time, $showseconds);
  }
  
  // *******************************************************
  // Creates an instance of the HTML editor
  // *******************************************************  
  public function HTMLEditor($name, $text = "", $height = "")
  {
    if($this->IsIGB())
    {
      echo "<textarea name='".$name."' rows='20' cols='60'>";
      echo $text;
      echo "</textarea>";
    }
    else
    {
      $oFCKeditor = new FCKeditor($name);
      $oFCKeditor->BasePath = "http://".$_SERVER["SERVER_NAME"]."/fckeditor/";
      $oFCKeditor->ToolbarSet = 'Full';
      $oFCKeditor->Value = $text;
      if(!empty($height)) $oFCKeditor->Height = $height;
      return $oFCKeditor->CreateHTML();
    }
  }

  // ******************************************************
  // Logs the given action
  // *******************************************************  
  public function Log($action)
  {
    $this->CoreSQL("INSERT INTO log (UserID, `Date`, Text) VALUES (".$this->CurrentUser()->ID.", '".$this->GMTTime()."', '".$this->SQLEscape($action)."')");
  }
	
  // ******************************************************
  // Returns the number of entries in the log
  // *******************************************************  
  public function LogCount()
  {
    $result = $this->CoreSQL("SELECT COUNT(*) FROM log");
    $count = mysql_result($result, 0);
    mysql_free_result($result);
    return $count;
  }

  // ******************************************************
  // Reads the log entries from the database
  // *******************************************************  
  public function ReadLog($start = 0, $count = 50)
  {
    $result = $this->CoreSQL("SELECT log.*, users.Name FROM log INNER JOIN users ON log.UserID = users.ID ORDER BY Date DESC LIMIT ".$start.", ".$count);
    $logs = array();
    while($row = mysql_fetch_assoc($result))
    {
      $log = new Log();
      $log->ID = $row["id"];
      $log->UserID = $row["UserID"];
      $log->UserName = $row["Name"];
      $log->Date = $row["Date"];
      $log->Text = $this->SQLUnEscape($row["Text"]);
      $logs[] = $log;
    }
    mysql_free_result($result);
    return $logs;
  }

  // *******************************************************
  // Returns the number of database queries performed
  // *******************************************************  
  public function GetQueryCount()
  {
    return $this->QueryCount;
  }
  
  // *******************************************************
  // Returns the time it took to perform database queries
  // *******************************************************  
  public function GetQueryTime()
  {
    return $this->QueryTime;
  }
  
  // *******************************************************
  // Returns the number of API queries performed
  // *******************************************************  
  public function GetAPIQueryCount()
  {
    return $this->APIQueryCount;
  }
  
  // *******************************************************
  // Returns the time it took to construct the core object
  // *******************************************************  
  public function GetCoreInitTime()
  {
    return $this->CoreInit;
  }
  
  // *******************************************************
  // NOTEPAD  NOTEPAD  NOTEPAD  NOTEPAD  NOTEPAD  
  // NOTEPAD  NOTEPAD  NOTEPAD  NOTEPAD  NOTEPAD  
  // NOTEPAD  NOTEPAD  NOTEPAD  NOTEPAD  NOTEPAD  
  // *******************************************************  

  // *******************************************************
  // Returns the number of notes in the current user's notepad
  // *******************************************************  
  public function GetNotepadCount()
  {
    $result = $this->CoreSQL("SELECT COUNT(*) FROM notepad WHERE Author=".$this->CurrentUser()->ID);
    
    $count = mysql_result($result, 0);
    
    mysql_free_result($result);
    return $count;
  }
  
  // *******************************************************
  // Returns an array of note titles in the current user's notepad
  // *******************************************************  
  public function GetNotepadTitles()
  {
    $result = $this->CoreSQL("SELECT id, Title FROM notepad WHERE Author=".$this->CurrentUser()->ID." ORDER BY Title ASC");
    
    $notes = array();
    while($row = mysql_fetch_assoc($result))
    {
      $notes[$row["id"]] = $this->SQLUnEscape($row["Title"]);
    }
    
    mysql_free_result($result);
    return $notes;
  }
  
  // *******************************************************
  // Reads a note from the current user's notepad
  // *******************************************************  
  public function ReadNotepad($id)
  {
    $result = $this->CoreSQL("SELECT id, Author, Title, AES_DECRYPT(Text, '??n0k1a!!".$id."') AS DeText FROM notepad WHERE Author=".$this->CurrentUser()->ID." AND id=".$id." ORDER BY Title ASC");
    
    $note = new Notepad();
    if($row = mysql_fetch_assoc($result))
    {
      $note->ID = $row["id"];
      $note->Author = $row["Author"];
      $note->Title = $this->SQLUnEscape($row["Title"]);
      $note->Text = $this->SQLUnEscape($row["DeText"]);
    }
    
    mysql_free_result($result);
    return $note;
  }

  // *******************************************************
  // Deletes a note from the current user's notepad
  // *******************************************************  
  public function DeleteNotepad($id)
  {
    $result = $this->CoreSQL("SELECT Title FROM notepad WHERE id=".$id." LIMIT 1");
    $title = mysql_result($result, 0);
    $this->CoreSQL("DELETE FROM notepad WHERE Author=".$this->CurrentUser()->ID." AND id=".$id);
		$this->Log("Notepad entry deleted: ".$title.".");
  }

  // *******************************************************
  // Inserts a note into the current user's notepad
  // *******************************************************  
  public function NewNotepad($title, $text)
  {
    $this->CoreSQL("INSERT INTO notepad (Author, Title, Text) VALUES (".$this->CurrentUser()->ID.", '".$this->SQLEscape($title)."', '')");
    $result = $this->CoreSQL("SELECT LAST_INSERT_ID();");
    $id = mysql_result($result, 0);
    $this->CoreSQL("UPDATE notepad SET Text=AES_ENCRYPT('".$this->SQLEscape($text)."', '??n0k1a!!".$id."') WHERE Author=".$this->CurrentUser()->ID." AND id=".$id);
    
    mysql_free_result($result);
		$this->Log("New notepad entry: ".$title.".");
  }

  // *******************************************************
  // Edits the given note in the current user's notepad
  // *******************************************************  
  public function EditNotepad($id, $title, $text)
  {
    $this->CoreSQL("UPDATE notepad SET Title='".$this->SQLEscape($title)."', Text=AES_ENCRYPT('".$this->SQLEscape($text)."', '??n0k1a!!".$id."') WHERE Author=".$this->CurrentUser()->ID." AND id=".$id);
  }

  // *******************************************************
  // FORUMS  FORUMS  FORUMS  FORUMS  FORUMS  FORUMS
  // FORUMS  FORUMS  FORUMS  FORUMS  FORUMS  FORUMS
  // FORUMS  FORUMS  FORUMS  FORUMS  FORUMS  FORUMS
  // *******************************************************  

  // *******************************************************
  // Reads forum category gropus
  // *******************************************************  
  public function ReadForumGroups()
  {
    $result = $this->CoreSQL("SELECT `Group` FROM forum_categories GROUP BY `Group` ORDER BY `Order` ASC");
    
    if(mysql_num_rows($result) == 0) return array();
    $groups = array();
    $i = 1;
    while($row = mysql_fetch_assoc($result))
    {
      $groups[$i] = $this->SQLUnEscape($row["Group"]);
      $i++;
    }
    mysql_free_result($result);
    return $groups;
  }

  // *******************************************************
  // Reads forum categories from the core database
  // *******************************************************  
  public function ReadForumCategories()
  {
    // Read unread topics
    $result = $this->CoreSQL("SELECT CategoryID FROM forum_topics AS t1 LEFT JOIN forum_topicwatch AS t2 ON (t1.id=t2.TopicID AND t2.UserID=".$this->CurrentUser()->ID.") WHERE t1.IsActive = TRUE AND (t1.LastReplyID > IFNULL(t2.LastReplyID, 0)) GROUP BY CategoryID");
    $unreads = array();
    while($row = mysql_fetch_assoc($result))
    {
      if($this->CanReadCategory($row["CategoryID"]) == 1)
        $unreads[] = $row["CategoryID"];
    }
    mysql_free_result($result);
    
    // Read categories
    $result = $this->CoreSQL("SELECT t1.*, COUNT(t2.id) AS TopicCount, MAX(t2.DateLastPost) AS LastPostDate FROM forum_categories AS t1 LEFT JOIN forum_topics AS t2 ON (t2.CategoryID = t1.ID AND t2.IsActive=TRUE) WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." GROUP BY id ORDER BY `Order`");
    
    if(mysql_num_rows($result) == 0) return array();
    $categories = array();
    while($row = mysql_fetch_assoc($result))
    {
      $category = new Category();
      $category->ID = $row["id"];
      $category->Name = $this->SQLUnEscape($row["Name"]);
      $category->Group = $this->SQLUnEscape($row["Group"]);
      $category->Description = $this->SQLUnEscape($row["Description"]);
      $category->Order = $row["Order"];
      $category->ReadAccess = $row["ReadAccess"];
      $category->WriteAccess = $row["WriteAccess"];
      $category->TopicCount = $row["TopicCount"];
      $category->LastPostDate = $row["LastPostDate"];
      $category->HasUnreadTopics = in_array($row["id"], $unreads);
      $category->Password = $row["Password"];
      $categories[] = $category;
    }
    mysql_free_result($result);
    return $categories;
  }

  // *******************************************************
  // Reads forum category names from the core database
  // *******************************************************  
  public function ReadForumCategoryNames()
  {
    $result = $this->CoreSQL("SELECT id, Name, `Group` FROM forum_categories ORDER BY `Order`");
    
    if(mysql_num_rows($result) == 0) return array();
    $categories = array();
    while($row = mysql_fetch_assoc($result))
    {
      $categories[$row["id"]] = array("Name" => $this->SQLUnEscape($row["Name"]), "Group" => $this->SQLUnEscape($row["Group"]));
    }
    mysql_free_result($result);
    return $categories;
  }

  // *******************************************************
  // Reads the forum category with the given ID from the core database
  // *******************************************************  
  public function ReadForumCategory($id)
  {
    $result = $this->CoreSQL("SELECT * FROM forum_categories WHERE id = ".$id." AND ReadAccess <= ".$this->CurrentUser()->AccessRight());
    
    if(mysql_num_rows($result) == 0) return;
    $row = mysql_fetch_assoc($result);

    $category = new Category();
    $category->ID = $row["id"];
    $category->Name = $this->SQLUnEscape($row["Name"]);
    $category->Group = $this->SQLUnEscape($row["Group"]);
    $category->Description = $this->SQLUnEscape($row["Description"]);
    $category->Order = $row["Order"];
    $category->ReadAccess = $row["ReadAccess"];
    $category->WriteAccess = $row["WriteAccess"];
    $category->Password = $row["Password"];

    mysql_free_result($result);
    return $category;
  }

  // *******************************************************
  // Creates a new forum category under the given group
  // *******************************************************  
  public function NewForumCategory($title, $description, $group, $readaccess, $writeaccess)
  {
    // Calculate group order
    $order = 0;
    $result = $this->CoreSQL("SELECT `Order` FROM forum_categories WHERE `Group`='".$this->SQLEscape($group)."' ORDER BY `Order` DESC LIMIT 1");
    if(mysql_num_rows($result) != 0)
      $order = mysql_result($result, 0) + 1;
    else
    {
      $result = $this->CoreSQL("SELECT `Order` FROM forum_categories ORDER BY `Order` DESC LIMIT 1");
      $maxgroup = mysql_result($result, 0);
      $order = mysql_result($result, 0) + 100;
    }
    
    $query = "INSERT INTO forum_categories (`Name`, `Description`, `Group`, `Order`, `ReadAccess`, `WriteAccess`) VALUES (";
    $query .= "'".$this->SQLEscape($title)."', ";
    $query .= "'".$this->SQLEscape($description)."', ";
    $query .= "'".$this->SQLEscape($group)."', ";
    $query .= $order.", ";
    $query .= $readaccess.", ";
    $query .= $writeaccess.")";
    $this->CoreSQL($query);
		$this->Log("FORUMS(".$title.") New category created.");
  }

  // *******************************************************
  // Determines whether the user has read access to the given forum board
  // Return values:
  // 0 -> User does not have access
  // 1 -> User has access
  // 2 -> User needs to enter a password
  // *******************************************************  
  public function CanReadCategory($category)
  {
    if($this->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $this->CurrentUser()->HasEVERole(User::EVE_Director)) return 1;
    
    $result = $this->CoreSQL("SELECT Password FROM forum_categories WHERE id = ".$category." AND ReadAccess <= ".$this->CurrentUser()->AccessRight());
    
    if(mysql_num_rows($result) == 0) return 0;
    $row = mysql_fetch_assoc($result);
    $password = $row["Password"];
    mysql_free_result($result);
    $savedpass = @$_COOKIE["mdyn_forum".$category];

    if($password == md5("") || $savedpass == $password)
      return 1;
    else
      return 2;
  }

  // *******************************************************
  // Determines whether the user has write access to the given forum board
  // Return values:
  // 0 -> User does not have access
  // 1 -> User has access
  // 2 -> User needs to enter a password
  // *******************************************************  
  public function CanWriteCategory($category)
  {
    if($this->CurrentUser()->Name == "Guest") return 0;
    if($this->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $this->CurrentUser()->HasEVERole(User::EVE_Director)) return 1;
    
    $result = $this->CoreSQL("SELECT Password FROM forum_categories WHERE id = ".$category." AND WriteAccess <= ".$this->CurrentUser()->AccessRight());
    
    if(mysql_num_rows($result) == 0) return 0;
    $row = mysql_fetch_assoc($result);
    $password = $row["Password"];
    mysql_free_result($result);
    $savedpass = @$_COOKIE["mdyn_forum".$category];

    if($password == md5("") || $savedpass == $password)
      return 1;
    else
      return 2;
  }

  // *******************************************************
  // Determines whether the user has read access to the given forum topic
  // 0 -> User does not have access
  // 1 -> User has access
  // 2 -> User needs to enter a password
  // *******************************************************  
  public function CanReadTopic($topic)
  {
    if($this->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $this->CurrentUser()->HasEVERole(User::EVE_Director)) return 1;
    
    $result = $this->CoreSQL("SELECT t1.CategoryID, t4.Password FROM forum_topics AS t1 INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id WHERE t1.id = ".$topic." AND t4.ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND t1.IsActive = TRUE");
    
    if(mysql_num_rows($result) == 0) return 0;
    $row = mysql_fetch_assoc($result);
    $category = $row["CategoryID"];
    $password = $row["Password"];
    mysql_free_result($result);
  
    $savedpass = @$_COOKIE["mdyn_forum".$category];
    
    if($password == md5("") || $savedpass == $password)
      return 1;
    else
      return 2;
  }

  // *******************************************************
  // Determines whether the user has write access to the given forum topic
  // 0 -> User does not have access
  // 1 -> User has access
  // 2 -> User needs to enter a password
  // *******************************************************  
  public function CanWriteTopic($topic)
  {
    if($this->CurrentUser()->Name == "Guest") return 0;
    if($this->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $this->CurrentUser()->HasEVERole(User::EVE_Director)) return 1;
    
    $result = $this->CoreSQL("SELECT t1.CategoryID, t4.Password FROM forum_topics AS t1 INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id WHERE t1.id = ".$topic." AND t4.WriteAccess <= ".$this->CurrentUser()->AccessRight()." AND t1.IsActive = TRUE");
    
    if(mysql_num_rows($result) == 0) return 0;
    $row = mysql_fetch_assoc($result);
    $category = $row["CategoryID"];
    $password = $row["Password"];
    mysql_free_result($result);
  
    $savedpass = @$_COOKIE["mdyn_forum".$category];
    
    if($password == md5("") || $savedpass == $password)
      return 1;
    else
      return 2;
  }

  // *******************************************************
  // Edits the given forum category
  // *******************************************************  
  public function EditForumCategory($id, $title, $description, $group, $readaccess, $writeaccess)
  {
    // Calculate group order
    $order = 0;
    $result = $this->CoreSQL("SELECT `Group`,`Order` FROM forum_categories WHERE id=".$id." LIMIT 1");
    $row = mysql_fetch_assoc($result);
    if($this->SQLUnEscape($row["Group"]) == $group)
      $order = $row["Order"];
    else
    {
      $result = $this->CoreSQL("SELECT `Order` FROM forum_categories WHERE `Group`='".$this->SQLEscape($group)."' ORDER BY `Order` DESC LIMIT 1");
      if(mysql_num_rows($result) != 0)
        $order = mysql_result($result, 0) + 1;
      else
      {
        $result = $this->CoreSQL("SELECT `Order` FROM forum_categories ORDER BY `Order` DESC LIMIT 1");
        $maxgroup = mysql_result($result, 0);
        $order = mysql_result($result, 0) + 100;
      }
    }
    
    $this->CoreSQL("UPDATE forum_categories SET `Name`='".$this->SQLEscape($title)."', `Description`='".$this->SQLEscape($description)."', `Group`='".$this->SQLEscape($group)."', `Order`=".$order.", `ReadAccess`=".$readaccess.", `WriteAccess`=".$writeaccess." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Sets a password for the given forum category
  // *******************************************************  
  public function SetForumCategoryPassword($id, $password)
  {
    $this->CoreSQL("UPDATE forum_categories SET `Password`='".md5($password)."' WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Reads topic count for the given category
  // *******************************************************  
  public function ReadForumTopicCount($category)
  {
    $result = $this->CoreSQL("SELECT id FROM forum_categories WHERE id = ".$category." AND ReadAccess <= ".$this->CurrentUser()->AccessRight());    
    if(mysql_num_rows($result) == 0) return 0;

    $result = $this->CoreSQL("SELECT COUNT(*) FROM forum_topics WHERE CategoryID = ".$category." AND IsActive = TRUE ");
    
    $count = mysql_result($result, 0);
    
    mysql_free_result($result);
    return $count;
  }

  // *******************************************************
  // Reads forum topics from the core database
  // *******************************************************  
  public function ReadForumTopics($category, $start = 0, $count = 20)
  {
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, t3.Name AS LastPosterName, (t1.LastReplyID > IFNULL(t5.LastReplyID, 0)) AS IsUnread FROM forum_topics AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id INNER JOIN users AS t3 ON t1.LastPosterID = t3.id INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id LEFT JOIN forum_topicwatch AS t5 ON (t1.id=t5.TopicID AND t5.UserID=".$this->CurrentUser()->ID.") WHERE t1.CategoryID = ".$category." AND t1.IsActive = TRUE AND t4.ReadAccess <= ".$this->CurrentUser()->AccessRight()." ORDER BY t1.IsSticky DESC, t1.DateLastPost DESC LIMIT ".$start.",".$count);
    
    if(mysql_num_rows($result) == 0) return array();    
    $topics = array();
    while($row = mysql_fetch_assoc($result))
    {
      $topic = new Topic();
      $topic->ID = $row["id"];
      $topic->CategoryID = $row["CategoryID"];
      $topic->Title = $this->SQLUnEscape($row["Title"]);
      $topic->AuthorID = $row["AuthorID"];
      $topic->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
      
      $topic->DateCreated = $row["DateCreated"];
      $topic->DateLastPost = $row["DateLastPost"];
      $topic->LastReplyID = $row["LastReplyID"];
      $topic->LastPosterID = $row["LastPosterID"];
      $topic->LastPosterName = $this->SQLUnEscape($row["LastPosterName"]);
      $topic->ReplyCount = $row["ReplyCount"];
      $topic->PageCount = floor(($topic->ReplyCount - 1) / 10) + 1;
      
      $topic->IsLocked = $row["IsLocked"];
      $topic->IsSticky = $row["IsSticky"];
      $topic->IsActive = $row["IsActive"];
      $topic->IsUnread = ($row["IsUnread"] != 0);

      $topics[] = $topic;
    }
    mysql_free_result($result);
    return $topics;
  }

  // *******************************************************
  // Marks all forum topics in the given category as read
  // *******************************************************  
  public function MarkAllTopicsAsRead($category)
  {
    $result = $this->CoreSQL("SELECT id FROM forum_topics WHERE CategoryID=".$category);
    
    $topics = array();
    while($row = mysql_fetch_assoc($result))
    {
      $topics[] = $row["id"];
    }
    mysql_free_result($result);
    foreach($topics as $topic)
    {
      $this->UpdateTopicWatch($topic);
    }
  }

  // *******************************************************
  // Reads hot forum topics from the core database
  // *******************************************************  
  public function ReadHotForumTopics()
  {
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, t3.Name AS LastPosterName, t4.Name AS CategoryName, (t1.LastReplyID > IFNULL(t5.LastReplyID, 0)) AS IsUnread, t4.ReadAccess AS ReadAccess FROM forum_topics AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id INNER JOIN users AS t3 ON t1.LastPosterID = t3.id INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id LEFT JOIN forum_topicwatch AS t5 ON (t1.id=t5.TopicID AND t5.UserID=".$this->CurrentUser()->ID.") WHERE t1.IsActive = TRUE AND t4.ReadAccess <= ".$this->CurrentUser()->AccessRight()." ORDER BY t1.DateLastPost DESC LIMIT 40");
    
    if(mysql_num_rows($result) == 0) return array();
    $topics = array();
    while($row = mysql_fetch_assoc($result))
    {
      if($this->CanReadCategory($row["CategoryID"]) == 1)
      {
        $topic = new Topic();
        $topic->ID = $row["id"];
        $topic->CategoryID = $row["CategoryID"];
        $topic->CategoryName = $this->SQLUnEscape($row["CategoryName"]);
        $topic->Title = $this->SQLUnEscape($row["Title"]);
        $topic->AuthorID = $row["AuthorID"];
        $topic->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
        $topic->ReadAccess = $row["ReadAccess"];
        
        $topic->DateCreated = $row["DateCreated"];
        $topic->DateLastPost = $row["DateLastPost"];
        $topic->LastReplyID = $row["LastReplyID"];
        $topic->LastPosterID = $row["LastPosterID"];
        $topic->LastPosterName = $this->SQLUnEscape($row["LastPosterName"]);
        $topic->ReplyCount = $row["ReplyCount"];
        $topic->PageCount = floor(($topic->ReplyCount - 1) / 10) + 1;
        
        $topic->IsLocked = $row["IsLocked"];
        $topic->IsSticky = $row["IsSticky"];
        $topic->IsActive = $row["IsActive"];
        $topic->IsUnread = ($row["IsUnread"] != 0);
        
        $topic->TimeElapsed = $this->SecondsToTime(strtotime($this->GMTTime()) - strtotime($topic->DateLastPost));

        $topics[] = $topic;
        
        if(count($topics) == 20) break;
      }
    }
    mysql_free_result($result);
    return $topics;
  }

  // *******************************************************
  // Returns the category ID of the given topic
  // *******************************************************  
  public function GetTopicCategoryID($topic)
  {
    $result = $this->CoreSQL("SELECT CategoryID FROM forum_topics WHERE id = ".$topic);
    if(mysql_num_rows($result) == 0) return 0;

    $category = mysql_result($result, 0);
    
    mysql_free_result($result);
    return $category;
  }
  
  // *******************************************************
  // Reads topic count for the given category
  // *******************************************************  
  public function GetUnreadForumTopicCount()
  {
    $result = $this->CoreSQL("SELECT COUNT(*) FROM forum_topics AS t1 INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id LEFT JOIN forum_topicwatch AS t5 ON (t1.id=t5.TopicID AND t5.UserID=".$this->CurrentUser()->ID.") WHERE t1.LastReplyID > IFNULL(t5.LastReplyID, 0) AND t1.IsActive = TRUE AND t4.ReadAccess <= ".$this->CurrentUser()->AccessRight());
    
    $count = mysql_result($result, 0);
    
    mysql_free_result($result);
    return $count;
  }

  // *******************************************************
  // Reads unread forum topics from the core database
  // *******************************************************  
  public function ReadUnreadForumTopics($start = 0, $count = 20)
  {
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, t3.Name AS LastPosterName, t4.ReadAccess AS ReadAccess FROM forum_topics AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id INNER JOIN users AS t3 ON t1.LastPosterID = t3.id INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id LEFT JOIN forum_topicwatch AS t5 ON (t1.id=t5.TopicID AND t5.UserID=".$this->CurrentUser()->ID.") WHERE t1.LastReplyID > IFNULL(t5.LastReplyID, 0) AND t1.IsActive = TRUE AND t4.ReadAccess <= ".$this->CurrentUser()->AccessRight()." ORDER BY t1.DateLastPost DESC LIMIT ".$start.",".$count);
    
    if(mysql_num_rows($result) == 0) return array();
    $topics = array();
    while($row = mysql_fetch_assoc($result))
    {
      if($this->CanReadCategory($row["CategoryID"]) == 1)
      {
        $topic = new Topic();
        $topic->ID = $row["id"];
        $topic->CategoryID = $row["CategoryID"];
        $topic->Title = $this->SQLUnEscape($row["Title"]);
        $topic->AuthorID = $row["AuthorID"];
        $topic->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
        $topic->ReadAccess = $row["ReadAccess"];
        
        $topic->DateCreated = $row["DateCreated"];
        $topic->DateLastPost = $row["DateLastPost"];
        $topic->LastReplyID = $row["LastReplyID"];
        $topic->LastPosterID = $row["LastPosterID"];
        $topic->LastPosterName = $this->SQLUnEscape($row["LastPosterName"]);
        $topic->ReplyCount = $row["ReplyCount"];
        $topic->PageCount = floor(($topic->ReplyCount - 1) / 10) + 1;
        
        $topic->IsLocked = $row["IsLocked"];
        $topic->IsSticky = $row["IsSticky"];
        $topic->IsActive = $row["IsActive"];

        $topics[] = $topic;
      }
    }
    mysql_free_result($result);
    return $topics;
  }
    
  // *******************************************************
  // Reads the forum topic with the given id from the core database
  // *******************************************************  
  public function ReadForumTopic($id)
  {
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, t3.Name AS LastPosterName, t4.Name as CategoryName, t4.ReadAccess AS ReadAccess FROM forum_topics AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id INNER JOIN users AS t3 ON t1.LastPosterID = t3.id INNER JOIN forum_categories AS t4 ON t1.CategoryID = t4.id WHERE t1.id = ".$id." AND t4.ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND t1.IsActive = TRUE");
    
    if(mysql_num_rows($result) == 0) return;
    $row = mysql_fetch_assoc($result);
    
    $topic = new Topic();
    $topic->ID = $row["id"];
    $topic->CategoryID = $row["CategoryID"];
    $topic->CategoryName = $this->SQLUnEscape($row["CategoryName"]);
    $topic->Title = $this->SQLUnEscape($row["Title"]);
    $topic->AuthorID = $row["AuthorID"];
    $topic->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
    $topic->ReadAccess = $row["ReadAccess"];
    
    $topic->DateCreated = $row["DateCreated"];
    $topic->DateLastPost = $row["DateLastPost"];
    $topic->LastReplyID = $row["LastReplyID"];
    $topic->LastPosterID = $row["LastPosterID"];
    $topic->LastPosterName = $this->SQLUnEscape($row["LastPosterName"]);
    $topic->ReplyCount = $row["ReplyCount"];
    $topic->PageCount = floor(($topic->ReplyCount - 1) / 10) + 1;
    
    $topic->IsLocked = $row["IsLocked"];
    $topic->IsSticky = $row["IsSticky"];
    $topic->IsActive = $row["IsActive"];
    
    mysql_free_result($result);
    return $topic;
  }

  // *******************************************************
  // Returns the number of the page containing the given reply
  // *******************************************************  
  public function GetReplyPageNumber($topicid, $replyid)
  {
    $result = $this->CoreSQL("SELECT COUNT(*) FROM forum_replies WHERE TopicID=".$topicid." AND id<=".$replyid." AND IsDeleted=FALSE");
    
    if(mysql_num_rows($result) == 0) return 1;
    $replies = mysql_result($result, 0);
    
    $count = floor(($replies - 1) / 10) + 1;
    
    mysql_free_result($result);
    return $count;
  }

  // *******************************************************
  // Reads reply count for the given category
  // *******************************************************  
  public function ReadForumReplyCount($topic)
  {
    $result = $this->CoreSQL("SELECT t1.ReplyCount FROM forum_topics AS t1 INNER JOIN forum_categories AS t2 ON t1.CategoryID=t2.id WHERE t1.id = ".$topic." AND t2.ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND t1.IsActive = TRUE");
    
    $count = mysql_result($result, 0);
    
    mysql_free_result($result);
    return $count;
  }
  
  // *******************************************************
  // Reads topic replies from the core database
  // *******************************************************  
  public function ReadForumReplies($topic, $start = 0, $count = 10)
  {
    $this->UpdateTopicWatch($topic);
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, 
t2.CharID AS AuthorCharID, t2.Signature AS Signature, t2.Title AS 
AuthorTitle, t2.CorporationTicker AS AuthorCorpTicker, 
t2.PortalRoles AS AuthorPortalRoles, t3.Name as 
EditedByName FROM forum_replies AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id LEFT JOIN users AS t3 ON t1.EditedBy = t3.id WHERE t1.TopicID = ".$topic." AND t1.IsDeleted = FALSE ORDER BY t1.DateCreated ASC LIMIT ".$start.",".$count);
    
    if(mysql_num_rows($result) == 0) return array();
    $replies = array();
    while($row = mysql_fetch_assoc($result))
    {
      $reply = new Reply();
      $reply->ID = $row["id"];
      $reply->TopicID = $row["TopicID"];
      $reply->AuthorID = $row["AuthorID"];
      $reply->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
      $reply->AuthorCharID = $row["AuthorCharID"];
      $reply->AuthorSignature = $this->SQLUnEscape($row["Signature"]);
      $reply->AuthorTitle = $this->SQLUnEscape($row["AuthorTitle"]);
      $reply->AuthorCorpTicker = $this->SQLUnEscape($row["AuthorCorpTicker"]);
      $reply->IsHonorary = 
(BigNumber::Compare(BigNumber::BitwiseAnd($row["AuthorPortalRoles"], 
User::MDYN_HonoraryMember), 
"0") != 0);
      if($reply->IsHonorary && $reply->AuthorTitle == "")
        $reply->AuthorTitle = "Honorary Member";
      $reply->Text = $this->SQLUnEscape($row["Text"]);

      $reply->DateCreated = $row["DateCreated"];
      $reply->DateEdited = $row["DateEdited"];
      $reply->EditedByID = $row["EditedBy"];
      $reply->EditedByName = $this->SQLUnEscape($row["EditedByName"]);
      $reply->DateDeleted = $row["DateDeleted"];
      
      $reply->IsDeleted = $row["IsDeleted"];

      $reply->ShowSignature = $row["ShowSignature"];
      $reply->ShowEdited = $row["ShowEdited"];
      
      $replies[] = $reply;
    }
    mysql_free_result($result);
    return $replies;
  }

  // *******************************************************
  // Reads all topic replies from the core database
  // *******************************************************  
  public function ReadAllForumReplies($topic)
  {
    $this->UpdateTopicWatch($topic);
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, t2.CharID AS AuthorCharID, t2.Signature AS Signature, t2.Title AS AuthorTitle, t2.CorporationTicker AS AuthorCorpTicker, t3.Name as EditedByName FROM forum_replies AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id LEFT JOIN users AS t3 ON t1.EditedBy = t3.id WHERE t1.TopicID = ".$topic." AND t1.IsDeleted = FALSE ORDER BY t1.DateCreated ASC");
    
    if(mysql_num_rows($result) == 0) return array();
    $replies = array();
    while($row = mysql_fetch_assoc($result))
    {
      $reply = new Reply();
      $reply->ID = $row["id"];
      $reply->TopicID = $row["TopicID"];
      $reply->AuthorID = $row["AuthorID"];
      $reply->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
      $reply->AuthorCharID = $row["AuthorCharID"];
      $reply->AuthorSignature = $this->SQLUnEscape($row["Signature"]);
      $reply->AuthorTitle = $this->SQLUnEscape($row["AuthorTitle"]);
      $reply->AuthorCorpTicker = $this->SQLUnEscape($row["AuthorCorpTicker"]);
      
      $reply->Text = $this->SQLUnEscape($row["Text"]);

      $reply->DateCreated = $row["DateCreated"];
      $reply->DateEdited = $row["DateEdited"];
      $reply->EditedByID = $row["EditedBy"];
      $reply->EditedByName = $this->SQLUnEscape($row["EditedByName"]);
      $reply->DateDeleted = $row["DateDeleted"];
      
      $reply->IsDeleted = $row["IsDeleted"];
      
      $reply->ShowSignature = $row["ShowSignature"];
      $reply->ShowEdited = $row["ShowEdited"];

      $replies[] = $reply;
    }
    mysql_free_result($result);
    return $replies;
  }

  // *******************************************************
  // Reads the given reply from the core database
  // *******************************************************  
  public function ReadForumReply($reply)
  {
    $result = $this->CoreSQL("SELECT t1.*, t2.Name AS AuthorName, t2.CharID AS AuthorCharID, t2.Signature AS Signature, t2.Title AS AuthorTitle, t2.CorporationTicker AS AuthorCorpTicker, t3.Name as EditedByName FROM forum_replies AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id LEFT JOIN users AS t3 ON t1.EditedBy = t3.id WHERE t1.id = ".$reply." AND t1.IsDeleted = FALSE");
    
    if(mysql_num_rows($result) == 0) return array();
    $row = mysql_fetch_assoc($result);

    $reply = new Reply();
    $reply->ID = $row["id"];
    $reply->TopicID = $row["TopicID"];
    $reply->AuthorID = $row["AuthorID"];
    $reply->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
    $reply->AuthorCharID = $row["AuthorCharID"];
    $reply->AuthorSignature = $this->SQLUnEscape($row["Signature"]);
    $reply->AuthorTitle = $this->SQLUnEscape($row["AuthorTitle"]);
    $reply->AuthorCorpTicker = $this->SQLUnEscape($row["AuthorCorpTicker"]);
    
    $reply->Text = $this->SQLUnEscape($row["Text"]);

    $reply->DateCreated = $row["DateCreated"];
    $reply->DateEdited = $row["DateEdited"];
    $reply->EditedByID = $row["EditedBy"];
    $reply->EditedByName = $this->SQLUnEscape($row["EditedByName"]);
    $reply->DateDeleted = $row["DateDeleted"];
    
    $reply->IsDeleted = $row["IsDeleted"];

    $reply->ShowSignature = $row["ShowSignature"];
    $reply->ShowEdited = $row["ShowEdited"];
    
    return $reply;
  }

  // *******************************************************
  // Posts a reply to the given topic
  // *******************************************************  
  public function ReplyToForumTopic($topic, $reply, $showsignature = true)
  {
    // Insert reply
    $this->CoreSQL("INSERT INTO forum_replies (TopicID, AuthorID, DateCreated, Text, ShowSignature) VALUES (".$topic.", ".$this->CurrentUser()->ID.", '".$this->GMTTime()."', '".$this->SQLEscape($reply)."', ".($showsignature ? 1 : 0).")");
    
    // Check for double post
    $result = $this->CoreSQL("SELECT id, Text FROM forum_replies WHERE TopicID=".$topic." AND AuthorID=".$this->CurrentUser()->ID." ORDER BY id DESC LIMIT 2");
    if(mysql_num_rows($result) == 2)
    {
      $row = mysql_fetch_assoc($result);
      $id = $row["id"];
      $text1 = $row["Text"];
      $row = mysql_fetch_assoc($result);
      $text2 = $row["Text"];
      mysql_free_result($result);
      
      if($text1 == $text2)
      {
        $this->CoreSQL("DELETE FROM forum_replies WHERE id=".$id." LIMIT 1");
        return;
      }
    }
    
    // Update topic
    $result = $this->CoreSQL("SELECT LAST_INSERT_ID();");
    $id = mysql_result($result, 0);
    mysql_free_result($result);
    $this->CoreSQL("UPDATE forum_topics SET DateLastPost='".$this->GMTTime()."', LastPosterID=".$this->CurrentUser()->ID.", ReplyCount=ReplyCount+1, LastReplyID=".$id." WHERE id=".$topic." LIMIT 1");
    
    // Update topic watch
    $this->UpdateTopicWatch($topic);
    
    // Check subsciptions
    $users = $this->GetAllSubscriptions($topic);
    $key = array_search($this->CurrentUser()->ID, $users);
    if($key !== FALSE) unset($users[$key]);
    if(!empty($users))
    {
      $result = $this->CoreSQL("SELECT Title FROM forum_topics WHERE id=".$topic);
      $title = mysql_result($result, 0);
      mysql_free_result($result);
      $this->SendMail("New Reply To Subscribed Topic", "<p>".$this->CurrentUser()->Name." replied to the following forum topic:</p><p><a href=http://".$_SERVER['HTTP_HOST']."/php/forums.php?topic=".$topic."#item".$id.">".$title."</a></p>", $users);
    }
  }
  
  // *******************************************************
  // Edits the given reply
  // *******************************************************  
  public function EditForumReply($id, $reply, $showedited = true, $showsignature = true)
  {
    $this->CoreSQL("UPDATE forum_replies SET Text='".$this->SQLEscape($reply)."', DateEdited='".$this->GMTTime()."', EditedBy=".$this->CurrentUser()->ID.", ShowEdited=".($showedited ? 1 : 0).", ShowSignature=".($showsignature ? 1 : 0)." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Creates a new topic
  // *******************************************************  
  public function NewForumTopic($category, $title, $text, $showsignature = true)
  {
    $this->CoreSQL("INSERT INTO forum_topics (CategoryID, Title, AuthorID, DateCreated) VALUES (".$category.", '".$this->SQLEscape($title)."', ".$this->CurrentUser()->ID.", '".$this->GMTTime()."')");
    $result = $this->CoreSQL("SELECT LAST_INSERT_ID();");
    $id = mysql_result($result, 0);    
    $this->ReplyToForumTopic($id, $text, $showsignature);
    $this->UpdateTopicWatch($id);
    mysql_free_result($result);
		$this->Log("FORUMS(".$title.") New topic created.");
    return $id;
  }

  // *******************************************************
  // Moves the topic to the given category
  // *******************************************************  
  public function MoveTopic($id, $category)
  {
    $this->CoreSQL("UPDATE forum_topics SET CategoryID=".$category." WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Renames the given topic to the given title
  // *******************************************************  
  public function RenameTopic($id, $title)
  {
    $this->CoreSQL("UPDATE forum_topics SET Title='".$this->SQLEscape($title)."' WHERE id=".$id." LIMIT 1");
  }

  // *******************************************************
  // Deleted the given topic
  // *******************************************************  
  public function DeleteTopic($id)
  {
    $result = $this->CoreSQL("SELECT Title FROM forum_topics WHERE id=".$id." LIMIT 1");
    $title = $this->SQLUnEscape(mysql_result($result, 0));
    $this->CoreSQL("UPDATE forum_topics SET IsActive=FALSE WHERE id=".$id." LIMIT 1");
		$this->Log("FORUMS(".$title.") Topic deleted.");
  }

  // *******************************************************
  // Locks/unlocks a forum topic
  // *******************************************************  
  public function LockTopic($topic, $islocked = true)
  {
    $islocked = $islocked ? 1 : 0;
    $this->CoreSQL("UPDATE forum_topics SET IsLocked=".$islocked." WHERE id=".$topic." LIMIT 1");
  }
  
  // *******************************************************
  // Stickifies/unstickifies (is there such a word?) a forum topic
  // *******************************************************  
  public function StickyTopic($topic, $issticky = true)
  {
    $issticky = $issticky ? 1 : 0;
    $this->CoreSQL("UPDATE forum_topics SET IsSticky=".$issticky." WHERE id=".$topic." LIMIT 1");
  }

  // *******************************************************
  // Deletes a reply
  // *******************************************************  
  public function DeleteForumReply($reply)
  {
    $this->CoreSQL("UPDATE forum_replies SET IsDeleted=TRUE, DateDeleted='".$this->GMTTime()."', DeletedBy=".$this->CurrentUser()->ID." WHERE id=".$reply." LIMIT 1");
    $result = $this->CoreSQL("SELECT TopicID FROM forum_replies WHERE id=".$reply);
    $topic = mysql_result($result, 0);
    mysql_free_result($result);
    $result = $this->CoreSQL("SELECT COUNT(*) FROM forum_replies WHERE TopicID=".$topic." AND IsDeleted=FALSE");
    $count = mysql_result($result, 0);
    mysql_free_result($result);
    $this->CoreSQL("UPDATE forum_topics SET ReplyCount=".$count." WHERE id=".$topic." LIMIT 1");
		$this->Log("FORUMS(Reply ID=".$reply.") Reply deleted.");
    if($count == 0) $this->DeleteTopic($topic);
  }

  // *******************************************************
  // Updates user's topic watch
  // *******************************************************  
  public function UpdateTopicWatch($topic)
  {
    $result = $this->CoreSQL("SELECT LastReplyID FROM forum_topics WHERE id=".$topic);
    $id = mysql_result($result, 0);
    $result = $this->CoreSQL("SELECT * FROM forum_topicwatch WHERE TopicID=".$topic." AND UserID=".$this->CurrentUser()->ID);
    if(mysql_num_rows($result) == 0)
      $this->CoreSQL("INSERT INTO forum_topicwatch (UserID, TopicID, LastReplyID) VALUES (".$this->CurrentUser()->ID.", ".$topic.", ".$id.")");
    else
      $this->CoreSQL("UPDATE forum_topicwatch SET LastReplyID=".$id." WHERE UserID=".$this->CurrentUser()->ID." AND TopicID=".$topic." LIMIT 1");
    
    mysql_free_result($result);
  }

  // *******************************************************
  // Searches the forums
  // *******************************************************  
  public function SearchForums($keyword, $category = 0)
  {
    $readablecats = array();
    $readablecattitles = array();
    $result = $this->CoreSQL("SELECT id, Name FROM forum_categories WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND Password=MD5('')");
    while($row = mysql_fetch_assoc($result))
    {
      $readablecats[] = $row["id"];
      $readablecattitles[$row["id"]] = $this->SQLUnEscape($row["Name"]);
    }
    $keyword = trim($keyword);
    $query = "SELECT t1.*, MATCH (t1.Text) AGAINST ('".$this->SQLEscape($keyword)."') AS Score, t2.Name AS AuthorName, t2.CharID AS AuthorCharID, t3.Title AS TopicTitle, t3.CategoryID AS CategoryID FROM forum_replies AS t1 INNER JOIN users AS t2 ON t1.AuthorID = t2.id LEFT JOIN forum_topics AS t3 ON t1.TopicID = t3.id";
    $query .= " WHERE t1.IsDeleted = FALSE AND t3.IsActive = TRUE AND MATCH (t1.Text) AGAINST ('".$this->SQLEscape($keyword)."')";
    $query .= " AND FIND_IN_SET(t3.CategoryID, '".implode(",", $readablecats)."')";
    if($category != 0) $query .= " AND t3.CategoryID = ".$category;
    $query .= " ORDER BY CategoryID ASC, Score DESC";
    $query .= " LIMIT 25";
    $result = $this->CoreSQL($query);
    if(mysql_num_rows($result) == 0) return array();
    $replies = array();
    while($row = mysql_fetch_assoc($result))
    {
      $reply = new Reply();
      $reply->ID = $row["id"];
      $reply->TopicID = $row["TopicID"];
      $reply->AuthorID = $row["AuthorID"];
      $reply->AuthorName = $this->SQLUnEscape($row["AuthorName"]);
      $reply->AuthorCharID = $row["AuthorCharID"];
      $text = strip_tags($this->SQLUnEscape($row["Text"]));
      $maxlen = 150;
      $keywords = explode(" ", $keyword);
      foreach($keywords as $keyword)
      {
        $i = stripos($text, $keyword);
        if($i !== false)
        {
          $startstr = substr($text, 0, $i);
          $key = substr($text, $i, strlen($keyword));
          $endstr = substr($text, $i + strlen($keyword));
          if(strlen($startstr) > $maxlen) $startstr = "<b>...&nbsp;</b>".substr($startstr, -$maxlen);
          if(strlen($endstr) > $maxlen) $endstr = substr($endstr, 0, $maxlen)."<b>&nbsp;...</b>";
          $text = $startstr."<span class='highlight'>".$key."</span>".$endstr;
        }
      }
      $reply->Text = $text;
      $reply->DateCreated = $row["DateCreated"];

      $reply->TopicID = $row["TopicID"];
      $reply->TopicTitle = $this->SQLUnEscape($row["TopicTitle"]);
      $reply->CategoryTitle = $readablecattitles[$row["CategoryID"]];

      $replies[] = $reply;
    }
    mysql_free_result($result);
    return $replies;
  }  

  // *******************************************************
  // Returns the replies authored by the given user
  // *******************************************************  
  public function ForumRepliesByAuthor($id)
  {
    $readablecats = array();
    $readablecattitles = array();
    $result = $this->CoreSQL("SELECT id, Name FROM forum_categories WHERE ReadAccess <= ".$this->CurrentUser()->AccessRight()." AND Password=MD5('')");
    while($row = mysql_fetch_assoc($result))
    {
      $readablecats[] = $row["id"];
      $readablecattitles[$row["id"]] = $this->SQLUnEscape($row["Name"]);
    }
    $query = "SELECT t1.*, t3.Title AS TopicTitle, t3.CategoryID AS CategoryID FROM forum_replies AS t1 INNER JOIN forum_topics AS t3 ON t1.TopicID = t3.id";
    $query .= " WHERE t1.IsDeleted = FALSE AND t3.IsActive = TRUE AND t1.AuthorID=".$id;
    $query .= " AND FIND_IN_SET(t3.CategoryID, '".implode(",", $readablecats)."')";
    $query .= " GROUP BY TopicID";
    $query .= " ORDER BY DateCreated DESC";
    $query .= " LIMIT 25";
    $result = $this->CoreSQL($query);
    if(mysql_num_rows($result) == 0) return array();
    $replies = array();
    while($row = mysql_fetch_assoc($result))
    {
      $reply = new Reply();
      $reply->ID = $row["id"];
      $reply->TopicID = $row["TopicID"];
      $reply->TopicTitle = $this->SQLUnEscape($row["TopicTitle"]);
      $text = strip_tags($this->SQLUnEscape($row["Text"]));
      if(strlen($text) > 150) $text = substr($text, 0, 150)."<b>&nbsp;...</b>";
      $reply->Text = $text;
      $reply->DateCreated = $row["DateCreated"];

      $replies[] = $reply;
    }
    mysql_free_result($result);
    return $replies;
  }  

  // *******************************************************
  // Returns all subscriptions of the current user
  // *******************************************************  
  public function GetForumSubscriptions()
  {
    $result = $this->CoreSQL("SELECT t1.*,t2.Title FROM forum_subscriptions AS t1 INNER JOIN forum_topics AS t2 ON t1.TopicID=t2.ID WHERE UserID=".$this->CurrentUser()->ID);
    $subs = array();
    while($row = mysql_fetch_assoc($result))
    {
      $subs[] = array("TopicID" => $row["TopicID"], "Title" => $row["Title"], "Date" => $row["Date"]);
    }
    return $subs;
  }

  // *******************************************************
  // Returns all users subscribed to the given topic
  // *******************************************************  
  public function GetAllSubscriptions($topicid)
  {
    $result = $this->CoreSQL("SELECT UserID FROM forum_subscriptions WHERE TopicID=".$topicid);
    $subs = array();
    while($row = mysql_fetch_assoc($result))
    {
      $subs[] = $row["UserID"];
    }
    return $subs;
  }
  
  // *******************************************************
  // Subscribes the current user to the given topic
  // *******************************************************  
  public function SubscribeForumTopic($topicid)
  {
    $this->CoreSQL("INSERT INTO forum_subscriptions (TopicID,UserID,Date) VALUES (".$topicid.",".$this->CurrentUser()->ID.",'".$this->GMTTime()."')");
  }
  
  // *******************************************************
  // Unsubscribes the current user from the given topic
  // *******************************************************  
  public function UnSubscribeForumTopic($topicid)
  {
    $this->CoreSQL("DELETE FROM forum_subscriptions WHERE TopicID=".$topicid." AND UserID=".$this->CurrentUser()->ID);
  }
  
  // *******************************************************
  // Moves category groups
  // id should be the database ID of a category in the group
  // dir is either "up" or "down"
  // *******************************************************  
  public function MoveSection($id, $dir)
  {
    // Read section order
    $result = $this->CoreSQL("SELECT id, `Order` FROM forum_categories ORDER BY `Order` ASC");
    if(mysql_num_rows($result) == 0) return;
    $cats = array();
    while($row = mysql_fetch_assoc($result))
    {
      $major = floor($row["Order"] / 100);
      $cats[] = array("id" => $row["id"], "Order" => $row["Order"], "Major" => $major);
      if($row["id"] == $id) $orgorder = $major;
    }
    mysql_free_result($result);
    if(empty($orgorder)) return;
    
    // Order sections
    $i = 0;
    foreach($cats as $cat)
    {
      if(($cat["Major"] == $orgorder) && ($dir == "up"))
        $cat["Major"] = $cat["Major"] - 1.5;
      elseif(($cat["Major"] == $orgorder) && ($dir == "down"))
        $cat["Major"] = $cat["Major"] + 1.5;
      $cats[$i] = $cat;
      $i += 1;
    }
    usort($cats, "forumsectioncmp");
    
    $nmajor = 0;
    $nminor = 1;
    $i = 0;
    unset($lastsection);
    foreach($cats as $cat)
    {
      if(!isset($lastsection) || ($lastsection != $cat["Major"]))
      {
        $lastsection = $cat["Major"];
        $nmajor += 100;
        $nminor = 1;
      }
      else
        $nminor += 1;
      $cat["Order"] = $nmajor + $nminor;
      $cats[$i] = $cat;
      $i += 1;
    }

    // Update database
    foreach($cats as $cat)
    {
      $this->CoreSQL("UPDATE forum_categories SET `Order`=".$cat["Order"]." WHERE id=".$cat["id"]." LIMIT 1");
    }
  }

  // *******************************************************
  // Moves categories
  // id should be the database ID of the category
  // dir is either "up" or "down"
  // *******************************************************  
  public function MoveCategory($id, $dir)
  {
    // Read section order
    $result = $this->CoreSQL("SELECT id, `Order` FROM forum_categories ORDER BY `Order` ASC");
    if(mysql_num_rows($result) == 0) return;
    $cats = array();
    while($row = mysql_fetch_assoc($result))
    {
      $major = floor($row["Order"] / 100);
      $cats[] = array("id" => $row["id"], "Order" => $row["Order"], "Major" => $major);
      if($row["id"] == $id) $orgorder = $major;
    }
    mysql_free_result($result);
    if(empty($orgorder)) return;

    // Remove other sections
    $n = count($cats) - 1;
    for($i = $n; $i >= 0; $i -= 1)
    { 
      if($cats[$i]["Major"] != $orgorder)
        unset($cats[$i]);
    }
    
    // Order categories
    $i = 0;
    foreach($cats as $cat)
    {
      if(($cat["id"] == $id) && ($dir == "up"))
        $cat["Order"] = $cat["Order"] - 1.5;
      elseif(($cat["id"] == $id) && ($dir == "down"))
        $cat["Order"] = $cat["Order"] + 1.5;
      $cats[$i] = $cat;
      $i += 1;
    }
    usort($cats, "forumsectioncmp");
    $i = 0;
    foreach($cats as $cat)
    {
      $cat["Order"] = $orgorder * 100 + $i + 1;
      $cats[$i] = $cat;
      $i += 1;
    }

    // Update database
    foreach($cats as $cat)
    {
      $this->CoreSQL("UPDATE forum_categories SET `Order`=".$cat["Order"]." WHERE id=".$cat["id"]." LIMIT 1");
    }
  }

  
}

function forumsectioncmp($a, $b)
{
  if($a["Major"] == $b["Major"])
    return ($a["Order"] > $b["Order"]);
  else
    return ($a["Major"] > $b["Major"]);
}


?>
