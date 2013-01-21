-- --------------------------------------------------------

-- -
-- テーブルの構造`multidatabase`
-- -

CREATE TABLE multidatabase (
  `multidatabase_id`   int(11) NOT NULL default '0',
  `room_id`	           int(11) NOT NULL default '0',
  `multidatabase_name` varchar(255) NOT NULL default '',
  `active_flag`        tinyint(1) NOT NULL default '0',
  `mail_flag`          varchar(255) NOT NULL default '',
  `mail_authority`     tinyint(1) NOT NULL default '0',
  `mail_subject`       varchar(255) default NULL,
  `mail_body`          text,
  `contents_authority` tinyint(1) NOT NULL default '0',
  `new_period`         int(11) NOT NULL default '0',
  `vote_flag`          tinyint(1) NOT NULL default '0',
  `comment_flag`       tinyint(1) NOT NULL default '0',
  `agree_flag`         tinyint(1) NOT NULL default '0',
  `agree_mail_flag`    tinyint(1) NOT NULL default '0',
  `agree_mail_subject` varchar(255) default NULL,
  `agree_mail_body`    text,
  `title_metadata_id`  int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`multidatabase_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `multidatabase_block`
-- -

CREATE TABLE multidatabase_block (
  `block_id`           int(11) NOT NULL default '0',
  `multidatabase_id`   int(11) NOT NULL default '0',
  `visible_item`       tinyint(1) NOT NULL default '0',
  `default_sort`       varchar(11) NOT NULL default '',
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
  KEY `multidatabase_id` (`multidatabase_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `multidatabase_metadata`
-- -

CREATE TABLE multidatabase_metadata (
  `metadata_id`        int(11) NOT NULL default '0',
  `multidatabase_id`   int(11) NOT NULL default '0',
  `name`               varchar(255) default NULL,
  `type`               tinyint(1) NOT NULL default '0',
  `display_pos`        tinyint(1) NOT NULL default '0',
  `select_content`     text,
  `require_flag`       tinyint(1) NOT NULL default '0',
  `list_flag`          tinyint(1) NOT NULL default '0',
  `detail_flag`        tinyint(1) NOT NULL default '0',
  `search_flag`        tinyint(1) NOT NULL default '0',
  `name_flag`          tinyint(1) NOT NULL default '0',
  `sort_flag`          tinyint(1) NOT NULL default '0',
  `file_password_flag` tinyint(1) NOT NULL default '0',
  `file_count_flag`    tinyint(1) NOT NULL default '0',
  `mobile_detail_flag` tinyint(1) NOT NULL default '0',
  `display_sequence`   int(11) default NULL,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metadata_id`),
  KEY `multidatabase_id` (`multidatabase_id`,`display_pos`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;


-- --------------------------------------------------------

-- -
-- テーブルの構造 `multidatabase_content`
-- -

CREATE TABLE multidatabase_content (
  `content_id`         int(11) NOT NULL default '0',
  `multidatabase_id`   int(11) NOT NULL default '0',
  `vote`               text,
  `vote_count`         int(11) NOT NULL default '0',
  `agree_flag`         tinyint(1) NOT NULL default '0',
  `temporary_flag`     tinyint(1) NOT NULL default 0,
  `display_sequence`   int(11) default NULL,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`content_id`),
  KEY `multidatabase_id` (`multidatabase_id`, `display_sequence`, `insert_time`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `multidatabase_metadata_content`
-- -

CREATE TABLE multidatabase_metadata_content (
  `metadata_content_id` int(11) NOT NULL default '0',
  `metadata_id`         int(11) NOT NULL default '0',
  `content_id`          int(11) NOT NULL default '0',
  `content`	            text,
  `room_id`	            int(11) NOT NULL default '0',
  `insert_time`         varchar(14) NOT NULL default '',
  `insert_site_id`      varchar(40) NOT NULL default '',
  `insert_user_id`      varchar(40) NOT NULL default '',
  `insert_user_name`    varchar(255) NOT NULL default '',
  `update_time`         varchar(14) NOT NULL default '',
  `update_site_id`      varchar(40) NOT NULL default '',
  `update_user_id`      varchar(40) NOT NULL default '',
  `update_user_name`    varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metadata_content_id`),
  KEY `metadata_id` (`metadata_id`,`content_id`),
  KEY `content_id` (`content_id`),
  KEY `room_id` (`room_id`)
 ,FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `multidatabase_comment`
-- -

CREATE TABLE multidatabase_comment (
  `comment_id`         int(11) NOT NULL default '0',
  `content_id`         int(11) NOT NULL default '0',
  `comment_content`    text NOT NULL,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`comment_id`),
  KEY `content_id` (`content_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `multidatabase_file`
-- -

CREATE TABLE multidatabase_file (
  `metadata_content_id`       int(11) NOT NULL default '0',
  `upload_id`                 int(11) NOT NULL default '0',
  `file_name`                 varchar(255) default NULL,
  `file_password`             varchar(255) default NULL,
  `download_count`            int(11) NOT NULL default '0',
  `physical_file_name`        varchar(255) default NULL,
  `room_id`                   int(11) NOT NULL default '0',
  PRIMARY KEY  (`metadata_content_id`),
  KEY `upload_id` (`upload_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;