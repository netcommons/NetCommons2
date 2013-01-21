-- 
-- テーブルの構造 `backup_uploads`
-- 

CREATE TABLE `backup_uploads` (
  `upload_id` int(11) unsigned NOT NULL default '0',
  `site_id` varchar(40) NOT NULL default '',
  `url` text NOT NULL,
  `parent_id` int(11) NOT NULL default '0',
  `thread_num` int(11) NOT NULL default '0',
  `space_type` tinyint(1) NOT NULL default '0',
  `private_flag` tinyint(1) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`upload_id`)
) ENGINE=MyISAM;

-- 
-- テーブルの構造 `backup_hash_history`
-- 

CREATE TABLE `backup_encrypt_history` (
  `encrypt_data` varchar(255) default NULL,
  `room_id` int(11) unsigned NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  KEY `hash_data` (`encrypt_data`)
) ENGINE=MyISAM;