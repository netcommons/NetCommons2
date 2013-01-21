<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員登録(会員編集)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Regist extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;
	var $sending_email = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $usersAction = null;
	var $usersView = null;
	var $uploadsAction = null;
	var $timezoneMain = null;
	var $pagesView = null;
	var $db = null;
	var $pagesAction = null;
	var $configView = null;
	var $monthlynumberAction = null;
	var $request = null;
	var $authoritiesView = null;
	var $blocksAction = null;

	// 値をセットするため
	var $mail_add_user = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$edit_flag = _ON;
		if($this->user_id == null || $this->user_id == "0") {
			$this->user_id = "0";
			$edit_flag = _OFF;
		}
		$sess_user_id = $this->user_id;
    	$where_params = array(
    						"user_authority_id" => _AUTH_ADMIN,		// 管理者固定
    						"type != 'label'" => null,
    						"type != 'system'" => null
    					);
		$items =& $this->usersView->getItems($where_params, null, null, null, array($this, "_getItemsFetchcallback"));
		if($items === false) return 'error';

		//
		// セッションデータを用いて登録
		//
		if(!$edit_flag) {
			$time = timezone_date();
			// 新規作成
			$user = array(
				"activate_key" => "",
				"password_regist_time" => $time,
				"last_login_time" => "",
				"previous_login_time" => ""
			);
		} else {
			// 編集　会員データ取得
			$users =& $this->db->selectExecute("users", array("user_id" => $this->user_id));
			if($users === false) return 'error';
			if(!isset($users[0])) {
				// エラー
				$this->db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "users"));
				return 'error';
			}
			$user =& $users[0];
    		$where_params = array("user_id" => $user['user_id']);
    		unset($user['user_id']);
    		unset($user['system_flag']);
    		unset($user['activate_key']);
    		unset($user['password_regist_time']);
    		unset($user['last_login_time']);
    		unset($user['previous_login_time']);
		}
		// ----------------------------------------------------------------------
		// --- 基本項目(usersテーブル)                                        ---
		// ----------------------------------------------------------------------

		$base_items =& $this->session->getParameter(array("user", "regist", $sess_user_id));
		$base_items_public =& $this->session->getParameter(array("user", "regist_public", $sess_user_id));
		$base_items_reception =& $this->session->getParameter(array("user", "regist_reception", $sess_user_id));
		$regist_pages_users_link =& $this->session->getParameter(array("user", "regist_confirm", $sess_user_id));
		if(!isset($base_items) || $base_items == null) {
			//セッションデータなし
			return 'error';
		}
		$db_users_items_link =& $this->usersView->getUserItemLinkById($this->user_id);
		if($db_users_items_link === false) return 'error';

		$handle = "";
		$login_id = "";
		$password = "";

		$users_items_link = array();
		$users_items_link_flag_arr = array();
		foreach($items as $item_id => $item) {
			$users_items_link[$item_id] = array(
				"user_id" => $this->user_id,
				"item_id" => $item_id,
				"public_flag" => _ON,
				"email_reception_flag" => _OFF
			);

			if(isset($base_items[$item_id])) {
				$base_item = $base_items[$item_id];
			} else {
				$base_item = "";
			}

			if( $item['tag_name'] != "" && $item['is_users_tbl_fld'] == true ) {
				$tag_name = $item['tag_name'];
				// userデータ
				switch ($item['tag_name']) {
					case "login_id":
						$login_id = $base_item;
						break;
					case "handle":
						$handle = $base_item;
						break;
					case "password":
						$password = $base_item;
						break;
					case "role_authority_name":
						$set_role_authority_id = $base_item;
						$tag_name = "role_authority_id";
						break;
					case "active_flag_lang":
						$tag_name = "active_flag";
						break;
					case "lang_dirname_lang":
						$tag_name = "lang_dirname";
						if($base_item != "" && $this->user_id == $this->session->getParameter("_user_id")) {
							$this->session->setParameter("_lang", $base_item);
						}
						break;
					case "timezone_offset_lang":
						$tag_name = "timezone_offset";
						if(defined($base_item)) {
							$base_item = $this->timezoneMain->getFloatTimeZone(constant($base_item));
							if($this->session->getParameter("_user_id") == $this->user_id) {
								$this->session->setParameter("_timezone_offset",$base_item);
							}
						}
						break;
				}
				$users_items_link_flag_arr[$item_id] = false;
				if($tag_name == "password" && $edit_flag == _ON && $base_item == "") {
					// パスワードは変更しない
					continue;
				}
				if($item['tag_name'] == "password") $base_item = md5($base_item);
				$user[$tag_name] = $base_item;
			} else {
				// users_items_linkデータ
				if($item['type'] == "radio" || $item['type'] == "checkbox" ||
					$item['type'] == "select") {
					if(is_array($base_item)) {
						$users_items_link[$item_id]['content'] = implode("|", $base_item) . "|";
					} else if($base_item == "") {
						$users_items_link[$item_id]['content'] = "";
					} else {
						$users_items_link[$item_id]['content'] = $base_item . "|";
					}
				} else {
					$users_items_link[$item_id]['content'] = $base_item;
				}
				$users_items_link_flag_arr[$item_id] = true;
			}
			if($item['allow_public_flag'] ==_ON) {
				if(!isset($base_items_public[$item_id])) {
					$users_items_link[$item_id]['public_flag'] = _OFF;
				}
			}
			//if($item['allow_public_flag'] ==_ON && isset($base_items_public[$item_id]) &&
			//	$base_items_public[$item_id] == _ON) {
			//	$users_items_link[$item_id]['public_flag'] = _ON;
			//}
			if($item['allow_email_reception_flag'] ==_ON && isset($base_items_reception[$item_id]) &&
				 $base_items_reception[$item_id] == _ON) {
				$users_items_link[$item_id]['email_reception_flag'] = _ON;
			}
		}

		if(!$edit_flag) {
			//
			// 新規作成
			// Insert
			//
			$this->user_id = $this->usersAction->insUser($user);
			if($this->user_id === false) return 'error';
		} else {
			$old_user = $this->usersView->getUserById($this->user_id);
			if($old_user === false || !isset($old_user['user_id'])) return 'error';
			if($old_user['role_authority_id'] != $user['role_authority_id']) {
				//
				// プライベートスペースが使用可能かどうかが変更されている場合、
				// それに応じてpagesテーブルのdisplay_flagも変更する(_ON or _PAGES_DISPLAY_FLAG_DISABLED)
				//
				$old_authoritiy =& $this->authoritiesView->getAuthorityById(intval($old_user['role_authority_id']));
				if ($old_authoritiy === false) return 'error';

				$authoritiy =& $this->authoritiesView->getAuthorityById(intval($user['role_authority_id']));
				if ($authoritiy === false) return 'error';

				if($old_authoritiy['myroom_use_flag'] != $authoritiy['myroom_use_flag']) {
					$private_where_params = array(
						"{pages}.insert_user_id" => $this->user_id,
						"{pages}.space_type" => _SPACE_TYPE_GROUP,
						"{pages}.private_flag" => _ON,
						"{pages}.default_entry_flag" => _OFF
					);
					if($authoritiy['myroom_use_flag'] == _ON) {
						$set_params = array(
							"display_flag" => _ON
						);
					} else {
						// プライベートスペース使用不可
						$set_params = array(
							"display_flag" => _PAGES_DISPLAY_FLAG_DISABLED
						);
					}
					$result = $this->pagesAction->updPage($set_params, $private_where_params);
					if($result === false) return 'error';
				}
			}
			//
			// Update
			//
			$result = $this->usersAction->updUsers($user, $where_params);
			if($result === false) return 'error';
		}

		// ----------------------------------------------------------------------
		// --- 詳細項目(users_items_linkデータ登録)                           ---
		// ----------------------------------------------------------------------
    	foreach($users_items_link as $item_id => $users_item_link) {
    		//$users_item_link[]
    		// users_items_linkが変更ないか、users_items_linkがなく初期値であれば
    		if($users_items_link_flag_arr[$item_id] == true) {
				if(isset($db_users_items_link[$item_id]) &&
					($db_users_items_link[$item_id]['content'] === $users_items_link[$item_id]['content'] &&
					   $db_users_items_link[$item_id]['public_flag'] == $users_items_link[$item_id]['public_flag'] &&
					   $db_users_items_link[$item_id]['email_reception_flag'] == $users_items_link[$item_id]['email_reception_flag']
					)) {
						//すべて等しい

				} else if($users_items_link[$item_id]['content'] == '' &&
					   $users_items_link[$item_id]['public_flag'] == _OFF &&
					   $users_items_link[$item_id]['email_reception_flag'] == _OFF
					) {
						//初期値のまま
						if(isset($db_users_items_link[$item_id])) {
							// 削除
			    			$result = $this->usersAction->delUsersItemsLinkById($item_id, $this->user_id);
			    			if($result === false) return 'error';
						}
				} else {
					// 更新 or 新規追加
					if($users_item_link['user_id'] == 0) {
	    				$users_item_link['user_id'] = $this->user_id;
	    			}
	    			$content = $users_item_link['content'];
	    			if(!isset($db_users_items_link[$item_id])) {
	    				// 新規追加
	    				$result = $this->usersAction->insUserItemLink($users_item_link);

	    			} else {
	    				$where_params = array(
							"user_id" => $users_item_link['user_id'],
							"item_id" => $users_item_link['item_id']
						);
			    		unset($users_item_link['user_id']);
			    		unset($users_item_link['item_id']);
	    				// 更新
	    				$result = $this->usersAction->updUsersItemsLink($users_item_link, $where_params);
	    			}
	    			if($result === false) return 'error';

	    			if($items[$item_id]['type'] == "file") {
		    			//アバターの画像のガーベージフラグOFF,unique_id セット
						$upload_path = $content;
						$pathList = explode("&", $upload_path);
						if(isset($pathList[1])) {
							$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
							$upload_params = array(
								"garbage_flag" => _OFF,
								"unique_id" => $this->user_id
							);
							$upload_where_params = array(
								"upload_id" => $upload_id,
								"garbage_flag" => _ON
							);
							$upload_result = $this->uploadsAction->updUploads($upload_params, $upload_where_params);
							if ($upload_result === false) return 'error';
						}
	    			}
				}
			}
    	}
    	// ----------------------------------------------------------------------
		// ---参加ルーム(pages_users_link登録)                          　　  ---
		// ----------------------------------------------------------------------
		//デフォルトで参加するルームを検索し、$regist_pages_users_linkにないものは不参加として登録
		//権限がconfigテーブルのdefault_entry_authと同じものは、$regist_pages_users_linkから削除

		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return 'error';


		$default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
    	$default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];
    	//$default_entry_role_auth_private = $config['default_entry_role_auth_private']['conf_value'];

		$where_params = array(
			"page_id = room_id" => null
		);

		$pages =& $this->db->selectExecute("pages", $where_params);
		if ($pages === false) return 'error';

		foreach($pages as $page) {
			if(isset($regist_pages_users_link[$page['page_id']])) {
				if($page['default_entry_flag'] == _ON && (($page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _OFF &&
					intval($regist_pages_users_link[$page['page_id']][0]) == $default_entry_role_auth_group &&
					intval($regist_pages_users_link[$page['page_id']][1]) == _OFF) ||
					($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['private_flag'] == _OFF && intval($regist_pages_users_link[$page['page_id']][0]) == $default_entry_role_auth_public &&
					intval($regist_pages_users_link[$page['page_id']][1]) == _OFF))) {
					// デフォルトで参加するルームのデフォルト値なので、値を削除する
					$where_params = array(
		    			"room_id" => $page['page_id'],
		    			"user_id" => $this->user_id
		    		);
					$result = $this->pagesAction->delPageUsersLink($where_params);
					if ($result === false) return 'error';
					unset($regist_pages_users_link[$page['page_id']]);
				}
			} else if ($page['private_flag'] == _OFF && ($page['space_type'] != _SPACE_TYPE_GROUP && $page['thread_num'] != 0)) {
				// 不参加
				$regist_pages_users_link[$page['page_id']][0] = _AUTH_OTHER;
				$regist_pages_users_link[$page['page_id']][1] = _OFF;
			} else if($page['default_entry_flag'] == _ON && ($page['space_type'] == _SPACE_TYPE_GROUP && $page['private_flag'] == _OFF && $page['thread_num'] != 0)) {
				// グループルーム-不参加
				$regist_pages_users_link[$page['page_id']][0] = _AUTH_OTHER;
				$regist_pages_users_link[$page['page_id']][1] = _OFF;
			}
		}

    	if($edit_flag) {
    		// 編集
    		if($regist_pages_users_link !== null) {
    			$where_params = array(
	    			"user_id" => $this->user_id
	    		);

    			//$pages_users_link =& $this->db->selectExecute("pages_users_link", $where_params, null, null, null, array($this, "_showpages_fetchcallback"));
    			$sql_where = $this->db->getWhereSQL($params, $where_params, false);
				$tableName = "pages_users_link";
		        $sql = "SELECT {".$tableName."}.*,{pages}.private_flag " .
						" FROM {".$tableName."}, {pages}";
		        $sql .= " WHERE {".$tableName."}.room_id = {pages}.page_id ".$sql_where;
		        $pages_users_link =& $this->db->execute($sql, $params, null, null, true, array($this, "_showpages_fetchcallback"));

    			if ($pages_users_link === false) return 'error';
    			if(count($pages_users_link) > 0) {
    				foreach($pages_users_link as $page_user_link) {
	    				if(isset($regist_pages_users_link[$page_user_link['room_id']])) {
	    					// 更新
	    					$params = array(
				    			"role_authority_id" => intval($regist_pages_users_link[$page_user_link['room_id']][0]),
				    			"createroom_flag" => intval($regist_pages_users_link[$page_user_link['room_id']][1])
				    		);
				    		$where_params = array(
				    			"room_id" => $page_user_link['room_id'],
				    			"user_id" => $this->user_id
				    		);
				    		if($params['role_authority_id'] != $page_user_link['role_authority_id'] ||
				    			$params['createroom_flag'] != $page_user_link['createroom_flag']) {
				    			// 変更があれば更新
		    					$result = $this->pagesAction->updPageUsersLink($params, $where_params);
		    					if ($result === false) return 'error';
				    		}
	    					unset($regist_pages_users_link[$page_user_link['room_id']]);
	    				} else if($page_user_link['private_flag'] != _ON) {
	    					// プライベートスペースのルームではなければ
	    					// 削除
	    					$where_params = array(
				    			"room_id" => $page_user_link['room_id'],
				    			"user_id" => $this->user_id
				    		);
	    					$result = $this->pagesAction->delPageUsersLink($where_params);
	    					if ($result === false) return 'error';
	    				}
	    			}
    			}
    		}

    	}

    	// 新規 OR 追加
		if($regist_pages_users_link != null) {
			foreach($regist_pages_users_link as $page_id => $regist_page) {
				$page_id = intval($page_id);
				$role_authority_id = intval($regist_page[0]);
				$createroom_flag = intval($regist_page[1]);

				//
				// ページユーザリンクテーブル追加
				//
				$params = array(
	    			"room_id" => $page_id,
	    			"user_id" => $this->user_id,
	    			"role_authority_id" => $role_authority_id,
	    			"createroom_flag" => $createroom_flag
	    		);
				$result = $this->pagesAction->insPageUsersLink($params);
		    	if ($result === false) return 'error';
			}
		}

    	// ----------------------------------------------------------------------
		// --- プライベートスペース作成                                       ---
		// ----------------------------------------------------------------------
		$permalink_handle = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $handle);
		$permalink_handle = $this->pagesAction->getRenamePermaLink($permalink_handle);
    	if(!$edit_flag) {
	    	//権限テーブルのmyroom_use_flagにかかわらずプライベートスペース作成

			// ----------------------------------------------------------------------
			// --- ページテーブル追加        		                              ---
			// ----------------------------------------------------------------------
			$private_where_params = array(
										"space_type" => _SPACE_TYPE_GROUP,
										"thread_num" => 0,
										"private_flag" => _ON,
										"display_sequence!=0" => null,
										"default_entry_flag" => _OFF
									);
			$buf_page_private =& $this->pagesView->getPages($private_where_params, null, 1);
			if ($buf_page_private === false) return 'error';
			if(!isset($buf_page_private[0])) {
				// エラー
				$this->db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "pages"));
				return 'error';
			}
			$display_sequence = $buf_page_private[0]['display_sequence'];

			// プライベートスペース名称取得
			if(!isset($handle) || $handle == "") $handle = _PRIVATE_SPACE_NAME;

			$private_space_name = str_replace("{X-HANDLE}", $handle, $config['add_private_space_name']['conf_value']);
			//if($config['open_private_space']['conf_value'] == _ON) {
			//	// プライベートスペースが公開していれば、default_entry_flagをonにする
			//	$default_entry_flag = _ON;
    		//} else {
    			$default_entry_flag = _OFF;
			//}
			//$config_private_space_name = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "add_private_space_name");
			//if($config_private_space_name === false) return 'error';
			//$private_space_name = str_replace("{X-HANDLE}", $handle, $config_private_space_name['conf_value']);
			$set_display_flag = _ON;
			if(isset($set_role_authority_id)) {
				$set_authoritiy =& $this->authoritiesView->getAuthorityById(intval($set_role_authority_id));
				if ($set_authoritiy === false) return 'error';
				if(isset($set_authoritiy['myroom_use_flag']) && $set_authoritiy['myroom_use_flag'] == _ON) {
					$set_display_flag = _ON;
				} else {
					$set_display_flag = _PAGES_DISPLAY_FLAG_DISABLED;
				}
			}
			if(_PERMALINK_PRIVATE_PREFIX_NAME != '') {
	    		$permalink = _PERMALINK_PRIVATE_PREFIX_NAME.'/'.$permalink_handle;
	    	} else {
	    		$permalink = $permalink_handle;
	    	}
			$time = timezone_date();
			$pages_params = array(
	    		"site_id" => $this->session->getParameter("_site_id"),
	    		"root_id" => 0,
	    		"parent_id" => 0,
	    		"thread_num" => 0,
	    		"display_sequence" => $display_sequence,
	    		"action_name" => DEFAULT_ACTION,
	    		"parameters" => "",
	    		"page_name" => $private_space_name,
	    		"permalink" => $permalink,
	    		"show_count" => 0,
	    		"private_flag" => _ON,
	    		"default_entry_flag" => $default_entry_flag,
	    		"space_type" => _SPACE_TYPE_GROUP,
	    		"node_flag" => _ON,
	    		"shortcut_flag" => _OFF,
	    		"copyprotect_flag" => _OFF,
	    		"display_scope" => _DISPLAY_SCOPE_NONE,
	    		"display_position" => _DISPLAY_POSITION_CENTER,
	    		"display_flag" => $set_display_flag,
	    		"insert_time" =>$time,
				"insert_site_id" => $this->session->getParameter("_site_id"),
				"insert_user_id" => $this->user_id,
				"insert_user_name" => $user["handle"],
				"update_time" =>$time,
				"update_site_id" => $this->session->getParameter("_site_id"),
				"update_user_id" => $this->user_id,
				"update_user_name" => $user["handle"]
	    	);

	    	$private_page_id = $this->pagesAction->insPage($pages_params, true, false);
	    	if ($private_page_id === false) return 'error';

	    	//
			// ページユーザリンクテーブル追加
			//
			$pages_users_link_params = array(
    			"room_id" => $private_page_id,
    			"user_id" => $this->user_id,
    			"role_authority_id" => _ROLE_AUTH_CHIEF,
    			"createroom_flag" => _OFF
    		);
			$result = $this->pagesAction->insPageUsersLink($pages_users_link_params);
	    	if ($result === false) return 'error';

	    	// ----------------------------------------------------------------------
			// --- 月別アクセス回数初期値登録		                              ---
			// ----------------------------------------------------------------------
			$user_id = $this->user_id;
			$name = "_hit_number";
			$time = timezone_date();
    		$year = intval(substr($time, 0, 4));
    		$month = intval(substr($time, 4, 2));
    		$monthlynumber_params = array(
    					"user_id" =>$user_id,
						"room_id" => $private_page_id,
						"module_id" => 0,
						"name" => $name,
						"year" => $year,
						"month" => $month,
						"number" => 0
    				);
    		$result = $this->monthlynumberAction->insMonthlynumber($monthlynumber_params);
    		if($result === false)  {
	    		return 'error';
	    	}

	    	// ----------------------------------------------------------------------
			// --- 初期ページ追加　　　　　　		                              ---
			// ----------------------------------------------------------------------
			$result = $this->blocksAction->defaultPrivateRoomInsert($private_page_id, $user_id, $handle);
			if($result === false)  {
	    		return 'error';
	    	}

	    	// ----------------------------------------------------------------------
			// --- マイポータル作成　　　　　　		                              ---
			// ----------------------------------------------------------------------
			if($config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP ||
				$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
				//
				// display_sequence取得
				//
				$private_where_params = array(
										"space_type" => _SPACE_TYPE_GROUP,
										"thread_num" => 0,
										"private_flag" => _ON,
										"display_sequence!=0" => null,
										"default_entry_flag" => _ON
									);
				$buf_page_private =& $this->pagesView->getPages($private_where_params, null, 1);
				if ($buf_page_private === false) return 'error';
				if(!isset($buf_page_private[0])) {
					// エラー
					$this->db->addError(get_class($this), sprintf(_INVALID_SELECTDB, "pages"));
					return 'error';
				}
				if(isset($set_authoritiy['myportal_use_flag']) && $set_authoritiy['myportal_use_flag'] == _ON) {
					$display_flag = _ON;
				} else {
					$display_flag = _PAGES_DISPLAY_FLAG_DISABLED;
				}
				$display_sequence = $buf_page_private[0]['display_sequence'];

				//
				// ページテーブル追加
				//
				$pages_params['page_name'] = $user["handle"];
				$pages_params['default_entry_flag'] = _ON;
				$pages_params['display_flag'] = $display_flag;
				$pages_params['display_sequence'] = $display_sequence;
				if(_PERMALINK_MYPORTAL_PREFIX_NAME != '') {
		    		$pages_params['permalink'] = _PERMALINK_MYPORTAL_PREFIX_NAME.'/'.$permalink_handle;
		    	} else {
		    		$pages_params['permalink'] = $permalink_handle;
		    	}
	        	$private_page_id = $this->pagesAction->insPage($pages_params, true, false);
		    	if ($private_page_id === false) return 'error';

		    	//
				// ページユーザリンクテーブル追加
				//
				$pages_users_link_params['room_id'] = $private_page_id;
				$result = $this->pagesAction->insPageUsersLink($pages_users_link_params);
		    	if ($result === false) return 'error';

				//
				// 月別アクセス回数初期値登録
				//
				$monthlynumber_params['room_id'] = $private_page_id;
				$result = $this->monthlynumberAction->insMonthlynumber($monthlynumber_params);
	    		if($result === false)  {
		    		return 'error';
		    	}
			}
    	}
    	//
    	// 固定リンク
    	//
    	$private_where_params = array(
			"{pages}.insert_user_id" => $this->user_id,
			"{pages}.private_flag" => _ON,
			"{pages}.page_id={pages}.room_id" => null
		);

		$private_pages = $this->pagesView->getPagesUsers($private_where_params, array("default_entry_flag" => "ASC"), 2);
		if($private_pages === false) return 'error';
    	$result = $this->pagesAction->updPermaLink($private_pages[0], $permalink_handle);
    	if($result === false)  {
    		return 'error';
    	}
    	if(isset($private_pages[1]) &&
    			$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP ||
				$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
			$result = $this->pagesAction->updPermaLink($private_pages[1], $permalink_handle);
	    	if($result === false)  {
	    		return 'error';
	    	}
		}

    	//
		// 登録時使用　セッション初期化
		//
		$this->session->removeParameter(array("user", "regist", $sess_user_id));
		$this->session->removeParameter(array("user", "regist_public", $sess_user_id));
		$this->session->removeParameter(array("user", "regist_reception", $sess_user_id));
		$this->session->removeParameter(array("user", "regist_auth", $sess_user_id));
		$this->session->removeParameter(array("user", "regist_role_auth", $sess_user_id));
		$this->session->removeParameter(array("user", "regist_confirm", $sess_user_id));
		$this->session->removeParameter(array("user", "selroom", $sess_user_id));
		$this->session->removeParameter(array("user", "selauth", $sess_user_id));

		if($edit_flag) return 'edit_success';
		else if($this->sending_email == "" || $this->sending_email == null) {
			// 新規登録で、Eメール指定なし->新規作成画面へ
			$this->request->setParameter("user_id" , 0);
			return 'regist_success';
		}
		// メール通達メッセージ作成
		$this->mail_add_user =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID, null, null, array($this, "_getConfigByCatidFetchcallback"));
		if ($this->mail_add_user === false) return 'error';

		//X-SITE_NAME等、変換処理を後に追加
		$order_params = array(
			"space_type" => "ASC",
			"private_flag" => "ASC",
			"thread_num" => "ASC",
			"display_sequence" => "ASC"
		);
		$where_params = array("user_id" => $this->user_id,"page_id = {pages}.room_id" => null);

		$entry_room = $this->pagesView->getShowPagesList($where_params, $order_params, 0, 0, array($this, '_entryroom_fetchcallback'));
		if($entry_room === false) {
			return 'error';
		}

		$meta =& $this->session->getParameter("_meta");
		$this->mail_add_user['subject'] = str_replace("{X-SITE_NAME}", $meta['sitename'], $this->mail_add_user['subject']);
		$this->mail_add_user['subject'] = str_replace("{X-HANDLE}", $handle, $this->mail_add_user['subject']);
		$this->mail_add_user['subject'] = str_replace("{X-LOGIN_ID}", $login_id, $this->mail_add_user['subject']);
		$this->mail_add_user['subject'] = str_replace("{X-PASSWORD}", $password, $this->mail_add_user['subject']);
		$this->mail_add_user['subject'] = str_replace("{X-EMAIL}", $this->sending_email, $this->mail_add_user['subject']);
		$this->mail_add_user['subject'] = str_replace("{X-ENTRY_ROOM}", $entry_room, $this->mail_add_user['subject']);
		$this->mail_add_user['subject'] = str_replace("{X-URL}", BASE_URL.INDEX_FILE_NAME, $this->mail_add_user['subject']);

		$this->mail_add_user['body'] = str_replace("{X-SITE_NAME}", $meta['sitename'], $this->mail_add_user['body']);
		$this->mail_add_user['body'] = str_replace("{X-HANDLE}", $handle, $this->mail_add_user['body']);
		$this->mail_add_user['body'] = str_replace("{X-LOGIN_ID}", $login_id, $this->mail_add_user['body']);
		$this->mail_add_user['body'] = str_replace("{X-PASSWORD}", $password, $this->mail_add_user['body']);
		$this->mail_add_user['body'] = str_replace("{X-EMAIL}", $this->sending_email, $this->mail_add_user['body']);
		$this->mail_add_user['body'] = str_replace("{X-ENTRY_ROOM}", $entry_room, $this->mail_add_user['body']);
		$this->mail_add_user['body'] = str_replace("{X-URL}", BASE_URL.INDEX_FILE_NAME, $this->mail_add_user['body']);

		// メール通達画面
		return 'success';
	}


	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$row['is_users_tbl_fld'] = false;
			if( $row['tag_name'] != '' ) {
				if( $this->usersView->isUsersTableField( $row['tag_name'] ) ) {
					$row['is_users_tbl_fld'] = true;
				}
			}
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド(pages)
	 * 参加ルーム取得用
	 * @param result adodb object
	 * @access	private
	 */
	function &_entryroom_fetchcallback($result) {
		$ret = "";
		$row_arr = array();
		//$public_flag = false;
		while ($row = $result->fetchRow()) {
			if($row['default_entry_flag'] == _ON && $row['authority_id'] == null) {
				if($row['private_flag'] == _ON) {
					$_default_entry_auth_private = $this->session->getParameter("_default_entry_auth_private");
					if(isset($_default_entry_auth_private)) {
						$row['authority_id'] = $_default_entry_auth_private;
						$row['hierarchy'] = $this->session->getParameter("_default_entry_hierarchy_private");
					}
				} elseif($row['space_type'] == _SPACE_TYPE_PUBLIC) {
					$_default_entry_auth_public = $this->session->getParameter("_default_entry_auth_public");
					if(isset($_default_entry_auth_public)) {
						$row['authority_id'] = $_default_entry_auth_public;
						$row['hierarchy'] = $this->session->getParameter("_default_entry_hierarchy_public");
					}
				} else {
					$_default_entry_auth_group = $this->session->getParameter("_default_entry_auth_group");
					if(isset($_default_entry_auth_group)) {
						$row['authority_id'] = $_default_entry_auth_group;
						$row['hierarchy'] = $this->session->getParameter("_default_entry_hierarchy_group");
					}
				}
				if($row['authority_id'] == null) {
					$row['authority_id'] = _AUTH_OTHER;
				}
			}
			if(($row['space_type'] == _SPACE_TYPE_GROUP && $row['thread_num'] == 0 && $row['private_flag'] == _OFF) ||
				$row['authority_id'] != _AUTH_OTHER && $row['space_type'] != _SPACE_TYPE_PUBLIC) {
				//if($row['space_type'] == _SPACE_TYPE_PUBLIC && $row['authority_id'] >= _AUTH_CHIEF) {
				//	$public_flag = true;
				//}
				// グループスペースか、バブリックスペースのうち、主担のもの
				$row_arr[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
			}
		}
		function &_entryroom(&$row_arr, $thread_num, $parent_id) {
			$ret = "";
			foreach($row_arr[$thread_num][$parent_id] as $pages) {
				if(!($pages['space_type'] == _SPACE_TYPE_GROUP && $pages['thread_num'] == 0 && $pages['private_flag'] == _OFF)) {
					if ($pages['space_type'] == _SPACE_TYPE_PUBLIC) {
						$max = $pages['thread_num'];
					} else {
						$max = $pages['thread_num'] - 1;
					}
					for($i = 0; $i < $max; $i++) {
						$ret .= "   ";
					}
					$ret .= $pages['page_name']."\n";
				}
				if(isset($row_arr[$thread_num+1]) && isset($row_arr[$thread_num+1][$pages['page_id']])) {
					$ret .= _entryroom($row_arr, $thread_num+1, $pages['page_id']);
				}
			}
			return $ret;
		}
		if(isset($row_arr[0])) {
			foreach($row_arr[0] as $parent_id => $row_sub_arr) {
				$ret .= _entryroom($row_arr, 0, $parent_id);
			}
		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function &_showpages_fetchcallback($result) {
		$ret = array();

		while ($row = $result->fetchRow()) {
			$ret[$row['room_id']] = $row;
		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド(config)
	 * @param result adodb object
	 * @access	private
	 */
	function &_getConfigByCatidFetchcallback($result) {
		$ret = array();

		while ($row = $result->fetchRow()) {
			if (isset($row['CLValue'])) {
				$row['conf_value'] = $row['CLValue'];
			}

			if($row['conf_name'] == "mail_add_user_subject") {
				$ret['subject'] = $row['conf_value'];
			} else if($row['conf_name'] == "mail_add_user_body") {
				$ret['body'] = $row['conf_value'];
			}
		}
		return $ret;
	}


}
?>
