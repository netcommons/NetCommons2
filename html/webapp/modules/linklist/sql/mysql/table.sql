-- --------------------------------------------------------

-- -
-- テーブルの構造 `linklist`
-- -

CREATE TABLE `linklist` (
  `linklist_id`        int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `linklist_name`      varchar(255) NOT NULL default '',
  `category_authority` tinyint(3) NOT NULL default '0',
  `link_authority`     tinyint(4) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`linklist_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `linklist_block`
-- -

CREATE TABLE `linklist_block` (
  `block_id`           int(11) NOT NULL default '0',
  `linklist_id`        int(11) NOT NULL default '0',
  `display`            tinyint(1) NOT NULL default '0',
  `target_blank_flag`  tinyint(1) NOT NULL default '0',
  `view_count_flag`    tinyint(1) NOT NULL default '0',
  `line`               varchar(255) NOT NULL default '',
  `mark`               varchar(255) NOT NULL default '',
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
  KEY `linklist_id` (`linklist_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `linklist_category`
-- -

CREATE TABLE `linklist_category` (
  `category_id`        int(11) unsigned NOT NULL default '0',
  `linklist_id`        int(11) NOT NULL default '0',
  `category_name`      varchar(255) NOT NULL default '',
  `category_sequence`  int(11) unsigned NOT NULL default '0',
  `default_flag`       tinyint(1) NOT NULL default '0',
  `room_id`            int(11) unsigned NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`category_id`),
  KEY `linklist_id` (`linklist_id`,`category_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `linklist_link`
-- -

CREATE TABLE `linklist_link` (
  `link_id`            int(11) unsigned NOT NULL default '0',
  `linklist_id`        int(11) NOT NULL default '0',
  `category_id`        int(11) unsigned NOT NULL default '0',
  `link_sequence`      int(11) unsigned NOT NULL default '0',
  `title`              text,
  `url`                text,
  `description`        text,
  `view_count`         int(11) NOT NULL default '0',
  `banner`             text,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`link_id`),
  KEY `linklist_id` (`linklist_id`,`category_id`,`link_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;