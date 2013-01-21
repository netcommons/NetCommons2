-- -
-- テーブルの構造 `announcement`
-- -

CREATE TABLE `announcement` (
  `block_id`         int(11) unsigned NOT NULL,
  `content`          text,
  `more_content`     text,
  `more_title`       varchar(255) default NULL,
  `hide_more_title`  varchar(255) default NULL,
  `room_id`          int(11) NOT NULL default '0',
  `insert_time`      varchar(14) NOT NULL default '',
  `insert_site_id`   varchar(40) NOT NULL default '',
  `insert_user_id`   varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL,
  `update_time`      varchar(14) NOT NULL default '',
  `update_site_id`   varchar(40) NOT NULL default '',
  `update_user_id`   varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`block_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;