-- -
-- テーブルの構造 `chat`
-- -
CREATE TABLE `chat` (
  `block_id`             int(11) NOT NULL default '0',
  `height`               int(11) NOT NULL default '0',
  `width`                int(11) NOT NULL default '0',
  `reload`               int(11) NOT NULL default '0',
  `status`               tinyint(1) NOT NULL default '0',
  `display_type`         tinyint(1) NOT NULL default '0',
  `line_num`             int(11) NOT NULL default '0',
  `allow_anonymous_chat` tinyint(1) NOT NULL default '0',
  `room_id`              int(11) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `chat_contents`
-- -
CREATE TABLE `chat_contents` (
  `chat_id`            int(11) NOT NULL default '0',
  `block_id`           int(11) NOT NULL default '0',
  `chat_text`          text,
  `color`              varchar(7) NOT NULL default '',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`,`chat_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `chat_login`
-- -
CREATE TABLE `chat_login` (
  `block_id`           int(11) NOT NULL default '0',
  `sess_id`            varchar(32) NOT NULL default '',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  KEY `block_id` (`block_id`,`update_user_id`),
  KEY `block_id_2` (`block_id`,`update_time`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
