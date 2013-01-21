-- -
-- テーブルの構造 `iframe`
-- -
CREATE TABLE `iframe` (
  `block_id`           int(11) unsigned NOT NULL,
  `url`                text,
  `frame_width`        int(11) unsigned NOT NULL default '600',
  `frame_height`       int(11) unsigned NOT NULL default '400',
  `scrollbar_show`     tinyint(1) unsigned NOT NULL default '1',
  `scrollframe_show`   tinyint(1) unsigned NOT NULL default '0',
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