<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限の登録・編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Action_Admin_Regist extends Action
{
	// リクエストパラメータを受け取るため

	var $role_authority_id = null;
	var $role_authority_name = null;
	var $user_authority_id = null;

	// SetDafaultフィルタによりセット
	var $module_id = null;

    // 使用コンポーネントを受け取るため

	var $db = null;
	var $pagesView = null;
	var $pagesAction = null;
	var $usersView = null;
	var $modulesView = null;
	var $authoritiesAction = null;
	var $session = null;
	var $authoritiesView = null;
	var $authorityCompmain = null;

	// バリデートによりセット
	var $authority = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$save_role_authority_id = $this->role_authority_id;

    	//
		// config.iniの値を取得
		//
		$config = $this->authorityCompmain->getConfig($this->role_authority_id, $this->user_authority_id);

		//
		// 管理系モジュール取得
		//
		$func = array($this->authorityCompmain, "setModules");
		$result =& $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($this->role_authority_id, array("system_flag"=>_ON), null, $func, array($config['sys_modules']));
		if($result === false) {
			return 'error';
		}
		list($sys_modules, $site_modules) = $result;


		$func = array($this->authorityCompmain,"setAuthoritiesModules");
		$modules_obj = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId(intval($this->role_authority_id), array("system_flag"=>_OFF), null, $func, array($this->role_authority_id));

		// hierarchy
		if ($this->user_authority_id != _AUTH_MODERATE) {
			switch ($this->user_authority_id) {
				case _AUTH_ADMIN:
					$hierarchy = _HIERARCHY_ADMIN;
					break;
				case _AUTH_CHIEF:
					$hierarchy = _HIERARCHY_CHIEF;
					break;
				case _AUTH_GENERAL:
					$hierarchy = _HIERARCHY_GENERAL;
					break;
				default:
					$hierarchy = _HIERARCHY_GUEST;
					break;
			}
		} else {
			$hierarchy = intval($this->session->getParameter(array("authority", $save_role_authority_id, "level", "hierarchy")));
			if($hierarchy < 0) $hierarchy = 3;
			else if($hierarchy > 100) $hierarchy = 100;
			$hierarchy += 3;
		}

		// myroom_use_flag
		if (isset($config['myroom_use_flag']['default'][_ON])) {
			$myroom_use_flag = _ON;
		} else {
			$myroom_use_flag = _OFF;
		}

		// public_createroom_flag
		if ($this->user_authority_id == _AUTH_ADMIN ||
			$this->session->getParameter(array("authority", $save_role_authority_id, "detail", "public_createroom_flag"))) {
			$public_createroom_flag = _ON;
		} else {
			$public_createroom_flag = _OFF;
		}

		// group_createroom_flag
		if ($this->user_authority_id == _AUTH_ADMIN ||
			$this->session->getParameter(array("authority", $save_role_authority_id, "detail", "group_createroom_flag"))) {
			$group_createroom_flag = _ON;
		} else {
			$group_createroom_flag = _OFF;
		}

		// allow_htmltag_flag
		if (isset($config['allow_htmltag_flag']['default'][_ON])) {
			$allow_htmltag_flag = _ON;
		} else {
			$allow_htmltag_flag = _OFF;
		}

		// allow_layout_flag
		if (isset($config['allow_layout_flag']['default'][_ON])) {
			$allow_layout_flag = _ON;
		} else {
			$allow_layout_flag = _OFF;
		}

		// allow_attachment
		if (isset($config['allow_attachment']['default'][_ALLOW_ATTACHMENT_ALL])) {
			$allow_attachment = _ALLOW_ATTACHMENT_ALL;
		} else if (isset($config['allow_attachment']['default'][_ALLOW_ATTACHMENT_IMAGE])) {
			$allow_attachment = _ALLOW_ATTACHMENT_IMAGE;
		} else {
			$allow_attachment = _ALLOW_ATTACHMENT_NO;
		}

		// allow_video
		if (isset($config['allow_video']['default'][_ON])) {
			$allow_video = _ON;
		} else {
			$allow_video = _OFF;
		}
		// max_size
		$max_size = null;
		$count = 0;
		foreach($config['max_size']['list'] as $max_size_value) {
			if (isset($config['max_size']['default'][$count])) {
				$max_size = $max_size_value;	//$config['max_size']['list_value'][$count];
				break;
			}
			$count++;
		}
		if($max_size == null) {
			// 無制限
			$max_size = 0;
		}

		$system_flag = _OFF;
		if(isset($this->authority) && $this->authority['system_flag'] == _ON) {
			$system_flag = _ON;
			//定義名称があればセット
			switch ($this->role_authority_name) {
				case _AUTH_SYSADMIN_NAME:
					$this->role_authority_name = "_AUTH_SYSADMIN_NAME";
					break;
				case _AUTH_ADMIN_NAME:
					$this->role_authority_name = "_AUTH_ADMIN_NAME";
					break;
				case _AUTH_CHIEF_NAME:
					$this->role_authority_name = "_AUTH_CHIEF_NAME";
					break;
				case _AUTH_MODERATE_NAME:
					$this->role_authority_name = "_AUTH_MODERATE_NAME";
					break;
				case _AUTH_GENERAL_NAME:
					$this->role_authority_name = "_AUTH_GENERAL_NAME";
					break;
				case _AUTH_GUEST_NAME:
					$this->role_authority_name = "_AUTH_GUEST_NAME";
					break;
			}
		}


		$params = array(
			"role_authority_name" => $this->role_authority_name,
			"system_flag" => $system_flag,
			"user_authority_id" => $this->user_authority_id,
			"hierarchy" => $hierarchy,
			"myroom_use_flag" => $myroom_use_flag,
			"public_createroom_flag" => $public_createroom_flag,
			"group_createroom_flag" => $group_createroom_flag,
			"private_createroom_flag" => _OFF,								//現状、off固定
			"allow_htmltag_flag" => $allow_htmltag_flag,
			"allow_layout_flag" => $allow_layout_flag,
			"allow_attachment" => $allow_attachment,
			"allow_video" => $allow_video,
			"max_size" => $max_size
		);
		if(isset($this->authority)) {
			// 更新
			$where_params = array(
									"role_authority_id" => $this->role_authority_id
								);
			$result = $this->authoritiesAction->updAuthority($params, $where_params);
			if ($result === false) {
				return 'error';
			}

			//
			// ログインIDと変更IDが同一の場合、セッションデータを書き換えておく
			//
			if($this->session->getParameter("_role_auth_id") == $this->role_authority_id) {
				$this->session->setParameter("_user_auth_id", $this->user_authority_id);
				$this->session->setParameter("_hierarchy", $hierarchy);

				// 添付関連をセッションに保存
				$this->session->setParameter("_allow_attachment_flag", $allow_attachment);
			    $this->session->setParameter("_allow_htmltag_flag", $allow_htmltag_flag);

			    $this->session->setParameter("_allow_video_flag", $allow_video);

			    // レイアウトできるかどうか(ヘッダー、左右カラムの表示非表示切り替え)
			    // この値がON＋主担であれば切り替え可能
			    $this->session->setParameter("_allow_layout_flag", $allow_layout_flag);

			    // プライベートスペースに対する
				// アップロードの最大容量
			    $this->session->setParameter("_private_max_size", $max_size);
			}
		} else {
			// 新規作成
			$result = $this->authoritiesAction->insAuthority($params);
			if ($result === false) {
				return 'error';
			}
			$this->role_authority_id = $result;
		}

		$authorities_modules_link =& $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($this->role_authority_id, null, null, array($this, "_fetchcallbackAuthorityModuleLink"));

		//
		// AuthorityModuleLinkテーブル削除
		//
		$enroll_modules = $this->session->getParameter(array("authority", $save_role_authority_id, "enroll_modules"));
		if(!is_array($enroll_modules)) $enroll_modules = array();
		foreach($authorities_modules_link as $authority_modules_link) {
			if (!isset($sys_modules['default'][$authority_modules_link['module_id']]) &&
				!isset($site_modules['default'][$authority_modules_link['module_id']]) &&
				!in_array($authority_modules_link['module_id'], $enroll_modules)
			) {
				//削除処理
				$where_params = array(
					"role_authority_id" => $this->role_authority_id,
					"module_id" => $authority_modules_link['module_id']
				);
				$result = $this->authoritiesAction->delAuthorityModuleLink($where_params);
				if ($result === false) {
					return 'error';
				}
			}
		}

		//
		// 管理系モジュール
		//
		$modules =& $this->modulesView->getModules(null, null, null, null, array($this, "_fetchcallbackModules"));
		for($i = 0; $i<=1;$i++) {
			if($i == 0) {
				$buf_data =& $sys_modules;
			} else {
				$buf_data =& $site_modules;
			}
			if(isset($buf_data['default']) && count($buf_data['default']) > 0 ) {

				foreach($buf_data['default'] as $enroll_sysmodule_id => $value) {
					if(!isset($modules[$enroll_sysmodule_id]) || $modules[$enroll_sysmodule_id]['system_flag'] == _OFF) {
						//インストールされていない or 管理系モジュールでない
						continue;
					}

					$pathList = explode("_", $modules[$enroll_sysmodule_id]['action_name']);
					if ($pathList[0]=="user") {
						// 会員管理
						$usermodule_auth = $this->session->getParameter(array("authority", $save_role_authority_id, "detail", "usermodule_auth"));
						if (isset($usermodule_auth)) {
							$authority_id = intval($usermodule_auth);
							if($authority_id == _AUTH_OTHER) {
								$authority_id = intval($this->user_authority_id);
								if($authority_id == _AUTH_ADMIN) {
									$authority_id = _AUTH_CHIEF;
								}
							}
						} else {
							$authority_id = intval($this->user_authority_id);
						}
					} else {
						// それ以外
						$authority_id = intval($this->user_authority_id);
					}

					if(isset($authorities_modules_link[$enroll_sysmodule_id])) {
						// 既に登録済み
						// 更新
						$where_params = array(
											"role_authority_id" => $this->role_authority_id,
											"module_id" =>intval($enroll_sysmodule_id)
										);
						$params = array("authority_id" => $authority_id);
						$result = $this->authoritiesAction->updAuthorityModuleLink($params, $where_params);
						if ($result === false) {
							return 'error';
						}
					} else {
						// 新規
						$params = array(
											"role_authority_id" => $this->role_authority_id,
											"module_id" =>intval($enroll_sysmodule_id),
											"authority_id" => $authority_id
						);
						$result = $this->authoritiesAction->insAuthorityModuleLink($params);
						if ($result === false) {
							return 'error';
						}
					}
				}
			}
		}
		//
		// プライベートスペースの一般系モジュールの使用有無
		//
		if(is_array($enroll_modules) && count($enroll_modules) > 0) {
			foreach($enroll_modules as $enroll_module_id) {
				if(!isset($modules[$enroll_module_id]) || $modules[$enroll_module_id]['system_flag'] == _ON) {
					//インストールされていない or 一般モジュールでない
					continue;
				}

				$authority_id = _AUTH_CHIEF;	//プライベートスペースのため主担固定

				if(isset($authorities_modules_link[$enroll_module_id])) {
					// 既に登録済み
					// 更新
					// 主担にしか更新していないので、実行しなくてもよいが念のため
					$where_params = array(
										"role_authority_id" => $this->role_authority_id,
										"module_id" =>intval($enroll_module_id)
									);
					$params = array("authority_id" => $authority_id);
					$result = $this->authoritiesAction->updAuthorityModuleLink($params, $where_params);
					if ($result === false) {
						return 'error';
					}
				} else {
					// 新規
					$params = array(
										"role_authority_id" => $this->role_authority_id,
										"module_id" =>intval($enroll_module_id),
										"authority_id" => $authority_id
					);
					$result = $this->authoritiesAction->insAuthorityModuleLink($params);
					if ($result === false) {
						return 'error';
					}
				}
			}
		}

		//
		// ベース権限が管理者に変更された場合、すべてのルームに主担として参加させる
		// 会員数が多い場合、処理に時間がかかる可能性あり(会員数＊ルーム数分のInsert(Update)が行われる)
		//
		if($this->authority != null && $this->authority['user_authority_id'] != $this->user_authority_id && $this->user_authority_id == _AUTH_ADMIN) {
			//
			$where_params = array(
								"{users}.active_flag IN ("._USER_ACTIVE_FLAG_OFF.","._USER_ACTIVE_FLAG_ON.","._USER_ACTIVE_FLAG_PENDING.","._USER_ACTIVE_FLAG_MAILED.")" => null,
								"{users}.system_flag IN ("._ON.","._OFF.")" => null,
								"{users}.role_authority_id" => $this->role_authority_id
							);
			$users =& $this->usersView->getUsers($where_params);
			if($users === false) return 'error';

			// ルーム一覧取得
    		$where_params = array(
    							"{pages}.space_type IN ("._SPACE_TYPE_PUBLIC.","._SPACE_TYPE_GROUP.")" => null,
        						'{pages}.private_flag' => _OFF,
    							'{pages}.page_id={pages}.room_id' => null
    						);
    		$pages =& $this->pagesView->getPages($where_params);
    		if($pages === false) return 'error';

    		// 全データ取得
    		$pages_users =& $this->pagesView->getPagesUsers(null, null, null, null, array($this, "_fetchcallbackPagesUsers"));
    		if($pages_users === false) return 'error';

    		foreach($users as $user) {
				foreach($pages as $page) {
					if($page['thread_num'] == 0 && $page['private_flag'] == _OFF &&
						$page['space_type'] == _SPACE_TYPE_GROUP) {
						// グループスペース直下
						continue;
					}
					if($page['thread_num'] == 1) {
						$createroom_flag = _ON;
					} else {
						$createroom_flag = _OFF;
					}
					if(isset($pages_users[$page['page_id']][$user['user_id']])) {
						// Update
						$where_params = array(
										"room_id" => $page['page_id'],
										"user_id" => $user['user_id']
									);
						$params = array(
										"role_authority_id" => _ROLE_AUTH_CHIEF,
										"createroom_flag" => $createroom_flag
									);
						$result = $this->pagesAction->updPageUsersLink($params, $where_params);
						if ($result === false) return 'error';
					} else {
						// Insert
						$params = array(
										"room_id" => $page['page_id'],
										"user_id" => $user['user_id'],
										"role_authority_id" => _ROLE_AUTH_CHIEF,
										"createroom_flag" => $createroom_flag
									);
						$result = $this->pagesAction->insPageUsersLink($params);
						if ($result === false) return 'error';
					}
				}
			}
		}
		//
		// 「プライベートスペースを使用する」が_ONから_OFFに変更されたら、プライベートスペースのdisplay_flag=_PAGES_DISPLAY_FLAG_DISABLEDを立てる
		// 「プライベートスペースを使用する」が_ONから_OFFに変更されたら、プライベートスペースにdisplay_flag=_ONを立てる
		//
		if ($save_role_authority_id > 0) {
			if($this->authority['myroom_use_flag'] == _ON && $myroom_use_flag == _OFF) {
				$set_params = array(
								"display_flag" => _PAGES_DISPLAY_FLAG_DISABLED
							);
			} else if($this->authority['myroom_use_flag'] == _OFF && $myroom_use_flag == _ON) {
				$set_params = array(
								"display_flag" => _ON
							);
			}
			if($this->authority['myroom_use_flag'] != $myroom_use_flag) {
				// 変更あり
				// 変更中の権限である会員のプライベートスペースのルームIDの一覧を取得
				$where_params = array(
					"{authorities}.role_authority_id" => $this->role_authority_id
				);
				$private_users = $this->usersView->getUsers($where_params, null, array($this, "_fetchcallbackUsers"));

				$private_where_params = array(
					"{pages}.space_type IN ("._SPACE_TYPE_PUBLIC.","._SPACE_TYPE_GROUP.") " => null,
					"{pages}.insert_user_id IN ('". implode("','", $private_users). "') " => null,
					"{pages}.private_flag" => _ON,
					"{pages}.default_entry_flag" => _OFF
				);
				$result = $this->pagesAction->updPage($set_params, $private_where_params);
				if($result === false) return 'error';
			}
		}
		$this->session->removeParameter(array("authority", $save_role_authority_id));
		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackModules($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['module_id']] = $row;
		}
		return $ret;
	}
	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @return array configs
	 * @access	private
	 */
	function _fetchcallbackAuthorityModuleLink($result)
	{
		$authorities_modules_link = array();
		while ($row = $result->fetchRow()) {
			if($row["authority_id"] != null) {
				$authorities_modules_link[$row["module_id"]] = $row;
			}
		}
		return $authorities_modules_link;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackPagesUsers($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['page_id']][$row['user_id']] = $row;
		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackUsers($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[] = $row['user_id'];
		}
		return $ret;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackPages($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[] = $row['page_id'];
		}
		return $ret;
	}
}
?>
