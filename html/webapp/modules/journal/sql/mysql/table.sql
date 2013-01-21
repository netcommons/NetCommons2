-- --------------------------------------------------------

-- -
-- テーブルの構造 `journal`
-- -

CREATE TABLE `journal` (
  `journal_id`                 int(11) NOT NULL default '0',
  `room_id`                    int(11) NOT NULL default '0',
  `journal_name`               varchar(255) NOT NULL default '',
  `active_flag`                tinyint(1) NOT NULL default '0',
  `post_authority`             tinyint(1) NOT NULL default '0',
  `mobile_mail_flag`           tinyint(1) NOT NULL default '0',
  `mail_flag`                  tinyint(1) NOT NULL default '0',
  `mail_authority`             tinyint(1) NOT NULL default '0',
  `mail_subject`               varchar(255) default NULL,
  `mail_body`                  text,
  `vote_flag`                  tinyint(1) NOT NULL default '0',
  `comment_flag`               tinyint(1) NOT NULL default '0',
  `sns_flag`                   tinyint(1) unsigned NOT NULL default '1',
  `new_period`                 int(11) NOT NULL default '0',
  `trackback_transmit_flag`    tinyint(1) NOT NULL default '0',
  `trackback_receive_flag`     tinyint(1) NOT NULL default '0',
  `transmit_blogname`          text,
  `agree_flag`                 tinyint(1) NOT NULL default '0',
  `agree_mail_flag`            tinyint(1) NOT NULL default '0',
  `agree_mail_subject`         varchar(255) default NULL,
  `agree_mail_body`            text,
  `comment_agree_flag`         tinyint(1) NOT NULL default '0',
  `comment_agree_mail_flag`    tinyint(1) NOT NULL default '0',
  `comment_agree_mail_subject` varchar(255) default NULL,
  `comment_agree_mail_body`    text,
  `insert_time`                varchar(14) NOT NULL default '',
  `insert_site_id`             varchar(40) NOT NULL default '',
  `insert_user_id`             varchar(40) NOT NULL default '',
  `insert_user_name`           varchar(255) NOT NULL default '',
  `update_time`                varchar(14) NOT NULL default '',
  `update_site_id`             varchar(40) NOT NULL default '',
  `update_user_id`             varchar(40) NOT NULL default '',
  `update_user_name`           varchar(255) NOT NULL default '',
  PRIMARY KEY  (`journal_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `journal_block`
-- -

CREATE TABLE `journal_block` (
  `block_id`           int(11) NOT NULL default '0',
  `journal_id`         int(11) NOT NULL default '0',
  `visible_item`       int(11) NOT NULL default '0',
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
  KEY `journal_id` (`journal_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `journal_post`
-- -

CREATE TABLE `journal_post` (
  `post_id`            int(11) NOT NULL default '0',
  `journal_id`         int(11) NOT NULL default '0',
  `journal_date`       varchar(14) NOT NULL default '',
  `category_id`        int(11) NOT NULL default '0',
  `root_id`            int(11) NOT NULL default '0',
  `parent_id`          int(11) default NULL,
  `title`              varchar(255) NOT NULL default '',
  `icon_name`          varchar(128) default NULL,
  `content`            text,
  `more_content`       text,
  `more_title`         varchar(255) default NULL,
  `hide_more_title`    varchar(255) default NULL,
  `vote`               text,
  `status`             tinyint(1) NOT NULL default '0',
  `agree_flag`         tinyint(1) NOT NULL default '0',
  `blog_name`          varchar(255) NOT NULL default '',
  `direction_flag`     tinyint(1) NOT NULL default '0',
  `tb_url`             text,
  `link`               text,
  `updateping`         tinyint(1) NOT NULL default '0',
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
  KEY `root_id` (`root_id`),
  KEY `parent_id` (`parent_id`),
  KEY `journal_id_2` (`journal_id`,`journal_date`,`insert_time`),
  KEY `category_id` (`category_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `journal_category`
-- -

CREATE TABLE `journal_category` (
  `category_id`        int(11) NOT NULL default '0',
  `journal_id`         int(11) NOT NULL default '0',
  `category_name`      varchar(255) default NULL,
  `display_sequence`   int(11) default NULL,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`category_id`),
  KEY `room_id` (`room_id`),
  KEY `journal_id` (`journal_id`,`display_sequence`)
) ENGINE=MyISAM;