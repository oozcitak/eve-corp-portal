-- MySQL dump 10.13  Distrib 5.1.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: core
-- ------------------------------------------------------
-- Server version	5.1.37-1ubuntu5.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) NOT NULL,
  `Author` int(10) unsigned NOT NULL,
  `ReadAccess` int(10) unsigned NOT NULL DEFAULT '1',
  `WriteAccess` int(10) unsigned NOT NULL DEFAULT '2',
  `Text` text NOT NULL,
  `IsHidden` tinyint(1) NOT NULL DEFAULT '0',
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `Author` (`Author`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `articles_comments`
--

DROP TABLE IF EXISTS `articles_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Article` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `Author` int(10) unsigned NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Article` (`Article`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Author` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `ReadAccess` int(10) unsigned NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Text` text NOT NULL,
  `Signups` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=158 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `corporations`
--

DROP TABLE IF EXISTS `corporations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `corporations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `Ticker` varchar(10) NOT NULL,
  `CorporationID` int(10) unsigned NOT NULL,
  `IsBlocked` tinyint(1) NOT NULL DEFAULT '0',
  `CEOID` int(10) unsigned NOT NULL,
  `CEOName` varchar(64) NOT NULL,
  `IsExecutor` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11706 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cron`
--

DROP TABLE IF EXISTS `cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) NOT NULL,
  `ScheduleType` int(11) NOT NULL,
  `Developer` int(11) NOT NULL,
  `Source` varchar(1024) NOT NULL,
  `LastRun` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastError` text,
  PRIMARY KEY (`id`),
  KEY `Developer` (`Developer`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cron`
--

LOCK TABLES `cron` WRITE;
/*!40000 ALTER TABLE `cron` DISABLE KEYS */;
INSERT INTO `cron` VALUES (1,'Update Titles and Roles from EVE API',2,1,'/core/cronjobs/updateusers.job.php','2010-11-01 00:01:02','\r\n'),(4,'Fetch XML Feeds from EVE Online',0,1,'/core/cronjobs/fetchevefeeds.job.php','2010-11-01 12:01:06','\r\n'),(6,'Fetch Alliance Members from EVE API',4,1,'/core/cronjobs/fetchcorps.job.php','2010-11-01 12:01:11','\r\n'),(17,'Eve-Central Market Data - Production',2,1,'/plugins/productionprices/evecentralxml.php','2010-11-01 00:07:14',''),(18,'Store Front Update',1,1,'/plugins/productionprices/storeassets.php','2009-06-22 23:31:02',''),(16,'Eve-Central Market Data - Operational',4,1,'/plugins/payoutprices/evecentralxml.php','2010-11-01 12:01:47','');
/*!40000 ALTER TABLE `cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Name` varchar(200) NOT NULL,
  `EMail` varchar(200) NOT NULL,
  `APIUserID` varchar(200) NOT NULL,
  `APIKey` varchar(200) NOT NULL,
  `Notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_categories`
--

DROP TABLE IF EXISTS `forum_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Description` text,
  `ReadAccess` int(10) unsigned NOT NULL DEFAULT '2',
  `WriteAccess` int(10) unsigned NOT NULL DEFAULT '2',
  `Order` int(10) unsigned NOT NULL DEFAULT '0',
  `Group` varchar(100) DEFAULT 'General',
  `Password` varchar(32) NOT NULL DEFAULT 'd41d8cd98f00b204e9800998ecf8427e',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_categories`
--

LOCK TABLES `forum_categories` WRITE;
/*!40000 ALTER TABLE `forum_categories` DISABLE KEYS */;
INSERT INTO `forum_categories` VALUES (1,'General','General discussion forum.',0,0,101,'Public','d41d8cd98f00b204e9800998ecf8427e'),(2,'EVE Stuff','Dev blogs, chats, and important info from the EVE website.',0,0,102,'Public','d41d8cd98f00b204e9800998ecf8427e'),(3,'Recruitment','So you wanna join us?',5,4,1002,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(4,'Alliance News and Information ','Alliance information.',5,4,1006,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(5,'Corporate Calendar','Corporate Calendar Schedule. Linked from calendar.',5,4,1007,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(6,'Corporate News','Read Only News and Important Corp Information.',5,4,1008,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(7,'Corporate Policy','Rules and Regulations',5,4,1009,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(8,'Corporate Archive & Meetings','Corporate Meetings and Corporate Historical Documents',5,4,1005,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(9,'Personnel','Personnel Issues and information.',5,4,1004,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(10,'Recruiting Section','Forum section for Recruiters to verify new potential recruits. This section will be ONLY for potential recruit information.',3,3,206,'Administrative','d41d8cd98f00b204e9800998ecf8427e'),(11,'The Lounge','A forum for members. Any topic here will do.',2,2,301,'Lounge','d41d8cd98f00b204e9800998ecf8427e'),(12,'Idea Factory','Got an idea for a corp project? Lay it on!',2,2,302,'Lounge','d41d8cd98f00b204e9800998ecf8427e'),(13,'Stories & Historical Data','A place for corp histories, personal stories and data to keep track of where we were, where we are, and where we are going!',5,4,1003,'Archive','d41d8cd98f00b204e9800998ecf8427e'),(19,'Officer Lounge','Managers Hideout',3,3,701,'Management','d41d8cd98f00b204e9800998ecf8427e'),(20,'Director Lounge','Directors Hideaway',4,4,702,'Management','d41d8cd98f00b204e9800998ecf8427e'),(21,'Diplomacy','Chat logs and external diplomacy information and intel',4,4,703,'Management','d41d8cd98f00b204e9800998ecf8427e'),(22,'Forum Tests','Testing posts to check sigs/pics/embeds etc.',2,2,303,'Lounge','d41d8cd98f00b204e9800998ecf8427e'),(27,'The Research Hangar','White Lab Coats Optional!',2,2,402,'Industrial','d41d8cd98f00b204e9800998ecf8427e'),(28,'Manufacturing','The Builders be here.',2,2,403,'Industrial','d41d8cd98f00b204e9800998ecf8427e'),(26,'POS and Support','Information exchange for Player Owned Structures',2,2,401,'Industrial','d41d8cd98f00b204e9800998ecf8427e'),(29,'Industry','All things drilled and droned!',2,2,404,'Industrial','d41d8cd98f00b204e9800998ecf8427e'),(30,'WAR Operations','WAR TIME Operations briefing and information section.',2,2,501,'Military Operations HQ','d41d8cd98f00b204e9800998ecf8427e'),(31,'Intel Reports','Enemy intel reports.',2,2,502,'Military Operations HQ','d41d8cd98f00b204e9800998ecf8427e'),(32,'Fleet Operations','Combat Operations Central',2,2,503,'Military Operations HQ','d41d8cd98f00b204e9800998ecf8427e'),(33,'After Action Reports','Wartime operations debriefing.',2,2,504,'Military Operations HQ','d41d8cd98f00b204e9800998ecf8427e'),(34,'To Sell','Wanna sell something?',2,2,801,'Trade and Markets','d41d8cd98f00b204e9800998ecf8427e'),(35,'To Buy','Questions regarding items you would like to purchase.',2,2,802,'Trade and Markets','d41d8cd98f00b204e9800998ecf8427e'),(36,'Exploration','The place to talk about all those blinky bits that say complex.',2,2,803,'Trade and Markets','d41d8cd98f00b204e9800998ecf8427e'),(37,'Market Discussion','Market prices in the various Regions we operate in and out of.',2,2,804,'Trade and Markets','d41d8cd98f00b204e9800998ecf8427e'),(39,'Alliance Parlor','A forum for alliance members. Any topic here will do. ',1,1,901,'Alliance','d41d8cd98f00b204e9800998ecf8427e'),(40,'Setups and Training','The place to find out all the techie bits about fits, fights and tactics.',2,2,505,'Military Operations HQ','d41d8cd98f00b204e9800998ecf8427e'),(44,'Archive','The place where the old go to and die.',5,4,1001,'Archive','d41d8cd98f00b204e9800998ecf8427e');
/*!40000 ALTER TABLE `forum_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_replies`
--

DROP TABLE IF EXISTS `forum_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TopicID` int(10) unsigned NOT NULL,
  `AuthorID` int(10) unsigned NOT NULL,
  `Text` text NOT NULL,
  `DateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateEdited` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `EditedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `IsDeleted` tinyint(1) NOT NULL DEFAULT '0',
  `DateDeleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DeletedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `ShowSignature` tinyint(1) NOT NULL DEFAULT '1',
  `ShowEdited` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `TopicID` (`TopicID`),
  FULLTEXT KEY `Text` (`Text`)
) ENGINE=MyISAM AUTO_INCREMENT=8299 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_subscriptions`
--

DROP TABLE IF EXISTS `forum_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_subscriptions` (
  `TopicID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`TopicID`),
  KEY `UserID` (`UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_topics`
--

DROP TABLE IF EXISTS `forum_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CategoryID` int(10) unsigned NOT NULL,
  `Title` varchar(100) NOT NULL,
  `AuthorID` int(10) unsigned NOT NULL,
  `DateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateLastPost` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastPosterID` int(10) unsigned NOT NULL DEFAULT '0',
  `ReplyCount` int(10) unsigned NOT NULL DEFAULT '0',
  `IsLocked` tinyint(1) NOT NULL DEFAULT '0',
  `IsSticky` tinyint(1) NOT NULL DEFAULT '0',
  `IsActive` tinyint(1) NOT NULL DEFAULT '1',
  `LastReplyID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `CategoryID` (`CategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=1388 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_topicwatch`
--

DROP TABLE IF EXISTS `forum_topicwatch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_topicwatch` (
  `UserID` int(10) unsigned NOT NULL,
  `TopicID` int(10) unsigned NOT NULL,
  `LastReplyID` int(10) unsigned NOT NULL,
  KEY `UserID` (`UserID`),
  KEY `TopicID` (`TopicID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10050 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail`
--

DROP TABLE IF EXISTS `mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `From` int(10) unsigned NOT NULL,
  `To` text NOT NULL,
  `CC` text NOT NULL,
  `BCC` text NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Text` blob NOT NULL,
  `IsRead` tinyint(1) NOT NULL,
  `IsInbox` tinyint(1) NOT NULL,
  `Folder` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `UserID` (`UserID`),
  KEY `IsInbox` (`IsInbox`)
) ENGINE=MyISAM AUTO_INCREMENT=1192 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Author` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `ReadAccess` int(10) unsigned NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notepad`
--

DROP TABLE IF EXISTS `notepad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notepad` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Author` int(10) unsigned NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Text` blob,
  PRIMARY KEY (`id`),
  KEY `Author` (`Author`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) NOT NULL,
  `Title` varchar(1024) NOT NULL,
  `Developer` int(10) unsigned NOT NULL,
  `Release` int(10) unsigned NOT NULL DEFAULT '0',
  `ReadAccess` int(10) unsigned NOT NULL DEFAULT '2',
  `Order` int(10) unsigned NOT NULL DEFAULT '0',
  `ShowIGB` tinyint(1) NOT NULL DEFAULT '0',
  `ShowAdmin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
INSERT INTO `plugins` VALUES (1,'orgchart','Organization Chart',1,2,2,0,0,0),(4,'browserstats','Browser Statistics',1,0,1,0,0,0),(12,'payoutsubmission','Submit Operation',1,2,2,0,1,0),(11,'payoutprices','Payout Prices',1,2,4,0,0,1),(13,'payoutview','Operation History',1,2,2,0,0,0),(14,'payoutmanagement','Payout Management',1,2,4,0,0,1),(15,'productionprices','Production Prices',1,2,4,0,0,1),(17,'productionorders','Orders',1,2,1,0,1,0),(18,'productionmanagement','Production Management',1,0,3,0,0,1),(19,'gallery','Gallery',1,2,2,0,0,0);
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'CorporationName',''),(2,'AllianceName',''),(3,'AllianceURL',''),(4,'InactivityPeriod','3'),(5,'DirectorAPICharID','0'),(6,'DirectorAPIUserID','0'),(7,'DirectorAPIKey','0'),(8,'KillboardURL',''),(10,'MarketLocation',''),(19,'SecondaryDirectorAPICharID','0'),(11,'MarketHanger',''),(12,'MarketOffice',''),(13,'CorpStorePrice','10'),(14,'AllyStorePrice','25'),(27,'NewsLimit','15'),(20,'SecondaryDirectorAPIUserID','0'),(21,'SecondaryDirectorAPIKey','0'),(25,'CorpID','');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shouts`
--

DROP TABLE IF EXISTS `shouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shouts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Author` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=187 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Password` varchar(32) NOT NULL,
  `CharID` int(10) unsigned NOT NULL,
  `APIUserID` int(10) unsigned NOT NULL DEFAULT '0',
  `APIKey` blob,
  `LastLogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastPageVisit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsActive` tinyint(1) NOT NULL DEFAULT '1',
  `Alts` varchar(650) DEFAULT NULL,
  `EVERoles` bigint(20) unsigned NOT NULL DEFAULT '0',
  `TimeZone` int(11) NOT NULL DEFAULT '0',
  `EMail` varchar(255) DEFAULT NULL,
  `IM` varchar(255) DEFAULT NULL,
  `BirthDate` date NOT NULL DEFAULT '0000-00-00',
  `Location` varchar(255) DEFAULT NULL,
  `PortalRoles` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DateFormat` varchar(20) NOT NULL DEFAULT 'm.d.Y H:i',
  `PortalSettings` int(10) unsigned NOT NULL DEFAULT '13',
  `Signature` text,
  `IsGuest` tinyint(1) NOT NULL DEFAULT '0',
  `OOPUntil` date NOT NULL DEFAULT '0000-00-00',
  `OOPNote` varchar(255) DEFAULT NULL,
  `CorporationName` varchar(200) DEFAULT NULL,
  `CorporationTicker` varchar(10) DEFAULT NULL,
  `CorporationID` int(10) unsigned DEFAULT '0',
  `IsAlly` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=480 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Guest','','0',0,0,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00',1,NULL,0,0,NULL,NULL,'0000-00-00',NULL,0,'m.d.Y H:i',13,NULL,1,'0000-00-00',NULL,NULL,NULL,0,0),(1,'admin','','21232f297a57a5a743894a0e4a801fc3',0,0,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00',1,NULL,1,3,'','','0000-00-00',NULL,127,'m.d.Y H:i',13,NULL,0,'0000-00-00','','','',0,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-11-01 12:30:04
