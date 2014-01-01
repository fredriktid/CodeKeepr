-- MySQL dump 10.13  Distrib 5.5.31, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: codekeepr
-- ------------------------------------------------------
-- Server version	5.5.31-0ubuntu0.13.04.1

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
-- Table structure for table `code`
--

DROP TABLE IF EXISTS `code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` int(11) NOT NULL,
  `modified` int(11) NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `is_private` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7715309882F1BAF4` (`language_id`),
  KEY `IDX_77153098A76ED395` (`user_id`),
  CONSTRAINT `FK_77153098A76ED395` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`),
  CONSTRAINT `FK_7715309882F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `code`
--

LOCK TABLES `code` WRITE;
/*!40000 ALTER TABLE `code` DISABLE KEYS */;
INSERT INTO `code` VALUES (4,1,2,'Escape a string to use as a regexp pattern',1372002926,1372002926,'static public function escapeGrepPattern($pattern) {\r\n	return addcslashes($pattern, \'./+*?[^]($)\');\r\n}',0),(5,2,2,'Login with SSH using Paramiko and run some commands',1372004247,1372004247,'#!/usr/bin/env python\r\nimport sys, paramiko\r\n\r\nclient = paramiko.SSHClient()\r\nclient.set_missing_host_key_policy(paramiko.AutoAddPolicy())\r\nclient.load_system_host_keys()\r\n\r\ntry:\r\n	client.connect(\'hostname\', username=\'username\', password=\'password\')\r\nexcept Exception:\r\n	print \"Login failed\"\r\nelse:\r\n\r\n	cmds = [\"find -type d -print0|xargs -0 chmod u=rwx,go=rx\",\r\n		\"find -type f -print0|xargs -0 chmod u=rw,g=r,o=r\",\r\n		\"find -type f -name \'*.php\' -print0|xargs -0 chmod o=\",\r\n		\"find -type f -name \'*.cgi\' -print0|xargs -0 chmod u=rwx,go=rx\"]\r\n\r\n	for cmd in cmds:\r\n		chan = client.get_transport().open_session()\r\n		print \"\\nrunning \'%s\'\" % cmd\r\n		chan.exec_command(cmd)\r\n		print \"exit status: %s\" % chan.recv_exit_status()\r\n\r\n	print \"\\ndone\\n\"\r\n	client.close()',0),(6,1,1,'Microsecond stopwatch',1372004480,1372004480,'<?php \r\n// timer start\r\n$time = microtime(true);\r\n// ... do something\r\n// stop timer\r\necho \'Took \' . ((microtime(true) - $time) * 1000) .  \' ms\';',0),(7,1,3,'Read CSV-file with SplFileObject',1372006559,1372006559,'try {\r\n    $this->fileObject = new SplFileObject($file);\r\n} catch(RuntimeException $e) {\r\n    $this->log(\'RuntimeException: \' . $e->getMessage());\r\n    return false;\r\n}\r\n\r\n$this->fileObject->setFlags(SplFileObject::READ_CSV);\r\n$this->fileObject->setCsvControl(\';\');\r\n\r\nforeach($this->fileObject as $line => $data) {\r\n    // do something with $data...\r\n}',0),(8,2,2,'Share files with Pythons SimpleHTTPServer',1372009072,1372009072,'python -m SimpleHTTPServer 8000\n# running this will share files in current directory on http://hostname:8000',0),(9,5,2,'Avoid jQuery-conflicts with other libraries',1372016941,1372016941,'jQuery.noConflict();\r\n(function($, window, document){\r\n    // now use dollar safely…\n    var body = $(\'body\').html();\r\n})(jQuery, window, document);',0),(10,3,2,'Enum value',1372020317,1372020317,'$node.data_map.enum_attribute.content.enumobject_list.0.enumvalue',0),(11,2,2,'Chaining comparison operators',1372020451,1372020451,'>>> x = 5\r\n>>> 1 < x < 10\r\nTrue\r\n>>> 10 < x < 20 \r\nFalse\r\n>>> x < 10 < x*10 < 100\r\nTrue\r\n>>> 10 > x <= 9\r\nTrue\r\n>>> 5 == x > 4\r\nTrue',0),(12,1,2,'Create a user in eZ Publish 4.x',1372020715,1372020715,'const USER_PARENT_ID = 12;\r\n\r\nprotected function createUser($login, $email, $password, $attributes)\r\n{\r\n    if($userObject = eZUser::fetchByName($login))\r\n        throw new RunetimeException(sprintf(\'User %s already exists\', $login));\r\n\r\n    $params = array(\r\n        \'creator_id\' => 14,\r\n        \'class_identifier\' => \'user\',\r\n        \'language\' => \'nor-NO\',\r\n        \'parent_node_id\' => self::USER_PARENT_ID,\r\n        \'attributes\' => $attributes\r\n    );\r\n\r\n    $contentObject = eZContentFunctions::createAndPublishObject($params);\r\n\r\n    $user = eZUser::create($contentObject->ID);\r\n    $user->setInformation(\r\n        $contentObject->ID,\r\n        $login,\r\n        $email,\r\n        \'placeholder\'\r\n    );\r\n\r\n    $user->setAttribute(\'password_hash\', $password);\r\n    $user->setAttribute(\'password_hash_type\', 1); // Basic MD5\r\n\r\n    $user->store();\r\n    return $contentObject;\r\n}',0),(13,1,2,'Current siteaccess in eZ 4.x',1372162347,1372162347,'$GLOBALS[\'eZCurrentAccess\'][\'name\'];',0),(14,1,2,'Current URI of a view in eZ  4.x',1372266504,1372266504,'$Params[\'Module\']->Functions[\'my_view\'][\'uri\']',0),(15,3,2,'Development settings',1372402492,1372402492,'### DEVELOPMENT ONLY ###\r\n[MailSettings]\r\nTransport=file \r\n[DebugSettings]\r\nDebugOutput=disabled\r\nDebugRedirection=disabled\r\nDebugToolbar=disabled\r\nDebugByIP=disabled\r\nDebugByUser=disabled\r\n[TemplateSettings]\r\nTemplateCompile=disabled\r\nTemplateCache=disabled\r\nDevelopmentMode=enabled\r\nShowUsedTemplates=enabled\r\nDebug=enabled\r\nShowXHTMLCode=disabled\r\n[ContentSettings]\r\nViewCaching=disabled\r\n[DatabaseSettings]\r\nSQLOutput=disabled',0),(16,1,2,'Current user in eZ 4.x',1372404746,1372404746,'$user = eZUser::currentUser();',0),(17,1,2,'Indexer to help with AttributeFilter',1372627970,1372627970,'<?php\n\n// a fictional base class\nClass Base\n{\n    protected $logFile;\n\n    public function log()\n    {\n        // do something....\n    }\n}\n\n\n// a helper class for using AttributeFilter in eZPublish 4.x\nclass Indexer extends Base\n{\n    // default settings (do not change, see overrides below)\n    protected $parentId = 2;\n    protected $identifier = \'website\';\n    protected $index = \'key\';\n    protected $operator = \'=\';\n    protected $depth = 1;\n\n    protected $data;\n    protected $node;\n\n    public function __construct($value)\n    {\n        parent::__construct();\n\n        $this->setData(array(\n            $this->index => $value\n        ));\n\n        try\n        {\n            $this->node = $this->getNodeByIndex();\n        }\n        catch(RunetimeException $e)\n        {\n            $this->log(\'Failed to get node \' . $this->value . \': \' . $e->getMessage());\n        }\n    }\n\n    public function getId()\n    {\n        return $this->getData($this->index);\n    }\n\n    public function getName()\n    {\n        return ($this->validNode()) ? $this->node->attribute(\'name\') : \'\';\n    }\n\n    protected function setData($data)\n    {\n        if(is_array($data))\n        {\n            $this->data = array();\n            foreach($data as $key => $value)\n            {\n                $this->data[$key] = $value;\n            }\n        }\n    }\n\n    public function getData($key)\n    {\n        if(array_key_exists($key, $this->data))\n            return $this->data[$key];\n\n        return false;\n    }\n\n    protected function getNodeByIndex()\n    {\n        if(!$this->index)\n            throw new RuntimeException(\'Missing index constant\');\n\n        if(!$value = $this->getData($this->index))\n            throw new RuntimeException(\'Can not find index in object\');\n\n        $indexNodes = eZContentObjectTreeNode::subTreeByNodeID(\n            array(\n                \'ClassFilterType\' => \'include\',\n                \'ClassFilterArray\' => array($this->identifier),\n                \'SortBy\' => array(\'published\', true),\n                \'Depth\' => $this->depth,\n                \'AttributeFilter\' => array(\n                    \'and\', array(\n                        sprintf(\'%s/%s\', $this->identifier, $this->index), $this->operator, $value\n                    ))\n                ),\n                $this->parentId\n            );\n\n        if(!isset($indexNodes[0]) || !is_object($indexNodes[0]))\n            throw new RuntimeException(\'No silos found with id \' . $value);\n        \n        return $indexNodes[0];\n    }\n}\n\n// extend to suit your needs, e.g:\nclass Silo extends Indexer\n{\n    // override parent id, class identifier, and index/attribute\n    protected $parentId = 64;\n    protected $identifier = \'silo\';\n    protected $index = \'remote_key\';\n}\n\n// print name of client 433\n$siloObj = new Silo(433);\necho $siloObj->getName();',0),(18,1,1,'Path to eZ-root',1372861039,1372861039,'eZSys::rootDir();',0);
/*!40000 ALTER TABLE `code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fos_user`
--

DROP TABLE IF EXISTS `fos_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fos_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fos_user`
--

LOCK TABLES `fos_user` WRITE;
/*!40000 ALTER TABLE `fos_user` DISABLE KEYS */;
INSERT INTO `fos_user` VALUES (1,'heidi','heidi','heidi@gmail.com','heidi@gmail.com',1,'lwax87xf32844swssogckkc4084wo04','+2vUzYbo3hsPSMu6aEBNd6VE6MtYIysZsFH8OyJ7ooojp0FqITm0kM6tyUWr4uQNEz4Mex0xK1JYXbNfAGXUHA==','2013-06-23 16:21:53',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL),(2,'freddy','freddy','freddy@netmaking.no','freddy@netmaking.no',1,'5ruuqa6r83k0gg0g0ws04ko8k8kskwc','v9q934+C+xu6kGAOpgzptCl1kYUSxf+jw17coiGrpGJHXk+kR3S3FwiWMy4ONU0RzrMrNG15THxsNGGHILsi8g==','2013-06-23 16:16:42',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL),(3,'trond','trond','trond@hollender.no','trond@hollender.no',1,'h7wvzry2lr4kgo40s08kwwc8ccsocg0','Lo/P4ken9S2m8F0HnWpWrqh/nUDFUWYFch1RN5Fp8X0cZ7kbOW8JFTPymedM0gaFz8vGctghe/gGN4g+Yq71dw==','2013-06-28 08:54:51',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL),(4,'nmeirik','nmeirik','eirik@netmaking.no','eirik@netmaking.no',1,'prysoo4audcwgokcs4w0oowksok8g0c','WdhhCKBnN8ewzCmDjmOofvw8DSfVpimYFNdT240qbfRmCWtEG3vI5QGCyrwpLzSMze1ddY5ZPu830+lrLY0t9g==','2013-06-24 09:32:33',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL);
/*!40000 ALTER TABLE `fos_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language`
--

LOCK TABLES `language` WRITE;
/*!40000 ALTER TABLE `language` DISABLE KEYS */;
INSERT INTO `language` VALUES (1,'PHP','php'),(2,'Python','python'),(3,'eZ Template','ezpublish'),(4,'Shell','bash'),(5,'jQuery','jquery');
/*!40000 ALTER TABLE `language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_codes`
--

DROP TABLE IF EXISTS `tag_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_codes` (
  `tag_id` int(11) NOT NULL,
  `code_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`code_id`),
  KEY `IDX_C741D84BBAD26311` (`tag_id`),
  KEY `IDX_C741D84B27DAFE17` (`code_id`),
  CONSTRAINT `FK_C741D84B27DAFE17` FOREIGN KEY (`code_id`) REFERENCES `code` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C741D84BBAD26311` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_codes`
--

LOCK TABLES `tag_codes` WRITE;
/*!40000 ALTER TABLE `tag_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag_codes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-07-18 15:49:59
