-- --------------------------------------------------------

-- -
-- Table Structure `reservation_reserve`
-- -

CREATE TABLE `reservation_reserve` (
  `reserve_id`         int(11) unsigned NOT NULL,
  `reserve_details_id` int(11) unsigned NOT NULL default '0',
  `location_id`        int(11) unsigned NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `user_id`            varchar(40) NOT NULL default '',
  `user_name`          varchar(255) NOT NULL default '',
  `calendar_id`        int(11) unsigned NOT NULL default '0',
  `title`              varchar(255) NOT NULL default '',
  `title_icon`         varchar(255) NOT NULL default '',
  `allday_flag`        tinyint unsigned NOT NULL default '0',
  `start_date`         varchar(8) NOT NULL default '',
  `start_time`         varchar(6) NOT NULL default '',
  `start_time_full`    varchar(14) NOT NULL default '',
  `end_date`           varchar(8) NOT NULL default '',
  `end_time`           varchar(6) NOT NULL default '',
  `end_time_full`      varchar(14) NOT NULL default '',
  `timezone_offset`    float(3,1) NOT NULL default '0.0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`reserve_id`),
  KEY `location_id` (`location_id`,`start_time_full`),
  KEY `room_id` (`room_id`, `location_id`, `reserve_details_id`),
  KEY `reserve_details_id` (`reserve_details_id`,`start_time_full`,`end_time_full`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `reservation_reserve_details`
-- -

CREATE TABLE `reservation_reserve_details` (
  `reserve_details_id` int(11) NOT NULL,
  `contact`            varchar(255) NOT NULL default '',
  `description`        text,
  `rrule`              text,
  `location_id`        int(11) unsigned NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`reserve_details_id`),
  KEY `room_id` (`room_id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `reservation_block`
-- -

CREATE TABLE `reservation_block` (
  `block_id`           int(11) unsigned NOT NULL,
  `display_type`       tinyint unsigned NOT NULL default '0',
  `display_timeframe`  tinyint(1) NOT NULL default '0',
  `display_start_time` varchar(64) NOT NULL default '',
  `display_interval`   tinyint unsigned NOT NULL default '0',
  `category_id`        int(11) unsigned NOT NULL default '0',
  `location_id`        int(11) unsigned NOT NULL default '0',
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
-- Table Structure `reservation_location`
-- -

CREATE TABLE `reservation_location` (
  `location_id`        int(11) unsigned NOT NULL,
  `category_id`        int(11) unsigned NOT NULL default '0',
  `location_name`      varchar(255) NOT NULL default '',
  `active_flag`        tinyint unsigned NOT NULL default '0',
  `add_authority`      tinyint unsigned NOT NULL default '0',
  `time_table`         varchar(32) NOT NULL default '',
  `start_time`         varchar(14) NOT NULL default '',
  `end_time`           varchar(14) NOT NULL default '',
  `timezone_offset`    float(3,1) NOT NULL default '0.0',
  `duplication_flag`   tinyint unsigned NOT NULL default '0',
  `use_private_flag`   tinyint unsigned NOT NULL default '0',
  `use_auth_flag`      tinyint unsigned NOT NULL default '0',
  `allroom_flag`       tinyint unsigned NOT NULL default '0',
  `display_sequence`   int(11) unsigned NOT NULL default '0',
  `room_id`            int(11) default NULL,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`location_id`),
  KEY `category_id` (`category_id`,`display_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `reservation_location_details`
-- -

CREATE TABLE `reservation_location_details` (
  `location_id`        int(11) NOT NULL,
  `contact`            varchar(255) NOT NULL default '',
  `description`        text,
  `room_id`            int(11) default NULL,
  PRIMARY KEY  (`location_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `reservation_timeframe`
-- -

CREATE TABLE `reservation_timeframe` (
  `timeframe_id`        int(11) unsigned NOT NULL,
  `timeframe_name`      varchar(255) NOT NULL default '',
  `start_time`         varchar(14) NOT NULL default '',
  `end_time`           varchar(14) NOT NULL default '',
  `timezone_offset`    float(3,1) NOT NULL default '0.0',
  `timeframe_color`    varchar(16) NOT NULL default '',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`timeframe_id`)
) ENGINE=MyISAM;


-- --------------------------------------------------------

-- -
-- Table Structure `reservation_location_rooms`
-- -

CREATE TABLE `reservation_location_rooms` (
  `location_id`        int(11) unsigned NOT NULL,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`location_id`,`room_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `reservation_category`
-- -

CREATE TABLE `reservation_category` (
  `category_id`        int(11) unsigned NOT NULL,
  `category_name`      varchar(255) NOT NULL default '',
  `display_sequence`   int(11) unsigned NOT NULL default '0',
  `room_id`            int(11) default NULL,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`category_id`),
  KEY `category_name` (`category_name`),
  KEY `display_sequence` (`display_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
