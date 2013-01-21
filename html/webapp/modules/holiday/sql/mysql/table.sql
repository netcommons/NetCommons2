-- --------------------------------------------------------

-- -
-- Table Structure `holiday`
-- -

CREATE TABLE `holiday` (
  `holiday_id`         int(11) unsigned NOT NULL,
  `rrule_id`           int(11) unsigned NOT NULL default '0',
  `lang_dirname`       varchar(64) NOT NULL default '',
  `holiday`            varchar(14) NOT NULL default '',
  `summary`            varchar(255) NOT NULL default '',
  `holiday_type`       tinyint unsigned NOT NULL default '0',
  PRIMARY KEY  (`holiday_id`),
  KEY `lang_dirname` (`lang_dirname`,`holiday`),
  KEY `rrule_id` (`rrule_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `holiday_rrule`
-- -

CREATE TABLE `holiday_rrule` (
  `rrule_id`           int(11) unsigned NOT NULL,
  `varidable_flag`     tinyint unsigned NOT NULL default '0',
  `substitute_flag`    tinyint unsigned NOT NULL default '0',
  `start_time`         varchar(14) NOT NULL default '',
  `end_time`           varchar(14) NOT NULL default '',
  `rrule`              text,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rrule_id`)
) ENGINE=MyISAM;
