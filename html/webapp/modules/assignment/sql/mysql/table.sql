-- --------------------------------------------------------

-- -
-- Table Structure `assignment`
-- -

CREATE TABLE `assignment` (
  `assignment_id`      int(11) NOT NULL default 0,
  `room_id`            int(11) NOT NULL default 0,
  `body_id`            int(11) NOT NULL default 0,
  `assignment_name`    varchar(255) NOT NULL default '',
  `icon_name`          varchar(255) NOT NULL default '',
  `activity`           tinyint(1) NOT NULL default 0,
  `period`             varchar(14) NOT NULL default '',
  `grade_authority`    tinyint(1) NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`assignment_id`),
  KEY `activity` (`activity`,`period`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_mail`
-- -

CREATE TABLE `assignment_mail` (
  `assignment_id`      int(11) NOT NULL default 0,
  `mail_send`          tinyint(1) NOT NULL default 0,
  `mail_subject`       varchar(255) NOT NULL default '',
  `mail_body`          text,
  `room_id`            int(11) NOT NULL default 0,
  PRIMARY KEY  (`assignment_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_body`
-- -

CREATE TABLE `assignment_body` (
  `body_id`            int(11) NOT NULL default 0,
  `assignment_id`      int(11) NOT NULL default 0,
  `report_id`          int(11) NOT NULL default 0,
  `body`               text,
  `room_id`            int(11) NOT NULL default 0,
  PRIMARY KEY  (`body_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `report_id` (`report_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_block`
-- -

CREATE TABLE `assignment_block` (
  `block_id`           int(11) NOT NULL default 0,
  `assignment_id`      int(11) NOT NULL default 0,
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
  KEY `assignment_id` (`assignment_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_submitter`
-- -

CREATE TABLE `assignment_submitter` (
  `submit_id`          int(11) NOT NULL default 0,
  `assignment_id`      int(11) NOT NULL default 0,
  `submit_flag`        tinyint(1) NOT NULL default 0,
  `grade_value`        varchar(255) NOT NULL default '',
  `room_id`            int(11) NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`submit_id`),
  KEY `assignment_id` (`assignment_id`,`insert_user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_report`
-- -

CREATE TABLE `assignment_report` (
  `report_id`          int(11) NOT NULL default 0,
  `assignment_id`      int(11) NOT NULL default 0,
  `submit_id`          int(11) NOT NULL default 0,
  `status`             tinyint(1) NOT NULL default 0,
  `body_id`            int(11) NOT NULL default 0,
  `room_id`            int(11) NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`report_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `body_id` (`body_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_comment`
-- -

CREATE TABLE `assignment_comment` (
  `comment_id`         int(11) NOT NULL default 0,
  `assignment_id`      int(11) NOT NULL default 0,
  `report_id`          int(11) NOT NULL default 0,
  `comment_value`      text,
  `room_id`            int(11) NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`comment_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `report_id` (`report_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------


-- -
-- Table Structure `assignment_grade_value`
-- -

CREATE TABLE `assignment_grade_value` (
  `grade_value`        varchar(255) NOT NULL,
  `room_id`            int(11) NOT NULL default 0,
  `display_sequence`   int(11) NOT NULL default 0,
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`grade_value`,`room_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
