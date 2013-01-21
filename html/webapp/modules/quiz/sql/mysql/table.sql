-- --------------------------------------------------------

-- -
-- テーブルの構造 `quiz`
-- -

CREATE TABLE `quiz` (
  `quiz_id`              int(11) NOT NULL default '0',
  `room_id`              int(11) NOT NULL default '0',
  `quiz_name`            varchar(255) NOT NULL default '',
  `icon_name`            varchar(128) NOT NULL default '',
  `status`               tinyint(1) NOT NULL default '0',
  `quiz_type`            tinyint(1) NOT NULL default '0',
  `period`               char(14) NOT NULL default '',
  `nonmember_flag`       tinyint(1) NOT NULL default '0',
  `image_authentication` tinyint(1) NOT NULL default '0',
  `repeat_flag`          tinyint(1) NOT NULL default '0',
  `correct_flag`         tinyint(1) NOT NULL default '0',
  `total_flag`           tinyint(1) NOT NULL default '0',
  `perfect_score`        int(11) NOT NULL default '0',
  `quiz_score`           int(11) NOT NULL default '0',
  `answer_count`         int(11) NOT NULL default '0',
  `mail_send`            tinyint(1) NOT NULL default '0',
  `mail_subject`         varchar(255) NOT NULL default '',
  `mail_body`            text,
  `insert_time`          varchar(14) NOT NULL default '',
  `insert_site_id`       varchar(40) NOT NULL default '',
  `insert_user_id`       varchar(40) NOT NULL default '',
  `insert_user_name`     varchar(255) NOT NULL default '',
  `update_time`          varchar(14) NOT NULL default '',
  `update_site_id`       varchar(40) NOT NULL default '',
  `update_user_id`       varchar(40) NOT NULL default '',
  `update_user_name`     varchar(255) NOT NULL default '',
  PRIMARY KEY  (`quiz_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `quiz_block`
-- -

CREATE TABLE `quiz_block` (
  `block_id`           int(11) NOT NULL default '0',
  `quiz_id`            int(11) NOT NULL default '0',
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
  KEY `quiz_id` (`quiz_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `quiz_question`
-- -

CREATE TABLE `quiz_question` (
  `question_id`        int(11) NOT NULL default '0',
  `quiz_id`            int(11) NOT NULL default '0',
  `question_sequence`  int(11) NOT NULL default '0',
  `question_value`     text,
  `question_type`      tinyint(1) NOT NULL default '0',
  `allotment`          int(11) NOT NULL default '0',
  `correct`            text,
  `require_flag`       tinyint(1) NOT NULL default '0',
  `description`        text NOT NULL,
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`question_id`),
  KEY `quiz_id_2` (`quiz_id`,`question_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `quiz_choice`
-- -

CREATE TABLE `quiz_choice` (
  `choice_id`          int(11) NOT NULL default '0',
  `quiz_id`            int(11) NOT NULL default '0',
  `question_id`        int(11) NOT NULL default '0',
  `choice_sequence`    int(11) NOT NULL default '0',
  `choice_value`       text,
  `choice_count`       int(11) NOT NULL default '0',
  `graph`              varchar(16) NOT NULL default '',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`choice_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `question_id` (`question_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `quiz_summary`
-- -

CREATE TABLE `quiz_summary` (
  `summary_id`         int(11) NOT NULL default '0',
  `quiz_id`            int(11) NOT NULL default '0',
  `answer_flag`        tinyint(1) NOT NULL default '0',
  `answer_number`      int(11) NOT NULL default '0',
  `summary_score`      int(11) NOT NULL default '0',
  `answer_time`        varchar(14) NOT NULL default '',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`summary_id`),
  KEY `quiz_id_2` (`quiz_id`,`insert_user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `quiz_answer`
-- -

CREATE TABLE `quiz_answer` (
  `answer_id`          int(11) NOT NULL default '0',
  `quiz_id`            int(11) NOT NULL default '0',
  `question_id`        int(11) NOT NULL default '0',
  `summary_id`         int(11) NOT NULL default '0',
  `answer_value`       text,
  `answer_flag`        tinyint(1) NOT NULL default '0',
  `score`              int(11) NOT NULL default '0',
  `room_id`            int(11) NOT NULL default '0',
  `insert_time`        varchar(14) NOT NULL default '',
  `insert_site_id`     varchar(40) NOT NULL default '',
  `insert_user_id`     varchar(40) NOT NULL default '',
  `insert_user_name`   varchar(255) NOT NULL default '',
  `update_time`        varchar(14) NOT NULL default '',
  `update_site_id`     varchar(40) NOT NULL default '',
  `update_user_id`     varchar(40) NOT NULL default '',
  `update_user_name`   varchar(255) NOT NULL default '',
  PRIMARY KEY  (`answer_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `question_id` (`question_id`),
  KEY `summary_id` (`summary_id`),
  KEY `answer_flag` (`answer_flag`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
