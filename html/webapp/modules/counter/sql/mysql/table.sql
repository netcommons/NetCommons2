-- -
-- テーブルの構造 `counter`
-- -
CREATE TABLE `counter` (
  `block_id`		   int(11) unsigned NOT NULL,
  `counter_digit`	   int(11) NOT NULL,
  `counter_num`	       int(11) default '0',
  `show_type`          text,
  `show_char_before`   text,
  `show_char_after`    text,
  `comment`            text,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
