--
-- Table structure for table `#__jaem_log`
--

DROP TABLE IF EXISTS `#__jaem_log`;
CREATE TABLE IF NOT EXISTS `#__jaem_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ext_id` varchar(50) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `check_date` datetime DEFAULT NULL,
  `check_info` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ext_id` (`ext_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `#__jaem_services`
-- 

CREATE TABLE IF NOT EXISTS `#__jaem_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ws_name` varchar(255) NOT NULL,
  `ws_mode` varchar(50) NOT NULL DEFAULT 'local',
  `ws_uri` varchar(255) NOT NULL,
  `ws_user` varchar(100) NOT NULL,
  `ws_pass` varchar(100) NOT NULL,
  `ws_default` tinyint(1) NOT NULL DEFAULT '0',
  `ws_core` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `#__jaem_services`
-- 

INSERT IGNORE INTO `#__jaem_services` VALUES (1, 'Local Service', 'local', '', '', '', 1, 1);
INSERT IGNORE INTO `#__jaem_services` VALUES (2, 'JoomlArt Updates', 'remote', 'http://update.joomlart.com/service/', '', '', 0, 1);