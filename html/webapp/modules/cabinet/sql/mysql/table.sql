-- --------------------------------------------------------

-- -
-- Table Structure `cabinet_manage`
-- -

CREATE TABLE `cabinet_manage` (
  `cabinet_id`         int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `cabinet_name`       varchar(255) NOT NULL default '',
  `active_flag`        tinyint(1) NOT NULL default '0',
  `add_authority_id`   tinyint(1) NOT NULL default '0',
  `cabinet_max_size`   int(11) NOT NULL default '0',
  `upload_max_size`    int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`cabinet_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `cabinet_block`
-- -

CREATE TABLE `cabinet_block` (
  `block_id`           int(11) NOT NULL default '0',
  `cabinet_id`         int(11) NOT NULL default '0',
  `disp_line`          int(11) NOT NULL default '5',
  `disp_standard_btn`  tinyint(1) NOT NULL default '1',
  `disp_address`       tinyint(1) NOT NULL default '1',
  `disp_folder`        tinyint(1) NOT NULL default '1',
  `disp_size`          tinyint(1) NOT NULL default '1',
  `disp_download_num`  tinyint(1) NOT NULL default '0',
  `disp_comment`       tinyint(1) NOT NULL default '0',
  `disp_insert_user`   tinyint(1) NOT NULL default '1',
  `disp_insert_date`   tinyint(1) NOT NULL default '1',
  `disp_update_user`   tinyint(1) NOT NULL default '0',
  `disp_update_date`   tinyint(1) NOT NULL default '0',
  `default_folder_id`  int(11) NOT NULL default '0',
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
  KEY `cabinet_id` (`cabinet_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `cabinet_file`
-- -

CREATE TABLE `cabinet_file` (
  `file_id`            int(11) NOT NULL default '0',
  `cabinet_id`         int(11) NOT NULL default '0',
  `upload_id`          int(11) NOT NULL default '0',
  `parent_id`          int(11) NOT NULL default '0',
  `file_name`          varchar(255) NOT NULL default '',
  `extension`          varchar(255) NOT NULL default '',
  `depth`              int(11) NOT NULL default '0',
  `size`               int(11) NOT NULL default '0',
  `download_num`       int(11) NOT NULL default '0',
  `file_type`          tinyint(1) NOT NULL default '0',
  `display_sequence`   int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`file_id`),
  KEY `cabinet_id` (`cabinet_id`,`file_type`),
  KEY `parent_id` (`parent_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- Table Structure `cabinet_file_comment`
-- -

CREATE TABLE `cabinet_comment` (
  `file_id`            int(11) NOT NULL default '0',
  `comment`            text    NOT NULL,
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;