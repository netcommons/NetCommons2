-- --------------------------------------------------------

-- -
-- Table Structure `mobile_modules`
-- -

CREATE TABLE `mobile_modules` (
  `module_id`          int(11) unsigned NOT NULL,
  `upload_id`          int(11) NOT NULL default 0,
  `mobile_action_name` varchar(255) NOT NULL default '',
  `use_flag`           tinyint(1) NOT NULL default 0,
  `display_position`   tinyint(1) NOT NULL default 0,
  `display_sequence`   int(11) NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`module_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `mobile_users`
-- -

CREATE TABLE `mobile_users` (
  `user_id`            varchar(40) NOT NULL,
  `tel_id`             varchar(255) NOT NULL default '',
  `login_id`           varchar(128) NOT NULL default '',
  `password`           varchar(128) NOT NULL default '',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `mobile_menu_detail`
-- -

CREATE TABLE `mobile_menu_detail` (
  `block_id`           int(11) NOT NULL,
  `page_id`            int(11) NOT NULL,
  `visibility_flag`    tinyint(1) NOT NULL default '1',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`,`page_id`)
)ENGINE=MyISAM;