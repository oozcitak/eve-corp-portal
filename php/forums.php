<?php
  require_once('../core/core.class.php');
  $cms = new Core();
  
  $ismoderator =$cms->CurrentUser()->HasPortalRole(User::MDYN_CEO) || $cms->CurrentUser()->HasPortalRole(User::MDYN_ForumModerator) || $cms->CurrentUser()->HasPortalRole(User::MDYN_Administrator) || $cms->CurrentUser()->HasEVERole(User::EVE_Director) ? 1 : 0;
  $cms->assign("ismoderator", $ismoderator);
  $cms->assign("pagetitle", " | Forums");
  
  if(isset($_GET["category"]) && is_numeric(@$_GET["category"]))
  {
    $access = $cms->CanReadCategory($_GET["category"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_GET["category"]);

    $page = 0;
    if(isset($_GET["page"])  && is_numeric(@$_GET["page"])) $page = 
$_GET["page"] - 1;
    $pagecount = floor($cms->ReadForumTopicCount($_GET["category"]) / 20) + 1;
    if($pagecount < 0) $pagecount = 0;
    if($page < 0) $page = 0;

    if($page > $pagecount - 1) $page = $pagecount - 1;

    $cat = $cms->ReadForumCategory($_GET["category"]);
    $topics = $cms->ReadForumTopics($_GET["category"], $page * 20);
    $cms->assign("cat", $cat);
    $cms->assign("topics", $topics);
    $cms->assign("action", "category");
    $cms->assign("page", $page);
    $cms->assign("pagecount", $pagecount);
    $cms->assign("pagetitle", " | Forums | ".$cat->Name);
  }
  elseif(isset($_GET["markallread"]) && 
is_numeric(@$_GET["markallread"]))
  {
    $cat = $_GET["markallread"];
    $cms->MarkAllTopicsAsRead($cat);
    $cms->Goto("forums.php?category=".$cat);
  }
  elseif(isset($_GET["readreply"]) && is_numeric(@$_GET["topicid"]) && 
is_numeric(@$_GET["readreply"]))
  {
    $topic = $_GET["topicid"];
    $reply = $_GET["readreply"];
    $page = $cms->GetReplyPageNumber($topic, $reply);
    $cms->Goto("forums.php?topic=".$topic."&page=".$page."#item".$reply);
  }
  elseif(isset($_GET["getcategorypassword"]) && 
is_numeric(@$_GET["getcategorypassword"]))
  {
    $cms->assign("category", $_GET["getcategorypassword"]);
    if(isset($_COOKIE["mdyn_forum".$_GET["getcategorypassword"]]))
      $cms->assign("result", "1");
    $cms->assign("action", "getcategorypassword");
  }
  elseif(@$_GET["action"] == "getcategorypassworddone" && 
is_numeric(@$_POST["category"]))
  {
    if($_POST["submit"] == "Submit")
    {
      $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? ".".$_SERVER['SERVER_NAME'] : false;
      setcookie("mdyn_forum".$_POST["category"], md5($_POST["password"]), time()+60*60*24*300, "/", $domain);
      $cms->Goto("forums.php?category=".$_POST["category"]);
    }
    else
      $cms->Goto("forums.php");
  }
  elseif(@$_GET["action"] == "unread")
  {
    $page = 0;
    if(isset($_GET["page"]) && is_numeric(@$_GET["page"])) $page = 
$_GET["page"] - 1;
    $pagecount = floor($cms->GetUnreadForumTopicCount() / 20) + 1;
    if($pagecount < 0) $pagecount = 0;
    if($page < 0) $page = 0;
    if($page > $pagecount - 1) $page = $pagecount - 1;

    $topics = $cms->ReadUnreadForumTopics($page * 20);
    $cms->assign("topics", $topics);
    $cms->assign("action", "unread");
    $cms->assign("page", $page);
    $cms->assign("pagecount", $pagecount);
    $cms->assign("pagetitle", " | Forums | Unread Topics");
  }
  elseif(isset($_GET["topic"]) && is_numeric(@$_GET["topic"]))
  {
    $access = $cms->CanReadTopic($_GET["topic"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($_GET["topic"]));

    $page = 0;
    if(isset($_GET["page"]) && is_numeric(@$_GET["page"])) $page = 
$_GET["page"] - 1;
    $pagecount = floor(($cms->ReadForumReplyCount($_GET["topic"]) - 1) / 10) + 1;
    if($pagecount < 0) $pagecount = 0;
    if($page < 0) $page = 0;
    if($page > $pagecount - 1) $page = $pagecount - 1;
    
    $topic = $cms->ReadForumTopic($_GET["topic"]);
    $replies = $cms->ReadForumReplies($_GET["topic"], $page * 10);
    $cms->assign("topic", $topic);
    $cms->assign("replies", $replies);
    $cms->assign("action", "topic");
    $cms->assign("page", $page);
    $cms->assign("pagecount", $pagecount);
    $cms->assign("pagetitle", " | Forums | $topic->Title");
  }
  elseif(isset($_GET["reply"]) && is_numeric(@$_GET["reply"]))
  {
    $access = $cms->CanWriteTopic($_GET["reply"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($_GET["reply"]));

    $topic = $cms->ReadForumTopic($_GET["reply"]);
    $replies = $cms->ReadAllForumReplies($_GET["reply"]);
    $quote = "";
    if(isset($_GET["quote"]) && is_numeric(@$_GET["quote"])) 
    {
      $reply = $cms->ReadForumReply($_GET["quote"]);
      $quote = "<b>".$reply->AuthorName." said:</b><br /><div class='quote'>".$reply->Text."</div>";
    }
    $cms->assign("topic", $topic);
    $cms->assign("replies", $replies);
    $cms->assign("quote", $quote);
    $cms->assign("action", "reply");
  }
  elseif(isset($_GET["move"]) && is_numeric(@$_GET["move"]))
  {
    $access = $cms->CanWriteTopic($_GET["move"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_GET["originalcategory"]);

    $topic = $_GET["move"];
    $cats = $cms->ReadForumCategoryNames();
    $cms->assign("topic", $topic);
    $cms->assign("original", $_GET["originalcategory"]);
    $cms->assign("cats", $cats);    
    $cms->assign("action", "move");
  }
  elseif(isset($_GET["deletetopic"]) && 
is_numeric(@$_GET["deletetopic"]) && 
is_numeric(@$_GET["originalcategory"]))
  {
    $access = $cms->CanWriteTopic($_GET["deletetopic"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_GET["originalcategory"]);

    $cms->DeleteTopic($_GET["deletetopic"]);
    $cms->Goto("forums.php?category=".$_GET["originalcategory"]);
  }
  elseif(@$_GET["action"] == "movedone" && 
is_numeric(@$_POST["category"]) && is_numeric(@$_POST["topic"]))
  {
    $access = $cms->CanWriteCategory($_POST["category"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_POST["category"]);

    $topic = $_POST["topic"];
    $category = $_POST["category"];
    
    if($_POST["submit"] == "Move Topic") 
    {
      $cms->MoveTopic($topic, $category);
    }
    
    $cms->Goto("forums.php?topic=".$topic);
  }
  elseif(isset($_GET["rename"]) && is_numeric(@$_GET["rename"]))
  {
    $access = $cms->CanWriteTopic($_GET["rename"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($_GET["rename"]));

    $topic = $_GET["rename"];
    $topicname = $cms->ReadForumTopic($topic)->Title;
    $cms->assign("topic", $topic);
    $cms->assign("topicname", $topicname);
    $cms->assign("action", "rename");
  }
  elseif(@$_GET["action"] == "renamedone" && 
is_numeric(@$_POST["topic"]))
  {
    $access = $cms->CanWriteTopic($_POST["topic"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($_POST["topic"]));

    $topic = $_POST["topic"];
    $topicname = $_POST["topicname"];
    
    if($_POST["submit"] == "Rename Topic") 
    {
      if(!empty($topicname))
      {
        $cms->RenameTopic($topic, $topicname);
        $cms->Goto("forums.php?topic=".$topic);
      }
      else
      {
        $cms->assign("result", "1");
        $cms->assign("topic", $topic);
        $cms->assign("topicname", $topicname);
        $cms->assign("action", "rename");      
      }
    }
    else
      $cms->Goto("forums.php?topic=".$topic);
  }
  elseif(@$_GET["action"] == "replydone" && 
is_numeric(@$_POST["topic"]))
  {
    $topic = $_POST["topic"];
    $reply = $_POST["reply"];
    $showsignature = $_POST["showsignature"];
    if($_POST["submit"] == "Save") 
    {
      if(!empty($reply))
      {
        $access = $cms->CanWriteTopic($_POST["topic"]);
        if($access == 0) $cms->Goto("access.php");
        if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($_POST["topic"]));
    
        $cms->ReplyToForumTopic($topic, $reply, $showsignature);
        $topic = $cms->ReadForumTopic($_POST["topic"]);
        $replies = $cms->ReadForumReplies($_POST["topic"]);

        $cms->assign("topic", $topic);
        $cms->assign("replies", $replies);
        $cms->assign("action", "topic");
        $cms->Goto("forums.php?topic=".$topic->ID."&page=".$topic->PageCount."#item".$topic->LastReplyID);
      }
      else
      {
        $topic = $cms->ReadForumTopic($_POST["topic"]);
        $replies = $cms->ReadForumReplies($_POST["topic"]);
        $cms->assign("topic", $topic);
        $cms->assign("replies", $replies);
        $cms->assign("result", "1");
        $cms->assign("action", "reply");
      }
    }
    else
    {
      $cms->Goto("forums.php?topic=".$topic);
      exit;
    }
  }
  elseif(isset($_GET["edit"]) && is_numeric(@$_GET["edit"]))
  {
    $reply = $cms->ReadForumReply($_GET["edit"]);

    $access = $cms->CanWriteTopic($reply->TopicID);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($reply->TopicID));
    
    $cms->assign("replyid", $reply->ID);
    $cms->assign("topicid", $reply->TopicID);
    $cms->assign("reply", $reply->Text);
    $cms->assign("showsignature", $reply->ShowSignature);
    $cms->assign("action", "edit");
  }
  elseif(@$_GET["action"] == "editdone" && 
is_numeric(@$_POST["replyid"]) && is_numeric(@$_POST["topicid"]))
  {
    $replyid = $_POST["replyid"];
    $topicid = $_POST["topicid"];
    $reply = $_POST["reply"];
    $showedited = $_POST["showedited"];
    $showsignature = $_POST["showsignature"];
    if($_POST["submit"] == "Save") 
    {
      if(!empty($reply))
      {
        $access = $cms->CanWriteTopic($topicid);
        if($access == 0) $cms->Goto("access.php");
        if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($topicid));
    
        $cms->EditForumReply($replyid, $reply, $showedited, $showsignature);
        $cms->assign("action", "topic");
        $cms->Goto("forums.php?topic=".$topicid."&page=".$cms->GetReplyPageNumber($topicid, $replyid)."#item".$replyid);
        $cms->Goto("forums.php?topic=".$topicid);
      }
      else
      {
        $cms->assign("replyid", $replyid);
        $cms->assign("topicid", $topicid);
        $cms->assign("reply", $reply);
        $cms->assign("showsignature", $showsignature);
        $cms->assign("result", "1");
        $cms->assign("action", "edit");
      }
    }
    else
    {
      $cms->Goto("forums.php?topic=".$topicid);
      exit;
    }
  }
  elseif(isset($_GET["newtopic"]) && is_numeric(@$_GET["newtopic"]))
  {
    $access = $cms->CanWriteCategory($_GET["newtopic"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_GET["newtopic"]);
    
    $cat = $cms->ReadForumCategory($_GET["newtopic"]);
    $cms->assign("cat", $cat);
    $cms->assign("action", "newtopic");
  }
  elseif(@$_GET["action"] == "newcategory")
  {
    $cms->assign("groups", $cms->ReadForumGroups());
    $cms->assign("action", "newcategory");    
    $cms->assign("readaccess", "2");    
    $cms->assign("writeaccess", "2");    
  }
  elseif(@$_GET["action"] == "newcategorydone")
  {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $section = $_POST["section"];
    $newsection = $_POST["newsection"];
    $readaccess = $_POST["readaccess"];
    $writeaccess = $_POST["writeaccess"];
    
    $sectiontitle = $newsection;
    if(!empty($section)) 
    {
      $sections = $cms->ReadForumGroups();
      $sectiontitle = $sections[$section];
    }
    
    if($_POST["submit"] == "Create Category") 
    {
      if(!empty($title) && !empty($sectiontitle))
      {
        $cms->NewForumCategory($title, $description, $sectiontitle, $readaccess, $writeaccess);
        $cms->Goto("forums.php");
      }
      else
      {
        $cms->assign("action", "newcategory");
        $cms->assign("groups", $cms->ReadForumGroups());
        $cms->assign("result", "20");
        $cms->assign("title", $title);
        $cms->assign("description", $description);
        $cms->assign("section", $section);
        $cms->assign("newsection", $newsection);
        $cms->assign("readaccess", $readaccess);
        $cms->assign("writeaccess", $writeaccess);
      }
    }
    else
    {
      $cms->Goto("forums.php");
      exit;
    }
  }
  elseif(isset($_GET["editcategory"]) && 
is_numeric(@$_GET["editcategory"]))
  {
    $access = $cms->CanWriteCategory($_GET["editcategory"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_GET["editcategory"]);
    
    $cat = $cms->ReadForumCategory($_GET["editcategory"]);
    $groups = $cms->ReadForumGroups();
    $cms->assign("category", $cat->ID);
    $cms->assign("title", $cat->Name);
    $cms->assign("description", $cat->Description);
    $cms->assign("groups", $groups);
    $cms->assign("section", array_search($cat->Group, $groups));
    $cms->assign("newsection", "");
    $cms->assign("readaccess", $cat->ReadAccess);
    $cms->assign("writeaccess", $cat->WriteAccess);
    $cms->assign("action", "editcategory");
  }
  elseif(@$_GET["action"] == "editcategorydone" && 
is_numeric(@$_POST["category"]))
  {
    $id = $_POST["category"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $section = $_POST["section"];
    $newsection = $_POST["newsection"];
    $readaccess = $_POST["readaccess"];
    $writeaccess = $_POST["writeaccess"];
    
    $sectiontitle = $newsection;
    if(!empty($section)) 
    {
      $sections = $cms->ReadForumGroups();
      $sectiontitle = $sections[$section];
    }
    
    if($_POST["submit"] == "Save") 
    {
      if(!empty($title) && !empty($sectiontitle))
      {
        $access = $cms->CanWriteCategory($id);
        if($access == 0) $cms->Goto("access.php");
        if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$id);
        
        $cms->EditForumCategory($id, $title, $description, $sectiontitle, $readaccess, $writeaccess);
        $cms->Goto("forums.php?category=".$id);
      }
      else
      {
        $cms->assign("category", $id);
        $cms->assign("action", "editcategory");
        $cms->assign("groups", $cms->ReadForumGroups());
        $cms->assign("result", "20");
        $cms->assign("title", $title);
        $cms->assign("description", $description);
        $cms->assign("section", $section);
        $cms->assign("newsection", $newsection);
        $cms->assign("readaccess", $readaccess);
        $cms->assign("writeaccess", $writeaccess);
      }
    }
    else
    {
      $cms->Goto("forums.php?category=".$id);
    }
  }
  elseif(isset($_GET["setcategorypassword"]) && 
is_numeric(@$_GET["setcategorypassword"]))
  {
    $access = $cms->CanWriteCategory($_GET["setcategorypassword"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_GET["setcategorypassword"]);
        
    $id = $_GET["setcategorypassword"];
    $cms->assign("category", $id);
    $cms->assign("action", "setcategorypassword");
  }
  elseif(@$_GET["action"] == "setcategorypassworddone" && 
is_numeric(@$_POST["category"]))
  {
    if($_POST["submit"] == "Save") 
    {
      $access = $cms->CanWriteCategory($_POST["category"]);
      if($access == 0) $cms->Goto("access.php");
      if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_POST["category"]);
    
      $id = $_POST["category"];
      $password = $_POST["password"];
      $cms->SetForumCategoryPassword($id, $password);
    }
    $cms->Goto("forums.php");
  }
  elseif(@$_GET["action"] == "newtopicdone" && 
is_numeric(@$_POST["category"]))
  {      
    $title = $_POST["title"];
    $text = $_POST["text"];
    $category = $_POST["category"];
    $showsignature = $_POST["showsignature"];
    if($_POST["submit"] == "Save") 
    {
      if(!empty($title) && !empty($text))
      {
        $access = $cms->CanWriteCategory($_POST["category"]);
        if($access == 0) $cms->Goto("access.php");
        if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$_POST["category"]);
      
        $topicid = $cms->NewForumTopic($category, $title, $text, $showsignature);
        $topic = $cms->ReadForumTopic($topicid);
        $replies = $cms->ReadForumReplies($topicid);
        $cms->assign("topic", $topic);
        $cms->assign("replies", $replies);
        $cms->assign("action", "topic");
        $cms->Goto("forums.php?topic=".$topic->ID);
      }
      else
      {
        $cms->assign("category", $category);
        $cms->assign("title", $title);
        $cms->assign("text", $text);
        $cms->assign("result", "2");
        $cms->assign("action", "newtopic");
      }
    }
    else
    {
      $cms->Goto("forums.php?category=".$category);
      exit;
    }
  }
  elseif(isset($_GET["lock"]) || isset($_GET["unlock"]) || isset($_GET["sticky"]) || isset($_GET["unsticky"]))
  {
    $topicid = 0;
    if(isset($_GET["lock"]))
      $topicid = $_GET["lock"];
    elseif(isset($_GET["unlock"]))
      $topicid = $_GET["unlock"];
    elseif(isset($_GET["sticky"]))
      $topicid = $_GET["sticky"];
    elseif(isset($_GET["unsticky"]))
      $topicid = $_GET["unsticky"];

    if(!is_numeric($topicid)) $cms->Goto("access.php");

    $access = $cms->CanWriteTopic($topicid);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($topicid));
        
    if(isset($_GET["lock"]))
      $cms->LockTopic($topicid, true);
    elseif(isset($_GET["unlock"]))
      $cms->LockTopic($topicid, false);
    elseif(isset($_GET["sticky"]))
      $cms->StickyTopic($topicid, true);
    elseif(isset($_GET["unsticky"]))
      $cms->StickyTopic($topicid, false);

    $cms->Goto("forums.php?topic=".$topicid);
  }
  elseif(isset($_GET["delete"]) && is_numeric(@$_GET["delete"]) && 
is_numeric(@$_GET["topicid"]))
  {
    $access = $cms->CanWriteTopic($_GET["topicid"]);
    if($access == 0) $cms->Goto("access.php");
    if($access == 2) $cms->Goto("forums.php?getcategorypassword=".$cms->GetTopicCategoryID($_GET["topicid"]));
    
    $cms->DeleteForumReply($_GET["delete"]);
    $cms->Goto("forums.php?topic=".$_GET["topicid"]);
  }
  elseif(@$_GET["action"] == "search")
  {
    if(@$_GET["submit"] == "Cancel") $cms->Goto("forums.php");

    $searchtext = @$_GET["searchtext"];
    $searchcategory = @$_GET["searchcategory"];
    
    $results = array();
    if(!empty($searchtext)) $results = $cms->SearchForums($searchtext, $searchcategory);
    $cms->assign("results", $results);
    
    $cats = $cms->ReadForumCategoryNames();
    $cms->assign("cats", $cats);    
    $cms->assign("searchtext", $searchtext);    
    $cms->assign("searchcategory", $searchcategory);    
    $cms->assign("action", "search");
  }
  elseif(@$_GET["action"] == "displayorder")
  {
    if(!$ismoderator) $cms->Goto("access.php");
    // List all categories
    $cats = $cms->ReadForumCategories();
    $cms->assign("cats", $cats);
    $cms->assign("action", "displayorder");
  }
  elseif(isset($_GET["movesection"]) && 
is_numeric(@$_GET["movesection"]))
  {
    $cms->MoveSection($_GET["movesection"], $_GET["dir"]);
    $cms->Goto("forums.php?action=displayorder");
  }
  elseif(isset($_GET["movecategory"]) && 
is_numeric(@$_GET["movecategory"]))
  {
    $cms->MoveCategory($_GET["movecategory"], $_GET["dir"]);
    $cms->Goto("forums.php?action=displayorder");
  }
  elseif(isset($_GET["subscribe"]) && is_numeric(@$_GET["subscribe"]))
  {
    $cms->SubscribeForumTopic($_GET["subscribe"]);
    $cms->assign("topic", $_GET["subscribe"]);
    $cms->assign("action", "subscribe");
  }
  elseif(@$_GET["action"] == "stats")
  {
    if(!$ismoderator) $cms->Goto("access.php");
    // List stats
    $result = $cms->CoreSQL("SELECT t1.TopicID, COUNT( t1.TopicID ) AS RowCount, t2.Title FROM `forum_topicwatch` AS t1 LEFT JOIN forum_topics AS t2 ON t1.TopicID = t2.id GROUP BY t1.TopicID ORDER BY RowCount DESC LIMIT 20");
    $mostviewed = array();
    while($row = mysql_fetch_assoc($result))
    {
      $mostviewed[] = array("URL" => "forums.php?topic=".$row["TopicID"], "Title" => $cms->SQLUnescape($row["Title"]), "Data" => $row["RowCount"]);
    }
    $result = $cms->CoreSQL("SELECT id,Title,ReplyCount FROM `forum_topics` ORDER BY ReplyCount DESC LIMIT 20");
    $mostreplied = array();
    while($row = mysql_fetch_assoc($result))
    {
      $mostreplied[] = array("URL" => "forums.php?topic=".$row["id"], "Title" => $cms->SQLUnescape($row["Title"]), "Data" => $row["ReplyCount"]);
    }
    $result = $cms->CoreSQL("SELECT COUNT(t1.ReplyCount) AS RowCount,t2.Name,t2.id FROM forum_topics AS t1 LEFT JOIN users AS t2 ON t1.AuthorID=t2.id WHERE t2.Name!='Guest' GROUP BY t1.AuthorID ORDER BY RowCount DESC LIMIT 20");
    $members = array();
    while($row = mysql_fetch_assoc($result))
    {
      $members[] = array("URL" => "profile.php?user=".$row["id"], "Title" => $cms->SQLUnescape($row["Name"]), "Data" => $row["RowCount"]);
    }

    $cms->assign("mostviewed", $mostviewed);
    $cms->assign("mostreplied", $mostreplied);
    $cms->assign("members", $members);
    $cms->assign("pagetitle", " | Forums | Statistics");

    $cms->assign("action", "stats");
  }
  elseif(@$_GET["action"] == "members")
  {
    $result = $cms->CoreSQL("SELECT id,Name,CorporationName,CorporationTicker FROM users WHERE Name!='Guest' ORDER BY Name ASC");
    $members = array();
    while($row = mysql_fetch_assoc($result))
    {
      $members[] = array("URL" => "profile.php?user=".$row["id"], "Title" => $cms->SQLUnescape($row["Name"]), "Data" => $cms->SQLUnescape($row["CorporationName"])." [".$cms->SQLUnescape($row["CorporationTicker"])."]");
    }

    $cms->assign("members", $members);
    $cms->assign("pagetitle", " | Forums | Members");

    $cms->assign("action", "members");
  }
  else
  {
    // List all categories
    $cats = $cms->ReadForumCategories();
    $cms->assign("cats", $cats);
    // Hot topics
    $hottopics = $cms->ReadHotForumTopics();
    $cms->assign("hottopics", $hottopics);
    $cms->assign("action", "home");
  }
  
  $cms->display('forums.tpl');
?>
