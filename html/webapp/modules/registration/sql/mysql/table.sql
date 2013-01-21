-- --------------------------------------------------------

-- -
-- テーブルの構造`registration`
-- -

CREATE TABLE registration (
  `registration_id`	     int(11) NOT NULL default '0',
  `room_id`	             int(11) NOT NULL default '0',
  `registration_name`    varchar(255) NOT NULL default '',
  `image_authentication` tinyint(1) NOT NULL default '0',
  `limit_number`         int(11) NOT NULL default '0',
  `period`               varchar(14) NOT NULL default '',
  `accept_message`       varchar(255) default NULL,
  `mail_send`            tinyint(1) NOT NULL default '0',
  `regist_user_send`     tinyint(1) NOT NULL default '0',
  `chief_send`           tinyint(1) NOT NULL default '0',
  `rcpt_to`              text,
  `mail_subject`         varchar(255) default NULL,
  `mail_body`            text,
  `active_flag`          tinyint(1) NOT NULL default '0',
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`registration_id`),
  KEY `room_id` (`room_id`,`active_flag`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `registration_block`
-- -

CREATE TABLE registration_block (
  `block_id`           int(11) NOT NULL default '0',
  `registration_id`	   int(11) NOT NULL default '0',
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
  KEY `registration_id` (`registration_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `registration_item`
-- -

CREATE TABLE registration_item (
  `item_id`	           int(11) NOT NULL default '0',
  `registration_id`	   int(11) NOT NULL default '0',
  `item_name`          varchar(255) default NULL,
  `item_sequence`      int(11) default NULL,
  `item_type`          tinyint(1) NOT NULL default '0',
  `option_value`       text,
  `require_flag`	   tinyint(1) NOT NULL default '0',
  `list_flag`          tinyint(1) NOT NULL default '0',
  `sort_flag`          tinyint(1) NOT NULL default '0',
  `description`        text,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`item_id`),
  KEY `registration_id_2` (`registration_id`,`item_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `registration_data`
-- -

CREATE TABLE registration_data (
  `data_id`            int(11) NOT NULL default '0',
  `registration_id`    int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`data_id`),
  KEY `registration_id` (`registration_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `registration_item_data`
-- -

CREATE TABLE registration_item_data (
  `item_data_id`       int(11) NOT NULL default '0',
  `registration_id`    int(11) NOT NULL default '0',
  `item_id`            int(11) NOT NULL default '0',
  `data_id`            int(11) NOT NULL default '0',
  `item_data_value`    text,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`item_data_id`),
  KEY `registration_id` (`registration_id`),
  KEY `item_id` (`item_id`,`data_id`),
  KEY `data_id` (`data_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `registration_file`
-- -

CREATE TABLE registration_file (
  `item_data_id`       int(11) NOT NULL default '0',
  `upload_id`          int(11) NOT NULL default '0',
  `file_name`          varchar(255) default NULL,
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`item_data_id`),
  KEY `upload_id` (`upload_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;