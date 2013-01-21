-- 
-- テーブルの構造 `menu_detail`
-- 

CREATE TABLE `menu_detail` (
  `block_id`           int(11) NOT NULL,
  `page_id`            int(11) NOT NULL,
  `visibility_flag`    tinyint(1) NOT NULL default '1',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`,`page_id`)
) ENGINE=MyISAM;