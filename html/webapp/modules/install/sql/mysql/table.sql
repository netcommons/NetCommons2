-- -
-- テーブルの構造 `blocks`
-- -

CREATE TABLE `blocks` (
  `block_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `module_id` int(11) NOT NULL default '0',
  `site_id` varchar(40) NOT NULL default '',
  `root_id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `thread_num` int(11) NOT NULL default '0',
  `col_num` int(11) NOT NULL default '0',
  `row_num` int(11) NOT NULL default '0',
  `url` text NOT NULL,
  `action_name` varchar(255) NOT NULL default '',
  `parameters` text,
  `block_name` varchar(255) NOT NULL default '',
  `theme_name` varchar(255) NOT NULL default '',
  `temp_name` varchar(255) NOT NULL default '',
  `leftmargin` int(11) default NULL,
  `rightmargin` int(11) default NULL,
  `topmargin` int(11) default NULL,
  `bottommargin` int(11) default NULL,
  `min_width_size` int(11) default NULL,
  `shortcut_flag` tinyint(1) NOT NULL default '0',
  `copyprotect_flag` tinyint(1) NOT NULL default '0',
  `display_scope` tinyint(1) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`block_id`),
  KEY `page_id` (`page_id`),
  KEY `module_id` (`module_id`),
  KEY `root_id` (`root_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pages`
-- -

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `site_id` varchar(40) NOT NULL default '',
  `root_id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `thread_num` int(11) NOT NULL default '0',
  `display_sequence` int(11) NOT NULL default '0',
  `url` text NOT NULL,
  `action_name` varchar(255) NOT NULL default '',
  `parameters` text,
  `lang_dirname` varchar(64) NOT NULL default '',
  `page_name` varchar(255) NOT NULL default '',
  `permalink` varchar(255) NOT NULL default '',
  `show_count` int(11) NOT NULL default '0',
  `private_flag` tinyint(1) NOT NULL default '0',
  `default_entry_flag` tinyint(1) unsigned NOT NULL default '0',
  `space_type` tinyint(1) NOT NULL default '0',
  `node_flag` tinyint(1) NOT NULL default '0',
  `shortcut_flag` tinyint(1) NOT NULL default '0',
  `copyprotect_flag` tinyint(1) NOT NULL default '0',
  `display_scope` tinyint(1) NOT NULL default '4',
  `display_position` tinyint(1) NOT NULL default '0',
  `display_flag` tinyint(1) NOT NULL default '1',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`page_id`),
  KEY `room_id` (`room_id`,`lang_dirname`),
  KEY `permalink_2` (`permalink`),
  KEY `parent_id` (`parent_id`),
  KEY `space_type` (`space_type`,`private_flag`,`insert_user_id`),
  KEY `root_id_2` (`root_id`),
  KEY `space_type_2` (`space_type`,`private_flag`,`thread_num`)
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `pages_modules_link`
-- -

CREATE TABLE `pages_modules_link` (
  `room_id` int(11) unsigned NOT NULL default '0',
  `site_id` varchar(40) NOT NULL default '',
  `module_id` int(11) unsigned NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`room_id`,`site_id`,`module_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pages_style`
-- -

CREATE TABLE `pages_style` (
  `set_page_id` int(11) NOT NULL default '0',
  `theme_name` varchar(255) NOT NULL default '',
  `temp_name` varchar(255) NOT NULL default '',
  `header_flag` tinyint(1) NOT NULL default '0',
  `footer_flag` tinyint(1) NOT NULL default '0',
  `leftcolumn_flag` tinyint(1) NOT NULL default '0',
  `rightcolumn_flag` tinyint(1) NOT NULL default '0',
  `body_style` varchar(255) NOT NULL default '',
  `header_style` varchar(255) NOT NULL default '',
  `footer_style` varchar(255) NOT NULL default '',
  `leftcolumn_style` varchar(255) NOT NULL default '',
  `centercolumn_style` varchar(255) NOT NULL default '',
  `rightcolumn_style` varchar(255) NOT NULL default '',
  `align` varchar(50) NOT NULL default '',
  `leftmargin` int(11) default NULL,
  `rightmargin` int(11) default NULL,
  `topmargin` int(11) default NULL,
  `bottommargin` int(11) default NULL,
  PRIMARY KEY  (`set_page_id`)
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `pages_users_link`
-- -

CREATE TABLE `pages_users_link` (
  `room_id` int(11) NOT NULL default '0',
  `user_id` varchar(40) NOT NULL default '',
  `role_authority_id` int(11) NOT NULL default '0',
  `createroom_flag` tinyint(1) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`room_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `users`
-- -

CREATE TABLE `users` (
  `user_id` varchar(40) NOT NULL default '0',
  `login_id` varchar(128) NOT NULL default '',
  `password` varchar(128) NOT NULL default '',
  `handle` varchar(128) NOT NULL default '',
  `role_authority_id` int(11) unsigned NOT NULL default '0',
  `active_flag` tinyint(3) unsigned NOT NULL default '0',
  `system_flag` tinyint(3) unsigned NOT NULL default '0',
  `activate_key` varchar(8) NOT NULL default '',
  `lang_dirname` varchar(64) NOT NULL default '',
  `timezone_offset` float(3,1) NOT NULL default '0.0',
  `password_regist_time` varchar(14) NOT NULL default '',
  `last_login_time` varchar(14) NOT NULL default '',
  `previous_login_time` varchar(14) NOT NULL default '',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `login_id` (`login_id`),
  KEY `handle` (`handle`),
  KEY `active_flag` (`active_flag`,`system_flag`,`role_authority_id`),
  KEY `activate_key` (`activate_key`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `users_items_link`
-- -

CREATE TABLE `users_items_link` (
  `user_id` varchar(40) NOT NULL default '',
  `item_id` int(11) unsigned NOT NULL default '0',
  `public_flag` tinyint(3) unsigned NOT NULL default '0',
  `email_reception_flag` tinyint(3) unsigned NOT NULL default '0',
  `content` text,
  PRIMARY KEY  (`user_id`,`item_id`),
  KEY `item_id` (`item_id`,`email_reception_flag`),
  KEY `content` (`content`(255))
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `users_sites_link`(現状、未使用)
-- -

CREATE TABLE `users_sites_link` (
  `user_id` varchar(40) NOT NULL default '',
  `site_id` varchar(40) NOT NULL default '',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`site_id`)
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `items`
-- -


CREATE TABLE `items` (
  `item_id` int(11) unsigned NOT NULL default '0',
  `item_name` varchar(255) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `tag_name` varchar(255) NOT NULL default '',
  `system_flag` tinyint(1) unsigned NOT NULL default '0',
  `require_flag` tinyint(1) unsigned NOT NULL default '0',
  `define_flag` tinyint(1) unsigned NOT NULL default '0',
  `display_flag` tinyint(1) NOT NULL default '1',
  `allow_public_flag` tinyint(1) unsigned NOT NULL default '0',
  `allow_email_reception_flag` tinyint(1) NOT NULL default '0',
  `col_num` int(11) unsigned NOT NULL default '0',
  `row_num` int(11) unsigned NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`item_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `items_authorities_link`
-- -

CREATE TABLE `items_authorities_link` (
  `item_id` int(11) unsigned NOT NULL default '0',
  `user_authority_id` tinyint(3) unsigned NOT NULL default '0',
  `under_public_flag` tinyint(1) NOT NULL default '0',
  `self_public_flag` tinyint(1) NOT NULL default '0',
  `over_public_flag` tinyint(1) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`item_id`,`user_authority_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `items_desc`
-- -

CREATE TABLE `items_desc` (
  `item_id` int(11) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  `attribute` text NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `items_options`
-- -

CREATE TABLE `items_options` (
  `item_id` int(11) unsigned NOT NULL default '0',
  `options` text NOT NULL,
  `default_selected` text NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `config`
-- -

CREATE TABLE `config` (
  `conf_id` int(11) unsigned NOT NULL default '0',
  `conf_modid` int(11) unsigned NOT NULL default '0',
  `conf_catid` int(11) unsigned NOT NULL default '0',
  `conf_name` varchar(64) NOT NULL default '',
  `conf_title` varchar(64) NOT NULL default '',
  `conf_value` text NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`conf_id`),
  KEY `conf_mod_cat_id` (`conf_modid`,`conf_catid`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `css_files`
-- -

CREATE TABLE `css_files` (
  `dir_name` varchar(255) NOT NULL default '',
  `type` tinyint(3) NOT NULL default '0',
  `block_id` int(11) NOT NULL default '0',
  `data` mediumtext,
  `system_flag` tinyint(3) NOT NULL default '0',
  `common_general_flag` tinyint(3) NOT NULL default '0',
  `common_admin_flag` tinyint(3) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`dir_name`,`type`,`block_id`),
  KEY `update_time` (`update_time`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `javascript_files`
-- -

CREATE TABLE `javascript_files` (
  `dir_name` varchar(255) NOT NULL default '',
  `data` mediumtext,
  `read_order` int(11) NOT NULL default '0',
  `system_flag` tinyint(3) NOT NULL default '0',
  `common_general_flag` tinyint(3) NOT NULL default '0',
  `common_admin_flag` tinyint(3) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`dir_name`),
  KEY `read_order` (`read_order`),
  KEY `update_time` (`update_time`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `language`
-- -

CREATE TABLE `language` (
  `lang_dirname` varchar(64) NOT NULL default '',
  `language` varchar(8) NOT NULL default '',
  `display_name` varchar(255) NOT NULL default '',
  `display_sequence` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`lang_dirname`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `monthly_number`
-- -

CREATE TABLE `monthly_number` (
  `user_id` varchar(40) NOT NULL default '',
  `room_id` int(11) unsigned NOT NULL default '0',
  `module_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `year` int(4) NOT NULL default '0',
  `month` int(2) NOT NULL default '0',
  `number` int(11) unsigned NOT NULL default '0',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  KEY `user_id` (`user_id`,`room_id`,`module_id`,`name`,`year`,`month`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `session`
-- -

CREATE TABLE `session` (
  `sess_id` varchar(32) NOT NULL default '',
  `base_sess_id` varchar(32) NOT NULL default '',
  `old_flag` tinyint(1) NOT NULL default '0',
  `sess_updated` varchar(14) NOT NULL default '0',
  `sess_ip` varchar(15) NOT NULL default '',
  `sess_data` mediumtext NOT NULL,
  PRIMARY KEY  (`sess_id`),
  KEY `updated` (`sess_updated`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `shortcut`
-- -

CREATE TABLE `shortcut` (
  `page_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `module_id` int(11) NOT NULL default '0',
  `block_id` int(11) NOT NULL default '0',
  `unique_id` int(11) NOT NULL default '0',
  `shortcut_site_id` int(11) NOT NULL default '0',
  `shortcut_page_id` int(11) NOT NULL default '0',
  `shortcut_room_id` int(11) NOT NULL default '0',
  `shortcut_block_id` int(11) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default ''
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `sites`
-- -

CREATE TABLE `sites` (
  `site_id` varchar(40) NOT NULL default '',
  `url` text NOT NULL,
  `self_flag` tinyint(1) NOT NULL default '0',
  `commons_flag` tinyint(1) unsigned NOT NULL default '0',
  `certify_flag` tinyint(1) unsigned NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `smarty_cache`
-- -

CREATE TABLE `smarty_cache` (
  `tpl_file` varchar(255) NOT NULL default '',
  `expire_time` varchar(14) NOT NULL default '',
  `compile_id` varchar(255) NOT NULL default '',
  `block_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `_user_id` varchar(40) NOT NULL default '',
  `_auth_id` tinyint(3) NOT NULL default '0',
  `_user_auth_id` tinyint(3) NOT NULL default '0',
  `_mobile_flag` tinyint(3) NOT NULL default '0',
  `_ssl_flag` tinyint(3) NOT NULL default '0',
  `lang_dirname` varchar(64) NOT NULL default '',
  `session_id` varchar(128) NOT NULL default '',
  `action_name` varchar(255) NOT NULL default '',
  `parameters` text NOT NULL,
  `cache_content` mediumtext NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  KEY `action_name2` (`block_id`,`_auth_id`,`action_name`),
  FULLTEXT KEY `parameters` (`parameters`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_attribute`
-- -

CREATE TABLE `textarea_attribute` (
  `attribute` varchar(255) NOT NULL default '',
  `value_regexp` text NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `attribute` (`attribute`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_attribute_protocol`
-- -

CREATE TABLE `textarea_attribute_protocol` (
  `attribute` varchar(255) NOT NULL default '',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `attribute` (`attribute`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_protocol`
-- -

CREATE TABLE `textarea_protocol` (
  `protocol` varchar(255) NOT NULL default '',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `protocol` (`protocol`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_style`
-- -

CREATE TABLE `textarea_style` (
  `css` varchar(255) NOT NULL default '',
  `value_regexp` text NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `css` (`css`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_tag`
-- -

CREATE TABLE `textarea_tag` (
  `tag` varchar(255) NOT NULL default '',
  `attribute` text NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_param_tag`
-- -

CREATE TABLE `textarea_param_tag` (
  `name` varchar(255) NOT NULL default '',
  `value_regexp` text NOT NULL,
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default ''
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `textarea_video_url`
-- -

CREATE TABLE `textarea_video_url` (
  `url` text NOT NULL,
  `action_name` varchar(255) NOT NULL default '',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default ''
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `uploads`
-- -

CREATE TABLE `uploads` (
  `upload_id` int(11) unsigned NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `module_id` int(11) NOT NULL default '0',
  `unique_id` varchar(40) NOT NULL default '',
  `file_name` text NOT NULL,
  `physical_file_name` text NOT NULL,
  `file_path` text NOT NULL,
  `action_name` varchar(255) NOT NULL default '',
  `file_size` bigint(20) NOT NULL default '0',
  `mimetype` varchar(255) NOT NULL default '',
  `extension` varchar(255) NOT NULL default '',
  `garbage_flag` tinyint(1) NOT NULL default '0',
  `sess_id` varchar(32) NOT NULL default '',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`upload_id`,`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `authorities`
-- -

CREATE TABLE `authorities` (
  `role_authority_id` int(11) unsigned NOT NULL default '0',
  `role_authority_name` varchar(64) NOT NULL default '',
  `system_flag` tinyint(1) unsigned NOT NULL default '0',
  `user_authority_id` tinyint(3) NOT NULL default '0',
  `hierarchy` int(11) NOT NULL default '0',
  `myroom_use_flag` tinyint(1) NOT NULL default '0',
  `public_createroom_flag` tinyint(1) unsigned NOT NULL default '0',
  `group_createroom_flag` tinyint(1) unsigned NOT NULL default '0',
  `private_createroom_flag` tinyint(1) unsigned NOT NULL default '0',
  `allow_htmltag_flag` tinyint(1) unsigned NOT NULL default '0',
  `allow_layout_flag` tinyint(1) unsigned NOT NULL default '0',
  `allow_attachment` tinyint(1) unsigned NOT NULL default '0',
  `allow_video` tinyint(1) unsigned NOT NULL default '0',
  `max_size` int(11) unsigned NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`role_authority_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `authorities_modules_link`
-- -

CREATE TABLE `authorities_modules_link` (
  `role_authority_id` int(11) unsigned NOT NULL default '0',
  `module_id` int(11) unsigned NOT NULL default '0',
  `authority_id` tinyint(3) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`role_authority_id`,`module_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `modules`
-- -

CREATE TABLE `modules` (
  `module_id` int(11) unsigned NOT NULL default '0',
  `version` varchar(32) NOT NULL default '',
  `display_sequence` int(11) unsigned NOT NULL default '0',
  `action_name` varchar(255) NOT NULL default '',
  `edit_action_name` varchar(255) NOT NULL default '',
  `edit_style_action_name` varchar(255) NOT NULL default '',
  `system_flag` tinyint(1) unsigned NOT NULL default '0',
  `disposition_flag` tinyint(1) unsigned NOT NULL default '1',
  `default_enable_flag` tinyint(1) NOT NULL default '1',
  `module_icon` varchar(255) default NULL,
  `theme_name` varchar(255) NOT NULL default '',
  `temp_name` varchar(255) NOT NULL default '',
  `min_width_size` int(11) NOT NULL default '0',
  `backup_action` varchar(255) NOT NULL default '',
  `restore_action` varchar(255) NOT NULL default '',
  `search_action` varchar(255) NOT NULL default '',
  `delete_action` varchar(255) NOT NULL default '',
  `block_add_action` varchar(255) NOT NULL default '',
  `block_delete_action` varchar(255) NOT NULL default '',
  `move_action` varchar(255) NOT NULL default '',
  `copy_action` varchar(255) NOT NULL default '',
  `shortcut_action` varchar(255) NOT NULL default '',
  `personalinf_action` varchar(255) NOT NULL default '',
  `whatnew_flag` tinyint(1) NOT NULL default '0',
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`module_id`),
  KEY `action_name` (`action_name`)
) ENGINE=MyISAM;

CREATE TABLE `modules_seq_id` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `encryption`
-- -

CREATE TABLE `encryption` (
  `public_key` text NOT NULL,
  `private_key` text NOT NULL,
  `key_length` int(11) NOT NULL default '0',
  `expiration_time` varchar(14) default NULL,
  `update_time` varchar(14) NOT NULL default '',
  `update_user` varchar(255) NOT NULL default ''
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `authorities_seq_id`
-- -

CREATE TABLE `authorities_seq_id` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `blocks_seq_id`
-- -

CREATE TABLE `blocks_seq_id` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `config_seq_id`
-- -

CREATE TABLE `config_seq_id` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;


-- -
-- テーブルの構造 `pages_seq_id`
-- -

CREATE TABLE `pages_seq_id` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `items_seq_id`
-- -

CREATE TABLE `items_seq_id` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `pages_meta_inf`
-- -

CREATE TABLE `pages_meta_inf` (
  `page_id`             int(11) UNSIGNED NOT NULL,
  `title`               varchar(255),
  `meta_keywords`       text,
  `meta_description`    text,
  `insert_time`         varchar(14) NOT NULL default '',
  `insert_site_id`      varchar(40) NOT NULL default '',
  `insert_user_id`      varchar(40) NOT NULL default '',
  `insert_user_name`    varchar(255) NOT NULL default '',
  `update_time`         varchar(14) NOT NULL default '',
  `update_site_id`      varchar(40) NOT NULL default '',
  `update_user_id`      varchar(40) NOT NULL default '',
  `update_user_name`    varchar(255) NOT NULL default '',
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `abbreviate_url`
-- -

CREATE TABLE `abbreviate_url` (
  `short_url` varchar(16) NOT NULL default '',
  `dir_name` varchar(32) NOT NULL default '',
  `module_id` int(11) NOT NULL default 0,
  `contents_id` int(11) NOT NULL default 0,
  `unique_id` int(11) NOT NULL default 0,
  `room_id` int(11) NOT NULL default 0,
  `insert_time` varchar(14) NOT NULL default '',
  `insert_site_id` varchar(40) NOT NULL default '',
  `insert_user_id` varchar(40) NOT NULL default '',
  `insert_user_name` varchar(255) NOT NULL default '',
  `update_time` varchar(14) NOT NULL default '',
  `update_site_id` varchar(40) NOT NULL default '',
  `update_user_id` varchar(40) NOT NULL default '',
  `update_user_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`short_url`),
  KEY `module_id` (`module_id`,`contents_id`,`unique_id`),
  KEY `dir_name` (`dir_name`,`unique_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM;

-- -
-- テーブルの構造 `config_language`
-- -
CREATE TABLE `config_language` (
  `conf_name` varchar(64) NOT NULL default '',
  `lang_dirname` varchar(64) NOT NULL default '',
  `conf_value` text NOT NULL,
  PRIMARY KEY (`conf_name`,`lang_dirname`)
) ENGINE=MyISAM;