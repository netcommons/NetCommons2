-- --------------------------------------------------------

-- -
-- Table Structure `security_access`
-- -


CREATE TABLE `security_access` (
  `ip` varchar(255) NOT NULL default '0.0.0.0',
  `request_uri` text,
  `insert_time` varchar(14) NOT NULL default ''
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `security_log`
-- -

CREATE TABLE `security_log` (
  `lid` mediumint(8) unsigned NOT NULL default '0',
  `uid` varchar(40) NOT NULL default '',
  `ip` varchar(255) NOT NULL default '0.0.0.0',
  `type` varchar(255) NOT NULL default '',
  `agent` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `extra` text NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  PRIMARY KEY  (`lid`)
)ENGINE=MyISAM;
