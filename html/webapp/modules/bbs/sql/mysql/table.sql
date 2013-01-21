-- --------------------------------------------------------

-- -
-- テーブルの構造 `bbs`
-- -

CREATE TABLE `bbs` (
  `bbs_id`             int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `bbs_name`           varchar(255) NOT NULL default '',
  `activity`           tinyint(1) NOT NULL default '0',
  `topic_authority`    tinyint(1) NOT NULL default '0',
  `child_flag`         tinyint(1) NOT NULL default '0',
  `vote_flag`          tinyint(1) NOT NULL default '0',
  `new_period`         int(11) NOT NULL default '0',
  `mail_send`          tinyint(1) NOT NULL default '0',
  `mail_authority`     tinyint(1) NOT NULL default '0',
  `mail_subject`       varchar(255) NOT NULL default '',
  `mail_body`          text,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bbs_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `bbs_block`
-- -

CREATE TABLE `bbs_block` (
  `block_id`           int(11) NOT NULL default '0',
  `bbs_id`             int(11) NOT NULL default '0',
  `display`            tinyint(1) NOT NULL default '0',
  `expand`             tinyint(1) NOT NULL default '0',
  `visible_row`        int(11) NOT NULL default '0',
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
  KEY `bbs_id` (`bbs_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `bbs_post`
-- -

CREATE TABLE `bbs_post` (
  `post_id`            int(11) NOT NULL default '0',
  `bbs_id`             int(11) NOT NULL default '0',
  `topic_id`           int(11) NOT NULL default '0',
  `parent_id`          int(11) NOT NULL default '0',
  `subject`            varchar(255) NOT NULL default '',
  `icon_name`          varchar(255) NOT NULL default '',
  `contained_sign`     varchar(255) NOT NULL default '',
  `vote_num`           int(11) NOT NULL default '0',
  `status`             tinyint(1) NOT NULL,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`post_id`),
  KEY `bbs_id` (`bbs_id`),
  KEY `topic_id` (`topic_id`),
  KEY `parent_id` (`parent_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `bbs_post_body`
-- -

CREATE TABLE `bbs_post_body` (
  `post_id`            int(11) NOT NULL default '0',
  `body`               text,
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `room_id` (`room_id`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `bbs_topic`
-- -

CREATE TABLE `bbs_topic` (
  `topic_id`           int(11) NOT NULL default '0',
  `newest_time`        varchar(14) NOT NULL default '',
  `child_num`          int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `bbs_users_post`
-- -

CREATE TABLE `bbs_user_post` (
  `user_id`            varchar(40) NOT NULL,
  `post_id`            int(11) NOT NULL default '0',
  `read_flag`          tinyint(1) NOT NULL default '0',
  `vote_flag`          tinyint(1) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`post_id`),
  KEY `post_id` (`post_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
