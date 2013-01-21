<?php

/**
 *  * 会員管理>>インポート>>アップロード>>決定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class User_Action_Admin_Import_Confirm extends Action
{
	// リクエストパラメータを受け取るため

	//使用コンポーネント
	var $session = null;
	var $actionChain = null;
	var $db = null;
	var $uploadsAction = null;
	var $usersView = null;
	var $timezoneMain = null;
	var $usersAction = null;
	var $pagesView = null;
	var $pagesAction = null;
	var $authoritiesView = null;
	var $configView = null;
	var $monthlynumberAction = null;
	var $mailMain = null;
	var $blocksAction = null;
	var $languagesView = null;

	// 値をセットするため
	var $allusers_num = null;		// 登録済み全会員数
	var $insUser_cnt = 0;			// 登録した会員数
	var $errUser_cnt = 0;			// 登録／更新できなかった会員数
	var $updUser_cnt = 0;			// 更新した会員数

    /**
     * インポートファイルのアップロード
     *
     * @access  public
     */
    function execute()
    {
        $errorList =& $this->actionChain->getCurErrorList();

		$items = $this->session->getParameter(array("user", "import", "items"));
		$items_public = $this->session->getParameter(array("user", "import", "items_public"));
		$items_reception = $this->session->getParameter(array("user", "import", "items_reception"));
		$users_id = $this->session->getParameter(array("user", "import", "userid"));
        $detail_res = $this->session->getParameter(array("user", "import", "detail_res"));


        for ($idx=1; $idx<=count($items); $idx++) {
			$item = $items[$idx];
			$item_public = $items_public[$idx];
			$item_reception = $items_reception[$idx];
			$user_id = $users_id[$idx];

        	// 会員登録
			if(isset($item) && ($item != null)) {
				$res_setUser = $this->setUser($user_id, $item, $item_public, $item_reception);
				if($res_setUser === "success") {
					if($user_id == null || $user_id == "0")	$this->insUser_cnt++;
					else $this->updUser_cnt++;
				} else {
					$this->errUser_cnt++;
				}
			}
 		}

		$sql = "SELECT COUNT(*) FROM {users} WHERE 1";
		$allusers_num = $this->db->execute($sql, null, null, null, true, null);
		$this->allusers_num = $allusers_num["0"]["COUNT(*)"];

		$this->session->removeParameter(array("user", "import"));

		return "success";
	}

	/**
	 * 会員情報の登録
	 * @param user_id　base_items　base_items_public　base_items_reception
	 * @return success or error
	 * @access private
	 */
	function setUser($user_id, $base_items, $base_items_public, $base_items_reception)
	{
	    $errorList =& $this->actionChain->getCurErrorList();

		$edit_flag = _ON;
		if($user_id == null || $user_id == "0") {
			$user_id = "0";
			$edit_flag = _OFF;
		}
		$sess_user_id = $user_id;
    	$where_params = array(
    						"user_authority_id" => _AUTH_ADMIN,		// 管理者固定
    						"type != 'label'" => null,
    						"type != 'system'" => null
    					);
		$items =& $this->usersView->getItems($where_params, null, null, null, array($this, "_getItemsFetchcallback"));
		if($items === false) return 'error';

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
			$users =& $this->db->selectExecute("users", array("user_id" => $user_id));
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

		// 基本項目(usersテーブル)
		$db_users_items_link =& $this->usersView->getUserItemLinkById($user_id);
		if($db_users_items_link === false) return 'error';

		$handle = "";
		$login_id = "";
		$password = "";

		$users_items_link = array();
		$users_items_link_flag_arr = array();
		$languages = $this->languagesView->getLanguagesList();
		if ($languages === false) return false;

		foreach($items as $item_id => $item) {
			$users_items_link[$item_id] = array(
				"user_id" => $user_id,
				"item_id" => $item_id,
				"public_flag" => _ON,
				"email_reception_flag" => _OFF
			);

			if(isset($base_items[$item_id])) {
				if($item['tag_name'] == 'lang_dirname_lang'
					&& (empty($base_items[$item_id])
						 || !array_key_exists($base_items[$item_id], $languages)
						 || $base_items[$item_id] == USER_IMPORT_LANGUAGE_AUTO)) {
					$base_item = "";
				}else {
					$base_item = $base_items[$item_id];
				}
			} else {
				$base_item = "";
			}

			if( $item['tag_name'] != "" && $item['is_users_tbl_fld']!=false ) {
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
						break;
					case "timezone_offset_lang":
						$tag_name = "timezone_offset";
						if(defined($base_item)) {
							$base_item = $this->timezoneMain->getFloatTimeZone(constant($base_item));
//							if($this->session->getParameter("_user_id") == $user_id) {
//								$this->session->setParameter("_timezone_offset",$base_item);
//							}
						}
						break;
				}
				$users_items_link_flag_arr[$item_id] = false;
				if($tag_name == "password" && $edit_flag == _ON && $base_item == "") {
					// パスワードは変更しない
					continue;
				}
				if($item['tag_name'] == "password") $base_item = md5($base_item);
				if($edit_flag && $tag_name == "active_flag" && $user[$tag_name] != _USER_ACTIVE_FLAG_ON &&
					$user[$tag_name] !=_USER_ACTIVE_FLAG_OFF) {
					// 編集で、データがON、あるいは、OFF以外のデータならば、変更を許さない
				} else {
					$user[$tag_name] = $base_item;
				}
			} else {
				// users_items_linkデータ
				if($item['type'] == "radio" || $item['type'] == "checkbox" ||
					$item['type'] == "select") {
					if(is_array($base_item)) {
						$users_items_link[$item_id]['content'] = implode("|", $base_item);
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
				if(isset($base_items_public[$item_id]) && ($base_items_public[$item_id] == _ON)) {
					$users_items_link[$item_id]['public_flag'] = _ON;
				} else {
					$users_items_link[$item_id]['public_flag'] = _OFF;
				}
			}
			if($item['allow_email_reception_flag'] ==_ON) {
				if(isset($base_items_reception[$item_id]) && ($base_items_reception[$item_id] == _ON)) {
					$users_items_link[$item_id]['email_reception_flag'] = _ON;
				}
			}
		}

		if(!$edit_flag) {
			// 新規作成
			$user_id = $this->usersAction->insUser($user);
			if($user_id === false) {
				return 'error';
			}
		} else {
			// 変更
			$old_user = $this->usersView->getUserById($user_id);
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
						"{pages}.insert_user_id" => $user_id,
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
			$result = $this->usersAction->updUsers($user, $where_params);
			if($result === false) return 'error';
		}

		// 詳細項目(users_items_linkデータ登録)
    	foreach($users_items_link as $item_id => $users_item_link) {
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
			    			$result = $this->usersAction->delUsersItemsLinkById($item_id, $user_id);
			    			if($result === false) return 'error';
						}
				} else {
					// 更新 or 新規追加
					if($users_item_link['user_id'] == 0) {
	    				$users_item_link['user_id'] = $user_id;
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
								"unique_id" => $user_id
							);
							$upload_where_params = array(
								"upload_id" => $upload_id,
								"unique_id" => 0,
								"garbage_flag" => _ON
							);
							$upload_result = $this->uploadsAction->updUploads($upload_params, $upload_where_params);
							if ($upload_result === false) return 'error';
						}
	    			}
				}
			}
    	}
    	$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return 'error';

		// プライベートスペース作成
		$permalink_handle = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $handle);
		if(_PERMALINK_PRIVATE_PREFIX_NAME != '') {
    		$myroom_permalink = _PERMALINK_PRIVATE_PREFIX_NAME.'/'.$permalink_handle;
    	} else {
    		$myroom_permalink = $permalink_handle;
    	}
		if(_PERMALINK_MYPORTAL_PREFIX_NAME != '') {
    		$myportal_permalink = _PERMALINK_MYPORTAL_PREFIX_NAME.'/'.$permalink_handle;
    	} else {
    		$myportal_permalink = $permalink_handle;
    	}
		if(!$edit_flag) {
	    	//権限テーブルのmyroom_use_flagにかかわらずプライベートスペース作成
			// ページテーブル追加
			$private_where_params = array(
										"space_type" => _SPACE_TYPE_GROUP,
										"thread_num" => 0,
										"private_flag" => _ON,
										"display_sequence!=0" => null
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
	    		"permalink" => $this->pagesAction->getRenamePermaLink($myroom_permalink),
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
				"insert_user_id" => $user_id,
				"insert_user_name" => $user["handle"],
				"update_time" =>$time,
				"update_site_id" => $this->session->getParameter("_site_id"),
				"update_user_id" => $user_id,
				"update_user_name" => $user["handle"]
	    	);
	    	$private_page_id = $this->pagesAction->insPage($pages_params, true, false);
	    	if ($private_page_id === false) return 'error';

	    	// ページユーザリンクテーブル追加
			$pages_users_link_params = array(
    			"room_id" => $private_page_id,
    			"user_id" => $user_id,
    			"role_authority_id" => _ROLE_AUTH_CHIEF,
    			"createroom_flag" => _OFF
    		);
			$result = $this->pagesAction->insPageUsersLink($pages_users_link_params);
	    	if ($result === false) return 'error';

			// 月別アクセス回数初期値登録
			$user_id = $user_id;
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
			$result = $this->blocksAction->defaultPrivateRoomInsert($private_page_id, $user_id,$handle);
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
		    	$pages_params['permalink'] = $this->pagesAction->getRenamePermaLink($myportal_permalink);

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
    	} else {
    		$result = $this->db->selectExecute("pages", array("private_flag" => _ON, "thread_num" => 0, "insert_user_id" => $user_id), array("default_entry_flag" => "ASC"), 2, 0);
			if(isset($result[0])) {
				$myroom_permalink = $this->pagesAction->getRenamePermaLink($myroom_permalink);
    			$this->pagesAction->updPermaLink($result[0], $permalink_handle);
			}
			if(isset($result[1])) {
				$myroom_permalink = $this->pagesAction->getRenamePermaLink($myportal_permalink);
    			$this->pagesAction->updPermaLink($result[1], $permalink_handle);
			}
    	}
    	//
    	// 固定リンク
    	//
    	/* 現状、マイルーム、マイポータルには１ページしかないため、必要なし
    	$private_where_params = array(
			"{pages}.insert_user_id" => $user_id,
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
		*/

		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result
	 * @return ret
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$row['is_users_tbl_fld'] = false;	// mod AllCreator
			if( $row['tag_name'] != '' ) {
				if( $this->usersView->isUsersTableField( $row['tag_name'] ) ) {
					$row['is_users_tbl_fld'] = true;
				}
			}
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}
}
?>