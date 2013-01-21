-- -
-- テーブルの構造 `online`
-- -

CREATE TABLE `online` (
  `block_id`            int(11) unsigned NOT NULL,
  `user_flag`           tinyint(1) NOT NULL default 0,
  `member_flag`         tinyint(1) NOT NULL default 0,
  `total_member_flag`   tinyint(1) NOT NULL default 0,
  `room_id`             int(11) NOT NULL default 0,
  `insert_time`         varchar(14) NOT NULL default '',
  `insert_site_id`      varchar(40) NOT NULL default '',
  `insert_user_id`      varchar(40) NOT NULL default '',
  `insert_user_name`    varchar(255) NOT NULL default '',
  `update_time`         varchar(14) NOT NULL default '',
  `update_site_id`      varchar(40) NOT NULL default '',
  `update_user_id`      varchar(40) NOT NULL default '',
  `update_user_name`    varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
