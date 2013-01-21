-- --------------------------------------------------------

-- -
-- Table Structure `whatsnew_block`
-- -

CREATE TABLE `whatsnew_block` (
  `block_id`            int(11) unsigned NOT NULL,
  `display_type`        tinyint(1) unsigned NOT NULL default 0,
  `display_flag`        tinyint(1) unsigned NOT NULL default 0,
  `display_days`        tinyint(3) unsigned NOT NULL default 0,
  `display_number`      tinyint(3) unsigned NOT NULL default 0,
  `display_modules`     text,
  `display_title`       tinyint(1) unsigned NOT NULL default 0,
  `display_room_name`   tinyint(1) unsigned NOT NULL default 0,
  `display_module_name` tinyint(1) unsigned NOT NULL default 0,
  `display_user_name`   tinyint(1) unsigned NOT NULL default 0,
  `display_insert_time` tinyint(1) unsigned NOT NULL default 0,
  `display_description` tinyint(1) unsigned NOT NULL default 0,
  `allow_rss_feed`      tinyint(1) unsigned NOT NULL default 0,
  `select_room`         tinyint(1) unsigned NOT NULL default 0,
  `myroom_flag`         tinyint(1) unsigned NOT NULL default 0,
  `rss_title`           varchar(255) NOT NULL default '',
  `rss_description`     text,
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


-- --------------------------------------------------------

-- -
-- Table Structure `whatsnew_select_room`
-- -

CREATE TABLE `whatsnew_select_room` (
  `block_id`            int(11) unsigned NOT NULL,
  `room_id`             int(11) unsigned NOT NULL,
  PRIMARY KEY  (`block_id`,`room_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;


-- --------------------------------------------------------

-- -
-- Table Structure `whatsnew`
-- -

CREATE TABLE `whatsnew` (
  `whatsnew_id`        int(11) unsigned NOT NULL,
  `room_id`            int(11) unsigned NOT NULL default 0,
  `module_id`          int(11) unsigned NOT NULL default 0,
  `user_id`            varchar(40) NOT NULL default '',
  `authority_id`       int(11) unsigned NOT NULL default 0,
  `unique_id`          int(11) unsigned NOT NULL default 0,
  `title`              varchar(255) NOT NULL default '',
  `description`        text,
  `action_name`        varchar(255) NOT NULL default '',
  `parameters`         text,
  `count_num`          int(11) unsigned NOT NULL default 0,
  `child_update_time`  varchar(14) default NULL,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`whatsnew_id`),
  KEY `unique_id` (`unique_id`,`module_id`),
  KEY `room_id` (`room_id`,`insert_user_id`,`module_id`,`insert_time`),
  KEY `child_update_time` (`child_update_time`,`insert_time`,`whatsnew_id`),
  KEY `insert_time` (`insert_time`,`whatsnew_id`),
  KEY `module_id` (`module_id`,`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `whatsnew_user`
-- -

CREATE TABLE `whatsnew_user` (
  `whatsnew_id`        int(11) NOT NULL default 0,
  `user_id`            varchar(40) NOT NULL,
  `room_id`            int(11) default NULL,
  PRIMARY KEY  (`whatsnew_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
