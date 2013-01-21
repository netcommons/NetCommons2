-- --------------------------------------------------------

-- -
-- テーブルの構造 `todo`
-- -

CREATE TABLE `todo` (
  `todo_id`            int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `todo_name`          varchar(255) NOT NULL default '',
  `task_authority`     tinyint(3) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`todo_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `todo_category`
-- -

CREATE TABLE `todo_category` (
  `category_id`        int(11) NOT NULL default '0',
  `todo_id`            int(11) NOT NULL default '0',
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
  PRIMARY KEY  (`category_id`, `todo_id`),
  KEY `room_id` (`room_id`),
  KEY `todo_id` (`todo_id`,`display_sequence`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `todo_block`
-- -

CREATE TABLE `todo_block` (
  `block_id`           int(11) NOT NULL default '0',
  `todo_id`            int(11) NOT NULL default '0',
  `default_sort`       int(11) NOT NULL default '0',
  `used_category`      tinyint(1) NOT NULL default '0',
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

-- --------------------------------------------------------

-- -
-- テーブルの構造 `todo_task`
-- -

CREATE TABLE `todo_task` (
  `task_id`            int(11) NOT NULL default '0',
  `todo_id`            int(11) NOT NULL default '0',
  `task_sequence`      int(11) default '0',
  `priority`           tinyint(1) NOT NULL default '0',
  `state`              tinyint(1) default '0',
  `period`             varchar(14) default '',
  `calendar_id`        int(11) unsigned NOT NULL default '0',
  `category_id`        int(11) NOT NULL default '0',
  `progress`           int(11) default '0',
  `task_value`         text NOT NULL,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`task_id`),
  KEY `todo_id` (`todo_id`,`category_id`,`task_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
