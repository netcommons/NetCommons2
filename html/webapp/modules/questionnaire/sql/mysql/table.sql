-- --------------------------------------------------------

-- -
-- テーブルの構造 `questionnaire`
-- -

CREATE TABLE `questionnaire` (
  `questionnaire_id`     int(11) NOT NULL default '0',
  `room_id`              int(11) NOT NULL default '0',
  `questionnaire_name`   varchar(255) NOT NULL default '',
  `icon_name`            varchar(128) NOT NULL default '',
  `status`               tinyint(1) NOT NULL default '0',
  `questionnaire_type`   tinyint(1) NOT NULL default '0',
  `period`               char(14) NOT NULL default '',
  `nonmember_flag`       tinyint(1) NOT NULL default '0',
  `image_authentication` tinyint(1) NOT NULL default '0',
  `anonymity_flag`       tinyint(1) NOT NULL default '0',
  `keypass_use_flag`     tinyint(1) NOT NULL default '0',
  `keypass_phrase`       varchar(128) NOT NULL default '',
  `repeat_flag`          tinyint(1) NOT NULL default '0',
  `total_flag`           tinyint(1) NOT NULL default '0',
  `answer_show_flag`     tinyint(1) NOT NULL default '0',
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
  PRIMARY KEY  (`questionnaire_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `questionnaire_block`
-- -

CREATE TABLE `questionnaire_block` (
  `block_id`           int(11) NOT NULL default '0',
  `questionnaire_id`   int(11) NOT NULL default '0',
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
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `questionnaire_question`
-- -

CREATE TABLE `questionnaire_question` (
  `question_id`        int(11) NOT NULL default '0',
  `questionnaire_id`   int(11) NOT NULL default '0',
  `question_sequence`  int(11) NOT NULL default '0',
  `question_value`     text,
  `question_type`      tinyint(1) NOT NULL default '0',
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
  KEY `questionnaire_id_2` (`questionnaire_id`,`question_sequence`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `questionnaire_choice`
-- -

CREATE TABLE `questionnaire_choice` (
  `choice_id`          int(11) NOT NULL default '0',
  `questionnaire_id`   int(11) NOT NULL default '0',
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
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `question_id` (`question_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `questionnaire_summary`
-- -

CREATE TABLE `questionnaire_summary` (
  `summary_id`         int(11) NOT NULL default '0',
  `questionnaire_id`   int(11) NOT NULL default '0',
  `answer_flag`        tinyint(1) NOT NULL default '0',
  `answer_number`      int(11) NOT NULL default '0',
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
  KEY `questionnaire_id_2` (`questionnaire_id`,`insert_user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- -
-- テーブルの構造 `questionnaire_answer`
-- -

CREATE TABLE `questionnaire_answer` (
  `answer_id`          int(11) NOT NULL default '0',
  `questionnaire_id`   int(11) NOT NULL default '0',
  `question_id`        int(11) NOT NULL default '0',
  `summary_id`         int(11) NOT NULL default '0',
  `answer_value`       text,
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
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `question_id` (`question_id`),
  KEY `summary_id` (`summary_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;
