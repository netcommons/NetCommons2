<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム作成-ルーム編集実行（DB登録）
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Regist_Selectusers extends Action
{
	// リクエストパラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	var $selected_auth_id = null;
	//var $append_flag = null;				// 追加登録かどうか(未使用)
	var $continue_flag = null;				// 続けて登録

	// バリデートによりセット
	var $parent_page = null;
	var $page = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $pagesView = null;
	var $usersView = null;
	var $pagesAction = null;
	var $monthlynumberAction = null;
	var $request = null;
	var $db = null;
	var $getdata = null;
	var $modulesView = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->edit_current_page_id = ($this->edit_current_page_id === null) ? 0 : intval($this->edit_current_page_id);
		$this->selected_auth_id = ($this->selected_auth_id === -1 || $this->selected_auth_id === null) ? null : intval($this->selected_auth_id);
		if($this->parent_page === null && $this->edit_current_page_id === 0) return 'error';
		//
    	// 参加者取得(Room_View_Admin_Regist_Userslist-confirm_flag=_ONと同様の処理)
    	//
    	$select_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_select_str"));
    	$from_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_from_str"));
	    $add_from_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_add_from_str"));
  		$where_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_where_str"));
    	$add_where_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_add_where_str"));
    	$sess_params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_params"));
    	$sess_from_params = $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_from_params"));
    	$sess_where_params = $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_where_params"));

    	$sql_params = array_merge((array)$sess_params, (array)$sess_from_params, (array)$sess_where_params);

    	$order_params = array("system_flag" => "DESC", "hierarchy"=>"DESC" , "user_authority_id"=>"DESC" , "handle"=>"ASC");
		$order_str = $this->db->getOrderSQL($order_params);

		$sql = $select_str.$from_str.$add_from_str.$where_str.$add_where_str.$order_str;

		// 管理者一覧取得
		$admin_users =& $this->usersView->getUsers(array("user_authority_id" => _AUTH_ADMIN), null,  array($this, "_usersFetchcallback"));

		//$admin_users_count = count($admin_users);	// 管理者数
		if($admin_users === false) return 'error';
		$room_authority_arr =& $this->session->getParameter(array("room", $this->edit_current_page_id, "room_authority"));
		$room_createroom_flag_arr =& $this->session->getParameter(array("room", $this->edit_current_page_id, "room_createroom_flag"));

		//foreach($room_authority_arr as $user_id => $room_authority) {
		//	if(isset($admin_users[$user_id])) {
		//		// 管理者権限
		//		//$admin_users_count--;
		//		unset($admin_users[$user_id]);
		//	} else {
		//		break;
		//	}
		//}
		$room_name =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","room_name"));
		$display_flag =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","display_flag"));
		$default_entry_flag =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","space_type_common"));

    	if($this->edit_current_page_id == 0) {
    		//
    		// 新規ルーム作成
    		//
    		$edit_flag = _OFF;
    		// ----------------------------------------------------------------------
			// --- ページテーブル追加                                             ---
			// ----------------------------------------------------------------------
    		//言語切替の対応
			$lang_dirname = "";
			if($this->parent_page['space_type'] == _SPACE_TYPE_PUBLIC && $this->parent_page['display_position'] == _DISPLAY_POSITION_CENTER) {
				//パブリックスペースのセンターカラムのページlang_dirname=japaneseもしくはenglishとする
				$lang_dirname = $this->session->getParameter("_lang");
			}
			$count = $this->pagesView->getMaxChildPage($this->parent_page_id, $lang_dirname);
	    	$count = intval($count) + 1;

	    	// 固定値
	    	$action_name = DEFAULT_ACTION;
	    	if($action_name == "install_view_main_init") {
	    		// 念のため
	    		$action_name = "pages_view_main";
	    	}
	    	$parameters = "";
	    	$show_count = 0;
	    	$node_flag = _ON;
	    	$shortcut_flag = _OFF;
	    	$copyprotect_flag = _OFF;
	    	$display_scope = _DISPLAY_SCOPE_NONE;

			if($this->parent_page['root_id'] == 0) $root_id = $this->parent_page['page_id'];
	    	else  $root_id = $this->parent_page['root_id'];

	    	$params = array(
	    		"site_id" => $this->session->getParameter("_site_id"),
	    		"root_id" => $root_id,
	    		"parent_id" => intval($this->parent_page_id),
	    		"thread_num" => $this->parent_page['thread_num'] + 1,
	    		"display_sequence" => $count,
	    		"action_name" => $action_name,
	    		"parameters" => $parameters,
	    		"lang_dirname" => $lang_dirname,
	    		"page_name" => $room_name,
	    		"permalink" => '',
	    		"show_count" => $show_count,
	    		"private_flag" => $this->parent_page['private_flag'],
	    		"default_entry_flag" => $default_entry_flag,
	    		"space_type" => $this->parent_page['space_type'],
	    		"node_flag" => $node_flag,
	    		"shortcut_flag" => $shortcut_flag,
	    		"copyprotect_flag" => $copyprotect_flag,
	    		"display_scope" => $display_scope,
	    		"display_position" => $this->parent_page['display_position'],
	    		"display_flag" => $display_flag
	    	);
	    	$edit_current_page_id = $this->pagesAction->insPage($params, true);
	    	if($edit_current_page_id === false) {
	    		return 'error';
	    	}
	    	$current_page = $params;
	    	$current_page['page_id'] = $edit_current_page_id;
	    	$current_page['room_id'] = $edit_current_page_id;
	    	$result = $this->pagesAction->updPermaLink($current_page, $room_name, $this->parent_page);
	    	if($result === false) {
	    		return 'error';
	    	}
	    	// ----------------------------------------------------------------------
			// --- ページスタイルテーブル追加 　　                                ---
			// --- サブグループで親ルームにページスタイルテーブルのデータがあれば ---
			// --- 追加する                                                       ---
			// ----------------------------------------------------------------------
			if($this->parent_page['thread_num'] + 1 == 2) {
				$pages_style = $this->pagesView->getPagesStyle(array("set_page_id" => intval($this->parent_page_id)));
				if(isset($pages_style[0])) {
					$pages_style[0]["set_page_id"] = $edit_current_page_id;
					$result = $this->pagesAction->insPageStyle($pages_style[0]);
				}
			}

	    	// ----------------------------------------------------------------------
			// --- ページユーザリンクテーブル追加                                 ---
			// ----------------------------------------------------------------------
			$func_params = array(
								$this->pagesAction,
								$edit_current_page_id,
								$default_entry_flag,
								$this->parent_page['thread_num'] + 1,
								$this->parent_page['private_flag'],
								$edit_flag,
								$this->selected_auth_id,
								$admin_users,
								$room_authority_arr,
								$room_createroom_flag_arr,
								null,
								$this->parent_page,
								0
							);

			$result = $this->db->execute($sql, $sql_params, null, null, true, array($this, "_fetchcallback"), $func_params);
			if($result === false) {
	    		return 'error';
	    	}
	    	// ----------------------------------------------------------------------
			// --- ページモジュールリンクテーブル追加                             ---
			// ----------------------------------------------------------------------
			// デフォルト親の使用できるモジュールを使用許可としておく
			$pages_modules_links =& $this->pagesView->getPageModulesLink(array('room_id' => $this->parent_page['page_id']));
			$error_flag = false;
			foreach($pages_modules_links as $pages_modules_link) {
	    		$params = array(
	    			"room_id" => $edit_current_page_id,
	    			"module_id" => $pages_modules_link['module_id']
	    		);
	    		$result = $this->pagesAction->insPagesModulesLink($params);
		    	if($result === false) {
		    		$error_flag = true;
		    	}
	    	}
	    	if($error_flag) {
	    		return 'error';
	    	}
	    	// ----------------------------------------------------------------------
			// --- 月別アクセス回数初期値登録		                              ---
			// ----------------------------------------------------------------------
			$user_id = $this->session->getParameter('_user_id');
			$name = "_hit_number";
			$time = timezone_date();
    		$year = intval(substr($time, 0, 4));
    		$month = intval(substr($time, 4, 2));
    		$params = array(
    					"user_id" =>$user_id,
						"room_id" => $edit_current_page_id,
						"module_id" => 0,
						"name" => $name,
						"year" => $year,
						"month" => $month,
						"number" => 0
    				);
    		$result = $this->monthlynumberAction->insMonthlynumber($params);
    		if($result === false)  {
	    		return 'error';
	    	}

	    	// ---------------------------------------------------------------------------
			// --- センターカラムにメニューがはってある場合、非表示としてルームを登録  ---
			// ---------------------------------------------------------------------------
	    	$module = $this->modulesView->getModuleByDirname("menu");
	    	if(isset($module['module_id'])) {
				$config_obj = $this->getdata->getParameter("config");

				// 左右カラムのpage_idをセッションにセット
				// （パブリックスペースpage_id | プライベートスペースpage_id | グループスペースpage_id）
				$headercolumn_page_id_str = $config_obj[_PAGESTYLE_CONF_CATID]['headercolumn_page_id']['conf_value'];
				$leftcolumn_page_id_str   = $config_obj[_PAGESTYLE_CONF_CATID]['leftcolumn_page_id']['conf_value'];
				$rightcolumn_page_id_str  = $config_obj[_PAGESTYLE_CONF_CATID]['rightcolumn_page_id']['conf_value'];

				$page_id_arr = array_merge ( explode("|",$headercolumn_page_id_str), explode("|",$leftcolumn_page_id_str), explode("|",$rightcolumn_page_id_str));

				$where_params = array(
					"page_id NOT IN (" . implode(',', $page_id_arr) . ") " => null,
					"module_id" => $module['module_id']
				);
				$blocks = $this->db->selectExecute("blocks", $where_params);
				foreach($blocks as $block) {
					$block_page_id = intval($block['page_id']);
			    	$where_params = array(
						"page_id" => $block_page_id
					);
					$current_page =& $this->pagesView->getPages($where_params);

					$params = array(
						"block_id" => $block['block_id'],
						"page_id" => $edit_current_page_id,
						"visibility_flag" => _OFF,
						"room_id" => $current_page[0]['room_id']
					);
					$this->db->insertExecute("menu_detail", $params);
				}
			}


    	} else {
    		//
    		// ルーム編集
    		//
    		$edit_flag = _ON;
    		$edit_current_page_id = intval($this->edit_current_page_id);
    		// ----------------------------------------------------------------------
			// --- ページテーブル編集                                             ---
			// ----------------------------------------------------------------------
			//$params = array(
    		//			"page_name" =>$room_name,
			//			"display_flag" => $display_flag,
			//			"default_entry_flag" => $default_entry_flag
    		//		);
			//$upd_where_params = array("page_id" => intval($this->edit_current_page_id));
			//$result = $this->pagesAction->updPage($params, $upd_where_params);
	    	//if($result === false) {
	    	//	return 'error';
	    	//}

    		// ----------------------------------------------------------------------
			// --- ページユーザリンクテーブル編集                                 ---
			// ----------------------------------------------------------------------
			$pages_users_links =& $this->pagesView->getPageUsersLink(array("room_id" => $edit_current_page_id), null, array($this,"_usersFetchcallback"));
			if($pages_users_links === false) return 'error';
			$func_params = array(
								$this->pagesAction,
								$edit_current_page_id,
								$default_entry_flag,
								$this->page['thread_num'],
								$this->page['private_flag'],
								$edit_flag,
								$this->selected_auth_id,
								$admin_users,
								$room_authority_arr,
								$room_createroom_flag_arr,
								$pages_users_links,
								$this->page
							);

			$result = $this->db->execute($sql, $sql_params, null, null, true, array($this, "_fetchcallback"), $func_params);
			if($result === false) {
	    		return 'error';
	    	}
	    	// ----------------------------------------------------------------------
	    	// --- サブグループ更新処理											  ---
	    	// --- 現在、親で参加しているメンバー以外は削除する					  ---
			// ----------------------------------------------------------------------
			if($this->page['thread_num'] == 1) {
				// サブグループ会員情報取得
				$where_params = array (
										"parent_id"=>$edit_current_page_id,
										"{pages}.page_id={pages}.room_id" => null
									);
		    	$subgroup_pages_users =& $this->pagesView->getPagesUsers($where_params, null, null, null, array($this, "_pagesusersFetchcallback"));
		    	if($subgroup_pages_users === false) {
		    		return 'error';
		    	}

		    	$where_params = array (
										"{pages}.page_id"=>$edit_current_page_id
									);
				// 親ルーム会員情報取得
		    	$pages_users =& $this->pagesView->getPagesUsers($where_params, null, null, null, array($this, "_usersFetchcallback"));
		    	if($pages_users === false) {
		    		return 'error';
		    	}
		    	if(count($subgroup_pages_users) > 0) {
		    		foreach($subgroup_pages_users as $page_id => $subgroup_page_users) {
		    			foreach($subgroup_page_users as $user_id => $subgroup_page_user) {
			    			if((!$default_entry_flag && !isset($pages_users[$user_id])) ||
			    				($default_entry_flag && isset($pages_users[$user_id]) &&
			    				 $pages_users[$user_id]['authority_id'] == _AUTH_OTHER)) {

			    				$where_params = array(
											"room_id" => $page_id,
											"user_id" => $user_id
										);
			    				$resultDelpage = $this->pagesAction->delPageUsersLink($where_params);
								if ($resultDelpage === false) return 'error';
			    			}
		    			}
		    		}
		    	}
			}
    	}

		// ----------------------------------------------------------------------
		// --- 終了処理　　　　　                                             ---
		// ----------------------------------------------------------------------
		// Sessionクリア
		$this->session->removeParameter(array("room", $this->edit_current_page_id));
		$this->session->removeParameter(array("room", "search"));
		if(isset($this->continue_flag) && $this->continue_flag == _ON) {
			// リクエストパラメータセット

			$this->request->setParameter("action", "room_view_admin_regist_selectusers");
			$this->request->setParameter("edit_current_page_id", $edit_current_page_id);
			$this->request->setParameter("room_name", $room_name);
			$this->request->setParameter("display_flag", $display_flag);
			$this->request->setParameter("space_type_common", $default_entry_flag);

			return 'continue_success';
		} else {
			//$parent_pages =& $this->pagesView->getPages(array("thread_num" => 0, "space_type" => $this->parent_page['space_type']));
			//if($parent_pages === false) return 'error';
	    	// リスト表示のリクエストパラメータセット
	    	$this->request->setParameter("action", "room_view_admin_regist_confirm");
	    	$this->request->setParameter("edit_current_page_id", $edit_current_page_id);
	    	if($this->edit_current_page_id == 0) {
	    		$this->request->setParameter("edit_flag", _OFF);
	    	} else {
	    		$this->request->setParameter("edit_flag", _ON);
	    	}
	    	/*
	    	if(isset($this->parent_page['space_type'])) {
	    		$this->request->setParameter("show_space_type", $this->parent_page['space_type']);
				$this->request->setParameter("show_private_flag", $this->parent_page['private_flag']);
	    	} else {
	    		$this->request->setParameter("show_space_type", $this->page['space_type']);
				$this->request->setParameter("show_private_flag", $this->page['private_flag']);
	    	}
			*/

			return 'success';
		}
	}


	/**
	 * fetch時コールバックメソッド(確認画面)
	 * @param result adodb object
	 * @param array  func_params
	 * 						pagesAction
	 * 						$edit_current_page_id
	 * 						$current_default_entry_flag
	 * 						$current_thread_num
     * 						$current_private_flag
	 * 						編集中かどうか
	 * 						全選択を押下しているかどうか（している場合、押下している権限）
	 * 						追加する管理者権限の会員
	 * 						表示された権限リスト					$room_authority_arr
	 * 						表示されたサブグループ作成許可リスト	$room_create_flag_arr
	 * 						親ページ配列							$parent_page
	 * 						ページユーザリンクデータ				$pages_users_links
	 * 						追加登録かどうか						$append_flag
	 * @return array
	 * @access	private
	 */
	function &_fetchcallback($result, $func_params) {
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$authoritiesView =& $container->getComponent("authoritiesView");
		$configView =& $container->getComponent("configView");

		$ret = true;

		$pagesAction =& $func_params[0];
		$edit_current_page_id = $func_params[1];
		$default_entry_flag = $func_params[2];
		$current_thread_num = $func_params[3];
		$current_private_flag = $func_params[4];
		$edit_flag = $func_params[5];
		$selected_auth_id = $func_params[6];
		$admin_users = $func_params[7];
		$room_authority_arr =& $func_params[8];
		$room_createroom_flag_arr =& $func_params[9];
		$pages_users_links =& $func_params[10];
		$parent_page =& $func_params[11];
		//$append_flag = $func_params[12];
		if($selected_auth_id !== null) {
			$authoritiy =& $authoritiesView->getAuthorityById(intval($selected_auth_id));
			if($authoritiy === false) {
				return false;
			}
		}
		$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return false;

		$default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
    	$default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];
    	$default_entry_role_auth_private = $config['default_entry_role_auth_private']['conf_value'];

		$space_type = $parent_page['space_type'];
		$private_flag = $current_private_flag;
		//$private_flag = $parent_page['private_flag'];
		if($private_flag == _ON) {
			$default_entry_auth = $default_entry_role_auth_private;
		} else if($space_type == _SPACE_TYPE_GROUP) {
			$default_entry_auth = $default_entry_role_auth_group;
		} else {
			$default_entry_auth = $default_entry_role_auth_public;
		}
		/*
		if($edit_flag) {
			// 編集中＋追加登録ではない
			// 参加会員削除処理
			$where_params = array("room_id" => $edit_current_page_id);
			$resultDelpage = $pagesAction->delPageUsersLink($where_params);
			if ($resultDelpage === false) return 'error';
		}
		*/
		//
		// 自分自身は必ず主担として登録
		//
		$admin_users[$session->getParameter("_user_id")]['user_id'] = $session->getParameter("_user_id");

		// 管理者Insert
		if(!$edit_flag && $private_flag == _OFF) {
		//if(!$edit_flag) {
			foreach($admin_users as $admin_user) {
				if($space_type == _SPACE_TYPE_PUBLIC) {
					$createroom_flag = _OFF;
				} else if($current_thread_num == 0) {
					// 深さ0ならば作成権限OFF
					$createroom_flag = _OFF;
				} else if($current_thread_num == 1) {
					$createroom_flag = _ON;
				} else {
					$createroom_flag = _OFF;
				}
				//if($parent_page['thread_num'] == 0 && $parent_page['private_flag'] == _OFF) {
				//	$createroom_flag = _ON;
				//} else {
				//	$createroom_flag = _OFF;
				//}
				//if(($edit_flag && !$edit_flag) || $pages_users_links == null) {
				//if($pages_users_links == null || !isset($pages_users_links[$admin_user['user_id']])) {
					$params = array(
		    			"room_id" => $edit_current_page_id,
		    			"user_id" => $admin_user['user_id'],
		    			"role_authority_id" => _ROLE_AUTH_CHIEF,
		    			"createroom_flag" => $createroom_flag
		    		);
		    		$resultPageUserLink = $pagesAction->insPageUsersLink($params);
			    	if($resultPageUserLink === false) {
			    		$ret = false;
			    	}
				//}
			}
		}
		while ($row = $result->fetchRow()) {
			$none_chg_flag = _OFF;
			if(isset($admin_users[$row['user_id']]) && $private_flag == _OFF && (!$edit_flag || $row['user_id'] != $session->getParameter("_user_id") || $row['user_authority_id'] == _AUTH_ADMIN)) {
				// 管理者
				continue;
			} else if(isset($room_authority_arr[$row['user_id']])) {
				$row['authority_id'] = $room_authority_arr[$row['user_id']];
				$row['createroom_flag'] = $room_createroom_flag_arr[$row['user_id']];
				//if($row['createroom_flag'] == null) $row['createroom_flag'] = _OFF;
			} else if($selected_auth_id !== null) {
				if((($private_flag == _ON) && $row['private_createroom_flag'] == _ON) ||
					($space_type == _SPACE_TYPE_PUBLIC && $row['public_createroom_flag'] == _ON) ||
					($space_type == _SPACE_TYPE_GROUP && $row['group_createroom_flag'] == _ON)
				) {
					$row['authority_id'] = $selected_auth_id;
					$row['createroom_flag'] = _OFF;
				} else {
					// ルーム作成権限がないならば、自分の権限以上にはなれない
					if($authoritiy['hierarchy'] <= $row['hierarchy']) {
						$row['authority_id'] = $selected_auth_id;
					} else {
						if($default_entry_flag == _ON) {
							if($row['authority_id'] === null) {
								if($row['user_authority_id'] == _AUTH_ADMIN) {
									$regist_role_auth = _ROLE_AUTH_ADMIN;
								} else if($row['user_authority_id'] == _AUTH_CHIEF) {
									$regist_role_auth = _ROLE_AUTH_CHIEF;
								} else if($row['user_authority_id'] == _AUTH_MODERATE) {
									$regist_role_auth = intval($row['role_authority_id']);
								} else if($row['user_authority_id'] == _AUTH_GENERAL) {
									$regist_role_auth = _ROLE_AUTH_GENERAL;
								} else if($selected_auth_id == _AUTH_GUEST || $row['user_authority_id'] == _AUTH_GUEST) {
									$regist_role_auth = _ROLE_AUTH_GUEST;
								} else if($default_entry_flag == _ON) {
								} else {
									$regist_role_auth = _ROLE_AUTH_OTHER;
								}
								$row['authority_id'] = $regist_role_auth;
							}
						} else {
							if($edit_flag == _ON) {
								$none_chg_flag = _ON;
							}
							$row['authority_id'] = _ROLE_AUTH_OTHER;
						}
					}
					$row['createroom_flag'] = _OFF;
				}
			} else {
				$row['createroom_flag'] = _OFF;

				if(($session->getParameter("_user_id") == $row['user_id'] && $private_flag == _ON) || ($edit_flag == _OFF && $row['role_authority_id'] != _ROLE_AUTH_ADMIN &&
							$session->getParameter("_user_id") == $row['user_id'])) {
					// 管理者ではなく、ログインしている会員がルームを作成している場合、主担にチェックをつける
					$row['authority_id'] = _ROLE_AUTH_CHIEF;
				} else if($default_entry_flag == _ON && $row['user_authority_id'] == _AUTH_GUEST) {
					$row['authority_id'] = _ROLE_AUTH_GUEST;
				} else if($default_entry_flag == _ON) {
					$row['authority_id'] = $default_entry_auth; //$regist_role_auth;
				} else {
					$row['authority_id'] = _ROLE_AUTH_OTHER;
				}
				if($edit_flag == _ON) {
					$none_chg_flag = _ON;
				}
			}
			if($space_type == _SPACE_TYPE_PUBLIC && $row['authority_id'] == _ROLE_AUTH_OTHER) {
				// パブリックスペースは最低ゲストとして参加
				$row['authority_id'] = _ROLE_AUTH_GUEST;
			}

			// 深さが１でないならばサブグループ作成許可はOFF
			if($current_thread_num != 1) $row['createroom_flag'] = _OFF;
			if(($row['authority_id'] != _ROLE_AUTH_OTHER && $default_entry_flag == _OFF) ||
				($default_entry_flag == _ON && ($row['authority_id'] != $default_entry_auth || $row['createroom_flag'] == _ON))
				) {

	    		if($edit_flag && isset($pages_users_links[$row['user_id']])) {
	    			// update
	    			$params = array(
		    			"role_authority_id" => $row['authority_id'],
		    			"createroom_flag" => $row['createroom_flag']
		    		);
	    			$where_params = array(
		    			"room_id" => $edit_current_page_id,
		    			"user_id" => $row['user_id']
		    		);
	    			$resultPageUserLink = $pagesAction->updPageUsersLink($params, $where_params);
	    		} else {
	    			$params = array(
		    			"room_id" => $edit_current_page_id,
		    			"user_id" => $row['user_id'],
		    			"role_authority_id" => $row['authority_id'],
		    			"createroom_flag" => $row['createroom_flag']
		    		);
	    			// insert
	    			$resultPageUserLink = $pagesAction->insPageUsersLink($params);
	    		}
		    	if($resultPageUserLink === false) {
		    		$ret = false;
		    	}
			} else if($edit_flag) {
				// 追加登録の場合の削除処理
				if($none_chg_flag != _ON) {
					$where_params = array(
											"room_id" => $edit_current_page_id,
											"user_id" => $row['user_id']
										);
					$resultDelpage = $pagesAction->delPageUsersLink($where_params);
					if ($resultDelpage === false) return 'error';
				}

			}

		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_usersFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['user_id']] = $row;
		}
		return $ret;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	//function &_subpagesFetchcallback($result) {
	//	$ret = array();
	//	while ($row = $result->fetchRow()) {
	//		$ret[$row['page_id']] = $row['page_id'];
	//	}
	//	return $ret;
	//}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_pagesusersFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['page_id']][$row['user_id']] = $row;
		}
		return $ret;
	}
}
?>