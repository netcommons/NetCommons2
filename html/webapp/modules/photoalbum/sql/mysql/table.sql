-- --------------------------------------------------------

-- -
-- テーブルの構造`photoalbum`
-- -

CREATE TABLE `photoalbum` (
  `photoalbum_id`      int(11) NOT NULL default '0',
  `room_id`	           int(11) NOT NULL default '0',
  `photoalbum_name`    varchar(255) NOT NULL default '',
  `album_authority`    tinyint(1) NOT NULL default '0',
  `album_new_period`   int(11) default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`photoalbum_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `photoalbum_block`
-- -

CREATE TABLE photoalbum_block (
  `block_id`           int(11) NOT NULL default '0',
  `photoalbum_id`      int(11) NOT NULL default '0',
  `display`            tinyint(1) NOT NULL default '0',
  `display_album_id`   int(11) NOT NULL default '0',
  `slide_type`         tinyint(1) NOT NULL default '0',
  `slide_time`         int(11) NOT NULL default '0',
  `size_flag`          tinyint(1) NOT NULL default '0',
  `width`              int(11) NOT NULL default '0',
  `height`             int(11) NOT NULL default '0',
  `album_visible_row`  tinyint(1) NOT NULL default '0',
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
  KEY `photoalbum_id` (`photoalbum_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `photoalbum_album`
-- -

CREATE TABLE photoalbum_album (
  `album_id`           int(11) NOT NULL default '0',
  `photoalbum_id`      int(11) NOT NULL default '0',
  `album_name`         varchar(255) default NULL,
  `upload_id`          int(11) NOT NULL default '0',
  `album_jacket`       varchar(255) default NULL,
  `width`              int(11) NOT NULL default '0',
  `height`             int(11) NOT NULL default '0',
  `album_sequence`     int(11) default NULL,
  `album_description`  text,
  `photo_count`        int(11) NOT NULL default '0',
  `photo_upload_time`  varchar(14) NOT NULL default '',
  `photo_new_period`   int(11) default '0',
  `vote_flag`          tinyint(1) NOT NULL default '0',
  `album_vote_count`   int(11) NOT NULL default '0',
  `comment_flag`       tinyint(1) NOT NULL default '0',
  `public_flag`        tinyint(1) NOT NULL default '0',
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`album_id`),
  KEY `photoalbum_id` (`photoalbum_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `photoalbum_photo`
-- -

CREATE TABLE photoalbum_photo (
  `photo_id`            int(11) NOT NULL default '0',
  `album_id`            int(11) NOT NULL default '0',
  `photoalbum_id`       int(11) NOT NULL default '0',
  `photo_name`          varchar(255) default NULL,
  `photo_sequence`      int(11) default NULL,
  `upload_id`           int(11) NOT NULL default '0',
  `photo_path`          varchar(255) default NULL,
  `width`               int(11) NOT NULL default '0',
  `height`              int(11) NOT NULL default '0',
  `photo_vote_count`	int(11) NOT NULL default '0',
  `photo_description`	text,
  `room_id`	            int(11) NOT NULL default '0',
  `insert_time`         varchar(14) NOT NULL default '',
  `insert_site_id`      varchar(40) NOT NULL default '',
  `insert_user_id`      varchar(40) NOT NULL default '',
  `insert_user_name`    varchar(255) NOT NULL default '',
  `update_time`         varchar(14) NOT NULL default '',
  `update_site_id`      varchar(40) NOT NULL default '',
  `update_user_id`      varchar(40) NOT NULL default '',
  `update_user_name`    varchar(255) NOT NULL default '',
  PRIMARY KEY  (`photo_id`),
  KEY `album_id` (`album_id`,`photo_sequence`),
  KEY `photoalbum_id` (`photoalbum_id`,`album_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `photoalbum_comment`
-- -

CREATE TABLE photoalbum_comment (
  `comment_id`         int(11) NOT NULL default '0',
  `photo_id`           int(11) NOT NULL default '0',
  `album_id`            int(11) NOT NULL default '0',
  `photoalbum_id`      int(11) NOT NULL default '0',
  `comment_value`      text,
  `room_id`	           int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`comment_id`),
  KEY `photo_id` (`photo_id`),
  KEY `album_id` (`album_id`),
  KEY `photoalbum_id` (`photoalbum_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `photoalbum_user_photo`
-- -

CREATE TABLE `photoalbum_user_photo` (
  `user_id`            varchar(40) NOT NULL,
  `photo_id`           int(11) NOT NULL default '0',
  `vote_flag`          tinyint(1) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
