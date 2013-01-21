-- --------------------------------------------------------

-- -
-- テーブルの構造 `circular`
-- -

CREATE TABLE `circular` (
  `circular_id`			int(11) NOT NULL default '0',
  `circular_subject`	varchar(255) NOT NULL default '',
  `circular_body`		text,
  `icon_name`			varchar(40) NOT NULL default '',
  `post_user_id`		varchar(40) NOT NULL default '',
  `period`				varchar(14) NOT NULL default '',
  `status`				tinyint(1) NOT NULL default '0',
  `reply_type`			tinyint(1) NOT NULL default '0',
  `seen_option`			tinyint(1) NOT NULL default '0',
  `room_id`				int(11) NOT NULL default '0',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`circular_id`),
  KEY `room_id` (`room_id`),
  KEY `post_user_id` (`post_user_id`),
  KEY `insert_time` (`insert_time`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `circular_user`
-- -

CREATE TABLE `circular_user` (
  `circular_id`			int(11) NOT NULL default '0',
  `receive_user_id`		varchar(40) NOT NULL default '',
  `reply_flag`			tinyint(1) NOT NULL default '0',
  `reply_body`			text,
  `reply_choice`		varchar(255) NOT NULL default '',
  `room_id`				int(11) NOT NULL default '0',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`circular_id`,`receive_user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `circular_block`
-- -

CREATE TABLE `circular_block` (
  `block_id`			int(11) NOT NULL default '0',
  `visible_row`			int(11) NOT NULL default '0',
  `block_type`			tinyint(1) NOT NULL default '0',
  `room_id`				int(11) NOT NULL default '0',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `circular_config`
-- -

CREATE TABLE `circular_config` (
  `room_id`				int(11) NOT NULL default '0',
  `create_authority`	tinyint(1) NOT NULL default '0',
  `mail_subject`		varchar(255) default '',
  `mail_body`			text NOT NULL default '',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `circular_choice`
-- -

CREATE TABLE `circular_choice` (
  `choice_id`			int(11) NOT NULL default '0',
  `circular_id`			int(11) NOT NULL default '0',
  `choice_sequence`		int(11) NOT NULL default '0',
  `choice_value`		text,
  `room_id`				int(11) NOT NULL default '0',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`choice_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `circular_postscript`
-- -

CREATE TABLE `circular_postscript` (
  `postscript_id`		int(11) NOT NULL default '0',
  `circular_id`			int(11) NOT NULL default '0',
  `postscript_sequence`	int(11) NOT NULL default '0',
  `postscript_value`	text,
  `room_id`				int(11) NOT NULL default '0',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`postscript_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `circular_group`
-- -

CREATE TABLE `circular_group` (
  `group_id`			int(11) NOT NULL default '0',
  `user_id`				varchar(40) NOT NULL default '',
  `group_name`			varchar(255) NOT NULL default '',
  `group_member`		text,
  `room_id`				int(11) NOT NULL default '0',
  `insert_time`			varchar(14) NOT NULL default '',
  `insert_site_id`		varchar(40) NOT NULL default '',
  `insert_user_id`		varchar(40) NOT NULL default '',
  `insert_user_name`	varchar(255) NOT NULL default '',
  `update_time`			varchar(14) NOT NULL default '',
  `update_site_id`		varchar(40) NOT NULL default '',
  `update_user_id`		varchar(40) NOT NULL default '',
  `update_user_name`	varchar(255) NOT NULL default '',
  PRIMARY KEY  (`group_id`),
  KEY `room_id` (`room_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM;
