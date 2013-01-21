-- --------------------------------------------------------

-- -
-- Table Structure `search_blocks`
-- -

CREATE TABLE `search_blocks` (
  `block_id`              int(11) NOT NULL default '0',
  `show_mode`             tinyint(1) NOT NULL default '0',
  `default_target_module` text,
  `detail_flag`           tinyint(1) NOT NULL default '0',
  `room_id`               int(11) NOT NULL default '0',
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;