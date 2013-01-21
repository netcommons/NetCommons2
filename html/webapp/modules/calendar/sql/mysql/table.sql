-- --------------------------------------------------------

-- -
-- Table Structure `calendar_plan`
-- -

CREATE TABLE `calendar_plan` (
  `calendar_id`        int(11) unsigned NOT NULL,
  `plan_id`            int(11) unsigned NOT NULL default 0,
  `room_id`            int(11) NOT NULL default 0,
  `user_id`            varchar(40) NOT NULL default '',
  `user_name`          varchar(255) NOT NULL default '',
  `title`              varchar(255) NOT NULL default '',
  `title_icon`         varchar(255) NOT NULL default '',
  `allday_flag`        tinyint(1) unsigned NOT NULL default 0,
  `start_date`         varchar(8) NOT NULL default '',
  `start_time`         varchar(6) NOT NULL default '',
  `start_time_full`    varchar(14) NOT NULL default '',
  `end_date`           varchar(8) NOT NULL default '',
  `end_time`           varchar(6) NOT NULL default '',
  `end_time_full`      varchar(14) NOT NULL default '',
  `timezone_offset`    float(3,1) NOT NULL default '0.0',
  `link_module`        varchar(255) NOT NULL default '',
  `link_id`            int(11) unsigned NOT NULL default 0,
  `link_action_name`   varchar(255) NOT NULL default '',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`calendar_id`),
  KEY `plan_id` (`plan_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `calendar_plan_details`
-- -

CREATE TABLE `calendar_plan_details` (
  `plan_id`            int(11) NOT NULL,
  `location`           varchar(255) NOT NULL default '',
  `contact`            varchar(255) NOT NULL default '',
  `description`        text,
  `rrule`              text,
  `room_id`            int(11) NOT NULL default 0,
  PRIMARY KEY  (`plan_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `calendar_manage`
-- -

CREATE TABLE `calendar_manage` (
  `room_id`            int(11) unsigned NOT NULL,
  `add_authority_id`   tinyint unsigned NOT NULL default 0,
  `use_flag`           tinyint unsigned NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `calendar_block`
-- -

CREATE TABLE `calendar_block` (
  `block_id`           int(11) unsigned NOT NULL,
  `display_type`       tinyint unsigned NOT NULL default 0,
  `start_pos`          tinyint unsigned NOT NULL default 0,
  `display_count`      tinyint unsigned NOT NULL default 0,
  `select_room`        tinyint unsigned NOT NULL default 0,
  `myroom_flag`        tinyint unsigned NOT NULL default 0,
  `room_id`            int(11) NOT NULL default 0,
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
-- Table Structure `calendar_select_room`
-- -

CREATE TABLE `calendar_select_room` (
  `block_id`            int(11) unsigned NOT NULL,
  `room_id`             int(11) unsigned NOT NULL,
  PRIMARY KEY  (`block_id`,`room_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
