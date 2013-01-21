-- -
-- テーブルの構造 `pm_message`
-- -
CREATE TABLE `pm_message` (
  `message_id`              int(11) unsigned NOT NULL,
  `room_id`                 int(11) NOT NULL default '0',
  `reply_top_message_id`    int(11) NOT NULL default '0',
  `reply_last_message_id`	int(11) NOT NULL default '0',
  `subject`					varchar(255) NOT NULL default '',
  `body`					text,
  `sent_time`				varchar(14) NOT NULL default '',
  `image_count`				int(11) NOT NULL default '0',
  `file_count`				int(11) NOT NULL default '0',
  `receivers_list`			text,
  `send_all_flag`			tinyint(1) NOT NULL default '0',
  `insert_time`				varchar(14) NOT NULL default '',
  `insert_site_id`			varchar(40) NOT NULL default '',
  `insert_user_id`			varchar(40) NOT NULL default '',
  `insert_user_name`		varchar(255) NOT NULL default '',
  `update_time`				varchar(14) NOT NULL default '',
  `update_site_id`			varchar(40) NOT NULL default '',
  `update_user_id`			varchar(40) NOT NULL default '',
  `update_user_name`		varchar(255) NOT NULL default '',
  PRIMARY KEY  (`message_id`),
  INDEX idx_pm_message_reply_top_message_id (reply_top_message_id),
  INDEX idx_pm_message_reply_last_message_id (reply_last_message_id),
  INDEX idx_pm_message_insert_user_id (insert_user_id)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pm_message_receiver`
-- -
CREATE TABLE `pm_message_receiver` (
  `receiver_id`           int(11) unsigned NOT NULL,
  `message_id`            int(11) NOT NULL default '0',
  `receiver_user_id`      varchar(40) NOT NULL default '',
  `receiver_user_name`	  varchar(255) NOT NULL default '',
  `mailbox`               tinyint(1) NOT NULL default '0',
  `read_state`            tinyint(1) NOT NULL default '0',
  `delete_state`          tinyint(1) NOT NULL default '0',
  `importance_flag`       tinyint(1) NOT NULL default '0',
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`receiver_id`),
  KEY `idx_pm_message_receiver_message_id` (`message_id`),
  KEY `receiver_user_id` (`receiver_user_id`,`delete_state`,`mailbox`,`read_state`,`importance_flag`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pm_tag`
-- -
CREATE TABLE `pm_tag` (
  `tag_id`                int(11) unsigned NOT NULL,
  `tag_name`              varchar(255) NOT NULL default '',
  `display_sequence`      int(11) NOT NULL default '0',
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`tag_id`),
  INDEX idx_pm_tag_insert_user_id (insert_user_id)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pm_message_tag_link`
-- -
CREATE TABLE `pm_message_tag_link` (
  `tag_id`                int(11) NOT NULL default '0',
  `receiver_id`			  int(11) NOT NULL default '0',
  `message_id`            int(11) NOT NULL default '0',
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`tag_id`,`receiver_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `insert_user_id` (`insert_user_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pm_filter`
-- -
CREATE TABLE `pm_filter` (
  `filter_id`             int(11) unsigned NOT NULL,
  `senders`               text NULL,
  `subject`           	  varchar(255) NOT NULL default '',
  `keyword_list`		  text,
  `apply_inbox_flag`      tinyint(1) NOT NULL default '0',
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`filter_id`),
  INDEX idx_pm_filter_insert_user_id (insert_user_id)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pm_filter_action`
-- -
CREATE TABLE `pm_filter_action` (
  `action_id`             int(11) unsigned NOT NULL,
  `action_description`    varchar(255) NOT NULL default '',
  `handle_action_name`    varchar(255) NOT NULL default '',
  `show_action_name`      varchar(255) NOT NULL default '',
  `execute_sequence`      int(11) NOT NULL default '0',
  `break_afterme_flag`    tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`action_id`),
  INDEX idx_pm_filter_action_execute_sequence (execute_sequence)
) ENGINE=MyISAM;

INSERT INTO pm_filter_action (action_id, action_description, handle_action_name, show_action_name, execute_sequence, break_afterme_flag) VALUES ('1', 'PM_FILTER_MARK_READ', 'markRead', 'markRead', '1', '0');

INSERT INTO pm_filter_action (action_id, action_description, handle_action_name, show_action_name, execute_sequence, break_afterme_flag) VALUES ('2', 'PM_FILTER_ADD_FLAG', 'addFlag', 'addFlag', '1', '0');

INSERT INTO pm_filter_action (action_id, action_description, handle_action_name, show_action_name, execute_sequence, break_afterme_flag) VALUES ('3', 'PM_FILTER_ADD_TAG', 'addTag', 'addTag', '1', '0');

INSERT INTO pm_filter_action (action_id, action_description, handle_action_name, show_action_name, execute_sequence, break_afterme_flag) VALUES ('4', 'PM_FILTER_FORWARD', 'forward', 'forward', '2', '0');

INSERT INTO pm_filter_action (action_id, action_description, handle_action_name, show_action_name, execute_sequence, break_afterme_flag) VALUES ('5', 'PM_FILTER_REMOVE', 'remove', 'remove', '0', '1');

-- -
-- テーブルの構造 `pm_filter_action_link`
-- -
CREATE TABLE `pm_filter_action_link` (
  `filter_id`             int(11) NOT NULL default '0',
  `action_id`             int(11) NOT NULL default '0',
  `action_parameters`     text,
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`filter_id`,`action_id`),
  KEY `insert_user_id` (`insert_user_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pm_forward`
-- -
CREATE TABLE `pm_forward` (
  `forward_id`            int(11) unsigned NOT NULL,
  `forward_state`         tinyint(11) NOT NULL default '0',
  `insert_time`           varchar(14) NOT NULL default '',
  `insert_site_id`        varchar(40) NOT NULL default '',
  `insert_user_id`        varchar(40) NOT NULL default '',
  `insert_user_name`      varchar(255) NOT NULL default '',
  `update_time`           varchar(14) NOT NULL default '',
  `update_site_id`        varchar(40) NOT NULL default '',
  `update_user_id`        varchar(40) NOT NULL default '',
  `update_user_name`      varchar(255) NOT NULL default '',
  PRIMARY KEY  (`forward_id`),
  INDEX pm_forward_insert_user_id (insert_user_id)
) ENGINE=MyISAM;
