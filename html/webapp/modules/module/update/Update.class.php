<?php
/**
 * モジュールアップデートクラス
 * 　　css_filesにカラム追加
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;
	var $configView = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."css_files");
		if(!isset($metaColumns["SYSTEM_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."css_files`
						ADD `system_flag` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `data` ,
						ADD `common_general_flag` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `system_flag` ,
						ADD `common_admin_flag` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `common_general_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}


		//
		// pagesにカラム追加
		//
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."pages");
		if(!isset($metaColumns["PERMALINK"])) {
			//set_time_limit(2400);
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD `permalink` TEXT NOT NULL AFTER `page_name` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD FULLTEXT (`permalink`);";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			//
			// permalinkセット
			//
			$pages = $this->db->execute("SELECT {users}.handle, {pages}.* " .
										" FROM {pages}" .
										" LEFT JOIN {users} ON {users}.user_id = {pages}.insert_user_id".
										" ORDER BY {pages}.thread_num,{pages}.display_sequence");
			if($pages === false) {
				return false;
			}
			if(is_array($pages)) {
				$set_permalink_arr = array();
				$set_parent_id_arr = array();
				foreach($pages as $page) {
					if(($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['thread_num'] == 1 && $page['display_sequence'] == 1) ||
						$page['display_sequence'] == 0) {
						// パブリックスペース OR トップページ　OR　グループスペース
						$set_parent_id_arr[$page['page_id']] = null;
						$set_permalink_arr[$page['page_id']] = "";
						continue;
					} else if($page['private_flag'] == _ON && $page['thread_num'] == 0) {
						// マイポータル、マイルーム直下
						// handle名セット
						if(isset($page['user_id'])) {
							$page['page_name'] = $page['handle'];
						} else {
							$page['page_name'] = $page['insert_user_name'];
						}
					}
					$permalink = "";
					$set_parent_id_arr[$page['page_id']] = $page['parent_id'];
					if(isset($set_permalink_arr[$page['parent_id']]) && $set_permalink_arr[$page['parent_id']] != "") {
						// 親のpermalinkあり
						$permalink = $set_permalink_arr[$page['parent_id']];
					} else {
						if($page['space_type'] == _SPACE_TYPE_PUBLIC && _PERMALINK_PUBLIC_PREFIX_NAME != '') {
							$permalink = _PERMALINK_PUBLIC_PREFIX_NAME;
						} else if($page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _ON &&
							$page['default_entry_flag'] == _ON && _PERMALINK_MYPORTAL_PREFIX_NAME != '') {
							$permalink = _PERMALINK_MYPORTAL_PREFIX_NAME;
						} else if($page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _ON &&
							$page['default_entry_flag'] == _OFF && _PERMALINK_PRIVATE_PREFIX_NAME != '') {
							$permalink = _PERMALINK_PRIVATE_PREFIX_NAME;
						} else if($page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _OFF &&
							_PERMALINK_GROUP_PREFIX_NAME != '') {
							$permalink = _PERMALINK_GROUP_PREFIX_NAME;
						}
					}

					$parent_permalink = $permalink;
					//if(substr($parent_permalink, -1, 1) == '/') {
					//	$parent_permalink = substr($parent_permalink, 0, strlen($parent_permalink) - 1);
					//}
					if(($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['thread_num'] == 0) ||
						($page['private_flag'] == _OFF && $page['space_type'] == _SPACE_TYPE_GROUP && $page['thread_num'] == 0)) {
						$replace_permalink = "";
					} else {
						$replace_permalink = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $page['page_name']);

						if($permalink == "") {
							$permalink .= $replace_permalink;
						} else {
							$permalink .= '/' . $replace_permalink;
						}
					}

					// 同じ階層に同じ名称の固定リンクがあればリネーム
					if($permalink !== "") {
						$parent_permalink = ($parent_permalink != "") ? $parent_permalink.'/' : $parent_permalink;
						$count = 1;
						if(preg_match(_PERMALINK_PROHIBITION_DIR_PATTERN, basename($permalink))) {
							$permalink = $parent_permalink. $replace_permalink ."-". $count;
							$count++;
						}

						while(1) {
							$same_pages = $this->db->selectExecute("pages", array("permalink"=> $permalink), null, 1);
							if(isset($same_pages[0])) {
								$permalink = $parent_permalink. $replace_permalink ."-". $count;
							} else {
								break;
							}
							$count++;
						}
					}

					$set_permalink_arr[$page['page_id']] = $permalink;

					if(isset($permalink)) {
						$params = array(
				    		"permalink" => $permalink
						);

						$where_params = array(
				    		"page_id" => $page['page_id']
						);
						$result = $this->db->updateExecute("pages", $params, $where_params, false);
						if($result === false) {
							return false;
						}
					}
				}
			}
			// pagesテーブルにカラムを追加するバージョンで同時にtextarea_styleのバグを修正
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `css` = 'border-right-color' WHERE css = 'boder-right-color' LIMIT 1");
		}

		//
		//言語切替
		//
		if(!isset($metaColumns["LANG_DIRNAME"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD `lang_dirname` VARCHAR( 64 ) NOT NULL DEFAULT '' AFTER `parameters` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			//
			//lang_dirname項目の更新
			//
			$config_lang =& $this->configView->getConfigByConfname(_SYS_CONF_MODID, 'language');
			$defualt_lang = empty($config_lang['conf_value'])?'japanese':$config_lang['conf_value'];
			$result = $this->db->execute("UPDATE {pages} SET {pages}.lang_dirname='".$defualt_lang.
										"' WHERE {pages}.space_type="._SPACE_TYPE_PUBLIC." AND {pages}.display_position="._DISPLAY_POSITION_CENTER." AND {pages}.thread_num!=0");
			if($result === false) return false;
		}

		// index pages
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."pages` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$insert_user_id_alter_table_flag = true;
		$thread_num_alter_table_flag = true;
		$root_id_alter_table_flag = true;
		$room_id_alter_table_flag = true;

		// INDEX 貼り直し
		$permalink_2_alter_table_flag = true;
		$parent_id_alter_table_flag = true;
		$space_type_alter_table_flag = true;
		$space_type_2_alter_table_flag = true;
		$root_id_2_alter_table_flag = true;

		foreach($results as $result) {
			if(isset($result['Column_name']) && $result['Column_name'] == "insert_user_id" &&
				$result['Key_name'] == "insert_user_id") {
				$insert_user_id_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "thread_num" &&
				$result['Key_name'] == "thread_num") {
				$thread_num_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "root_id" &&
				$result['Key_name'] == "root_id") {
				$root_id_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "room_id" &&
				$result['Key_name'] == "room_id") {
				$room_id_alter_table_flag = false;
			// INDEX 貼り直し
			} else if(isset($result['Column_name']) && $result['Column_name'] == "permalink" &&
				$result['Key_name'] == "permalink_2") {
				$permalink_2_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "parent_id" &&
				$result['Key_name'] == "parent_id") {
				$parent_id_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "space_type" &&
				$result['Key_name'] == "space_type") {
				$space_type_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "space_type" &&
				$result['Key_name'] == "space_type_2") {
				$space_type_2_alter_table_flag = false;
			} else if(isset($result['Column_name']) && $result['Column_name'] == "root_id" &&
				$result['Key_name'] == "root_id_2") {
				$root_id_2_alter_table_flag = false;
			}
		}
		if(!$insert_user_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						DROP INDEX `insert_user_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if(!$thread_num_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						DROP INDEX `thread_num` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if(!$root_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						DROP INDEX `root_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($room_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD INDEX ( `room_id` , `lang_dirname` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		// INDEX 貼り直し
		if($permalink_2_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						DROP INDEX `permalink` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			//varcharへ
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						 CHANGE `permalink` `permalink` VARCHAR( 255 ) NOT NULL ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD INDEX `permalink_2` ( `permalink` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($parent_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD INDEX ( `parent_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($space_type_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD INDEX ( `space_type` , `private_flag` , `insert_user_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($space_type_2_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD INDEX `space_type_2` ( `space_type` , `private_flag` , `thread_num` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($root_id_2_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages`
						ADD INDEX `root_id_2` ( `root_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// add index pages_users_link
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."pages_users_link` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$user_id_alter_table_flag = true;
		foreach($results as $result) {
			if(isset($result['Column_name']) && $result['Column_name'] == "user_id" &&
				$result['Key_name'] == "user_id") {
				$user_id_alter_table_flag = false;
			}
		}
		if($user_id_alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."pages_users_link`
						ADD INDEX ( `user_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// index action_name
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."modules` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_flag = true;
		foreach($results as $result) {
			if(isset($result['Column_name']) && $result['Column_name'] == "action_name" &&
				$result['Key_name'] == "action_name") {
				$alter_table_flag = false;
			}
		}
		if($alter_table_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."modules`
						ADD INDEX ( `action_name` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$max_id = $this->db->maxExecute("modules_seq_id", "id");
		if($max_id !== false && $max_id > 0) {
			$where_params = array("id !=".intval($max_id) => null);
			$this->db->deleteExecute("modules_seq_id", $where_params);
		}
		//
		// Group Left Column:pagesテーブル:page_id=10のdefault_entry_flag=2の値をdefault_entry_flag=1に更新
		// configテーブル:column_space_type_useが1の場合、影響あり。
		// （一般がグループルームへ遷移するとエラーとなった）
		//
		$pages = $this->db->selectExecute("pages", array("page_id"=> 10,"default_entry_flag" => 2));
		if($pages !== false && count($pages) > 0) {
			$this->db->updateExecute("pages", array("default_entry_flag" => _ON), array("page_id" => 10));
		}
		//
		// page_id=12のDefault Private RoomをDefault Roomに変更し、space_type=0,private_flag=0,action_name=''に変更
		//
		$pages = $this->db->selectExecute("pages", array("page_id"=> 12, 'private_flag'=> _ON));
		if($pages !== false && count($pages) > 0) {
			$this->db->updateExecute("pages", array("page_name" => 'Default Room', 'private_flag'=> _OFF, 'action_name'=> '', 'space_type'=> 0), array("page_id" => 12));
		}
		//
		// page_id=2のグループスペース直下のノードのdefault_entry_flagをONに変更
		//
		$pages = $this->db->selectExecute("pages", array("page_id"=> 2, 'default_entry_flag'=> _OFF));
		if($pages !== false && count($pages) > 0) {
			$this->db->updateExecute("pages", array("default_entry_flag" => _ON), array("page_id" => 2));
		}
		//
		// private_spaceのinsert_user_idを自分自身に更新
		//
		$private_pages = $this->db->execute("SELECT {pages}.*, {pages_users_link}.user_id,{users}.handle " .
									" FROM {pages},{pages_users_link} " .
									" LEFT JOIN {users} ON {users}.user_id = {pages_users_link}.user_id ".
									" WHERE {pages}.default_entry_flag = "._OFF." AND {pages}.private_flag = "._ON.
									" AND {pages}.thread_num = 0 AND {pages}.room_id = {pages_users_link}.room_id AND ({pages}.insert_user_id != {pages_users_link}.user_id OR {pages}.insert_user_name != {users}.handle) ");
		if ($private_pages !== false && isset($private_pages[0])) {
			foreach($private_pages as $private_page) {
				if(!isset($private_page['handle'])) {
					$private_page['handle'] = "";
				}
				$where_params = array(
									"(page_id=".$private_page['page_id']." OR root_id=".$private_page['page_id'].")" => null
								);

				$this->db->updateExecute("pages", array("insert_user_id" => $private_page['user_id'], "insert_user_name" => $private_page['handle']), $where_params);
			}
		}

		//
		// pages_meta_inf追加
		//
		$adodb = $this->db->getAdoDbObject();
		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."pages_meta_inf", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."pages_meta_inf` (" .
					"`page_id`             int(11) UNSIGNED NOT NULL,".
					"`title`               varchar(255),".
					"`meta_keywords`       text,".
					"`meta_description`    text,".
					"`insert_time`         varchar(14) NOT NULL default '',".
					"`insert_site_id`      varchar(40) NOT NULL default '',".
					"`insert_user_id`      varchar(40) NOT NULL default '',".
					"`insert_user_name`    varchar(255) NOT NULL default '',".
					"`update_time`         varchar(14) NOT NULL default '',".
					"`update_site_id`      varchar(40) NOT NULL default '',".
					"`update_user_id`      varchar(40) NOT NULL default '',".
					"`update_user_name`    varchar(255) NOT NULL default '',".
					" PRIMARY KEY (`page_id`)" .
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if (!in_array($this->db->getPrefix()."textarea_param_tag", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."textarea_param_tag` (" .
					"`name` varchar(255) NOT NULL default '',".
					"`value_regexp` text NOT NULL,".
					"`insert_time`         varchar(14) NOT NULL default '',".
					"`insert_site_id`      varchar(40) NOT NULL default '',".
					"`insert_user_id`      varchar(40) NOT NULL default '',".
					"`insert_user_name`    varchar(255) NOT NULL default '',".
					"`update_time`         varchar(14) NOT NULL default '',".
					"`update_site_id`      varchar(40) NOT NULL default '',".
					"`update_user_id`      varchar(40) NOT NULL default '',".
					"`update_user_name`    varchar(255) NOT NULL default ''".
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_param_tag` (`name`, `value_regexp`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('play', '^(true|false)$', '', '', '0', '', '', '', '0', ''),
					('loop', '^(true|false)$', '', '', '0', '', '', '', '0', ''),
					('quality', '^(high|low|medium|best|autolow|autohigh)$', '', '', '0', '', '', '', '0', ''),
					('movie', '', '', '', '0', '', '', '', '0', ''),
					('bgcolor', '', '', '', '0', '', '', '', '0', ''),
					('scale', '^(showall|noborder|exactfit|noscale)$', '', '', '0', '', '', '', '0', ''),
					('salign', '', '', '', '0', '', '', '', '0', ''),
					('menu', '^(true|false)$', '', '', '0', '', '', '', '0', ''),
					('wmode', '^(window|opaque|transparent)$', '', '', '0', '', '', '', '0', '');";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('object', 'archive,border,classid,code,codebase,codetype,data,declare,name,standby,tabindex,type,usemap,align,width,height,hspace,vspace', '', '', '0', '', '', '', '0', ''),
					('embed', 'src,height,width,hspace,vspace,units,border,frameborder,play,loop,quality,pluginspage,type,allowscriptaccess,allowfullscreen,flashvars', '', '', '0', '', '', '', '0', ''),
					('noembed', '', '', '', '0', '', '', '', '0', ''),
					('param', 'name,value', '', '', '0', '', '', '', '0', '');";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		if (!in_array($this->db->getPrefix()."textarea_video_url", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."textarea_video_url` (" .
					"`url` text NOT NULL,".
					"`action_name` varchar(255) NOT NULL default '',".
					"`insert_time`         varchar(14) NOT NULL default '',".
					"`insert_site_id`      varchar(40) NOT NULL default '',".
					"`insert_user_id`      varchar(40) NOT NULL default '',".
					"`insert_user_name`    varchar(255) NOT NULL default '',".
					"`update_time`         varchar(14) NOT NULL default '',".
					"`update_site_id`      varchar(40) NOT NULL default '',".
					"`update_user_id`      varchar(40) NOT NULL default '',".
					"`update_user_name`    varchar(255) NOT NULL default ''".
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_video_url` (`url`, `action_name`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('http://www.youtube.com/', '', '', '', '0', '', '', '', '0', ''),
					('', 'multimedia_view_main_play', '', '', '0', '', '', '', '0', '');";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// WYSIWYGのparamタグのname追加
		$textarea_param_tag = $this->db->selectExecute("textarea_param_tag", array("name"=> 'allowFullScreen'));
		if($textarea_param_tag !== false && count($textarea_param_tag) == 0) {
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_param_tag` (`name`, `value_regexp`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES ('allowFullScreen', '^(true|false)$', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_param_tag` (`name`, `value_regexp`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES ('allowscriptaccess', '^(always|sameDomain|never)$', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// WYSIWYGの許可するVideoURLに「http://www.youtube-nocookie.com/」を追加
		$textarea_video_url = $this->db->selectExecute("textarea_video_url", array("url"=> 'http://www.youtube-nocookie.com/'));
		if($textarea_video_url !== false && count($textarea_video_url) == 0) {
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_video_url` (`url`, `action_name`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('http://www.youtube-nocookie.com/', '', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// WYSIWYGの許可するVideoURLに「http://www.youtube-nocookie.com/」を追加
		$textarea_video_url = $this->db->selectExecute("textarea_video_url", array("url"=> 'http://www.youtube-nocookie.com/'));
		if($textarea_video_url !== false && count($textarea_video_url) == 0) {
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_video_url` (`url`, `action_name`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('http://www.youtube-nocookie.com/', '', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// WYSIWYGの許可するタグに「em」,「i」,「strike」,「s」を追加
		$textarea_tag = $this->db->selectExecute("textarea_tag", array("tag"=> 'em'));
		if($textarea_tag !== false && count($textarea_tag) == 0) {
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('em', '', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('i', '', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('strike', '', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('s', '', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
		}

		// WYSIWYGの許可するタグに「iframe」を追加
		$textarea_tag = $this->db->selectExecute("textarea_tag", array("tag"=> 'iframe'));
		if($textarea_tag !== false && count($textarea_tag) == 0) {
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('iframe', 'src,height,width,hspace,vspace,marginheight,marginwidth,allowtransparency,frameborder,border,bordercolor,allowfullscreen', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);
		}

		// WYSIWYGの許可するタグに「colgroup」,「col」を追加
		// styleの指定をpt等を許すように修正
	$textarea_tag = $this->db->selectExecute("textarea_tag", array("tag"=> 'colgroup'));
		if($textarea_tag !== false && count($textarea_tag) == 0) {
			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('colgroup', 'span', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);

			$sql = "INSERT INTO `".$this->db->getPrefix()."textarea_tag` (`tag`, `attribute`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES
					('col', 'span', '', '', '0', '', '', '', '0', '')";
			$result = $this->db->execute($sql);

			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '(^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$|^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$|^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$|^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$)' WHERE css = 'margin' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '(^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$|^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$|^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$|^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto) +(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$)' WHERE css = 'padding' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'margin-left' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'margin-right' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'margin-top' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'margin-bottom' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'padding-left' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'padding-right' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'padding-top' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto)$' WHERE css = 'padding-bottom' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '(^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$|^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex) +\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$|^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex) +\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex) +\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$|^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex) +\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex) +\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex) +\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$)' WHERE css = 'border-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$' WHERE css = 'border-left-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$' WHERE css = 'border-right-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$' WHERE css = 'border-top-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)$' WHERE css = 'border-bottom-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto|inherit)$' WHERE css = 'left' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto|inherit)$' WHERE css = 'right' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto|inherit)$' WHERE css = 'top' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|auto|inherit)$' WHERE css = 'bottom' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^((\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex))|auto|none|inherit)$' WHERE css = 'width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^((\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex))|auto|none|inherit)$' WHERE css = 'height' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^((\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex))|auto|none|inherit)$' WHERE css = 'min-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^((\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex))|auto|none|inherit)$' WHERE css = 'min-height' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^((\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex))|auto|none|inherit)$' WHERE css = 'max-width' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^((\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex))|auto|none|inherit)$' WHERE css = 'max-height' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^([+-]?(\\\d+(\\\.\\\d+)? *(px|em|%|pt|ex)|baseline|top|middle|bottom|text-top|text-bottom|super|sub|inherit)$' WHERE css = 'vertical-align' LIMIT 1");
			$this->db->execute("UPDATE `".$this->db->getPrefix()."textarea_style` SET `value_regexp` = '^(none|hidden|solid|double|groove|ridge|inset|outset|dashed|dotted) *(none|hidden|solid|double|groove|ridge|inset|outset|dashed|dotted)* *(none|hidden|solid|double|groove|ridge|inset|outset|dashed|dotted)* *(none|hidden|solid|double|groove|ridge|inset|outset|dashed|dotted)*$' WHERE css = 'border-style' LIMIT 1");
		}

		//
		// URL短縮形追加
		//
		$adodb = $this->db->getAdoDbObject();
		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."abbreviate_url", $metaTables)) {
    		$container =& DIContainerFactory::getContainer();
			$abbreviateurlView =& $container->getComponent("abbreviateurlView");
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");

			// URL短縮形テーブル
			$sql = "CREATE TABLE `".$this->db->getPrefix()."abbreviate_url` (" .
					"`short_url`           varchar(16) NOT NULL default '',".
					"`dir_name`            varchar(32) NOT NULL default '',".
					"`module_id`           int(11) NOT NULL default 0,".
					"`contents_id`         int(11) NOT NULL default 0,".
					"`unique_id`           int(11) NOT NULL default 0,".
					"`room_id`             int(11) NOT NULL default 0,".
					"`insert_time`         varchar(14) NOT NULL default '',".
					"`insert_site_id`      varchar(40) NOT NULL default '',".
					"`insert_user_id`      varchar(40) NOT NULL default '',".
					"`insert_user_name`    varchar(255) NOT NULL default '',".
					"`update_time`         varchar(14) NOT NULL default '',".
					"`update_site_id`      varchar(40) NOT NULL default '',".
					"`update_user_id`      varchar(40) NOT NULL default '',".
					"`update_user_name`    varchar(255) NOT NULL default '',".
					" PRIMARY KEY (`short_url`)," .
					" KEY `module_id` (`module_id`,`contents_id`,`unique_id`),".
					" KEY `dir_name` (`dir_name`,`unique_id`),".
					" KEY `room_id` (`room_id`)".
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}

			//掲示板のURL短縮
			$params = array();
			$sql = "SELECT bbs_id, post_id, room_id FROM {bbs_post}";
			$posts = $this->db->execute($sql);
			if ($posts === false) {
				return $posts;
			}
			foreach ($posts as $post) {
				$result = $abbreviateurlAction->setAbbreviateUrl($post['bbs_id'], $post['post_id'], "bbs", null, $post['room_id']);
				if ($result === false) {
					return false;
				}
			}
			//日誌のURL短縮
			$sql = "SELECT journal_id, post_id, room_id FROM {journal_post}";
			$posts = $this->db->execute($sql);
			if ($posts === false) {
				return $posts;
			}
			foreach ($posts as $post) {
				$result = $abbreviateurlAction->setAbbreviateUrl($post['journal_id'], $post['post_id'], "journal", null, $post['room_id']);
				if ($result === false) {
					return false;
				}
//				$result = $abbreviateurlAction->setAbbreviateUrl($post['journal_id'], $post['post_id'], "journal_trackback", null, $post['room_id']);
//				if ($result === false) {
//					return false;
//				}
			}
			//汎用DBのURL短縮
			$params = array();
			$sql = "SELECT multidatabase_id, content_id, room_id FROM {multidatabase_content}";
			$posts = $this->db->execute($sql);
			if ($posts === false) {
				return $posts;
			}
			foreach ($posts as $post) {
				$result = $abbreviateurlAction->setAbbreviateUrl($post['multidatabase_id'], $post['content_id'], "multidatabase", null, $post['room_id']);
				if ($result === false) {
					return false;
				}
			}
		}

		// smarty_cacheにssl_flagを追加
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."smarty_cache");
		if(!isset($metaColumns["_SSL_FLAG"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."smarty_cache`
						ADD `_ssl_flag` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `_mobile_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		$metaColumns = $adodb->MetaColumns($this->db->getPrefix().'language');
		if(!isset($metaColumns['LANGUAGE'])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."language`
						ADD `language` VARCHAR( 8 ) NOT NULL DEFAULT '' AFTER `lang_dirname` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			$result = $this->db->updateExecute("language", array('language' => 'ja'), array('lang_dirname' => 'japanese'), false);
			if($result === false) {
				return false;
			}
			$result = $this->db->updateExecute("language", array('language' => 'en'), array('lang_dirname' => 'english'), false);
			if($result === false) {
				return false;
			}
			$result = $this->db->updateExecute("language", array('language' => 'zh'), array('lang_dirname' => 'chinese'), false);
			if($result === false) {
				return false;
			}
		}

		// blocksにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."blocks` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_module_id_flag = true;
		$alter_table_root_id_flag = true;
		$alter_table_parent_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "module_id") {
				$alter_table_module_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "root_id") {
				$alter_table_root_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "parent_id") {
				$alter_table_parent_id_flag = false;
			}
		}
		if($alter_table_module_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."blocks` ADD INDEX ( `module_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_root_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."blocks` ADD INDEX ( `root_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_parent_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."blocks` ADD INDEX ( `parent_id` ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// smarty_cacheにlang_dirnameを追加
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."smarty_cache");
		if(!isset($metaColumns["LANG_DIRNAME"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."smarty_cache`
						ADD `lang_dirname` varchar(64) NOT NULL DEFAULT '' AFTER `_ssl_flag` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// abbreviate_urlにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."abbreviate_url` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."abbreviate_url` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// javascript_filesにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."javascript_files` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_update_time = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "update_time") {
				$alter_table_update_time = false;
			}
		}
		if($alter_table_update_time) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."javascript_files` ADD INDEX ( `update_time`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// css_filesにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."css_files` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_update_time = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "update_time") {
				$alter_table_update_time = false;
			}
		}
		if($alter_table_update_time) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."css_files` ADD INDEX ( `update_time`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// smarty_cacheにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."smarty_cache` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_action_name = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "action_name2") {
				$alter_table_action_name = false;
			}
		}
		if($alter_table_action_name) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."smarty_cache` DROP INDEX `action_name` ," .
					" ADD INDEX `action_name2` ( `block_id` , `_auth_id` , `action_name` ) ";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}

		// カラムの型変換処理
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."smarty_cache");
		if (is_object($metaColumns["CACHE_CONTENT"]) && $metaColumns["CACHE_CONTENT"]->type != "mediumtext") {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."smarty_cache` " .
					"MODIFY `cache_content` mediumtext NOT NULL;";
			$results = $this->db->execute($sql);
			if($results === false) return false;
		}

		return true;
	}
}
?>
