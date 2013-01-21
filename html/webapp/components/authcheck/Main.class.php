<?php
/**
 * 権限チェッククラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authcheck_Main {
	var $_className = "Authcheck_Main";

	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $session = null;
	var $request = null;
	var $getdata = null;
	var $authoritiesView = null;
	var $pagesView = null;
	var $usersView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Authcheck_Main() {
	}

	/**
	 * 会員の権限を取得
	 * @param int user_id (default:login_user)
	 * @param int page_id (default:request-page_id)			一般系モジュールの場合、使用
	 * @param int module_id (default:request-module_id)		管理系モジュールの場合、使用
	 * @return	int auth_id (page_idを指定していない場合、array[page_id][key_name]) エラーの場合、0
	 * @access	public
	 **/
	function getPageAuthId($user_id = null, $page_id = null, $module_id = null) {
		$user_id = ($user_id === null) ? $this->session->getParameter("_user_id") : $user_id;
		$system_flag = $this->session->getParameter("_system_flag");
		if($page_id != null && $module_id == null) {
			// 管理系からのリクエストであっても一般の権限取得とみなす
			$system_flag = _OFF;
		}
		if($system_flag == _ON) {
			//--------------------------------------------------------------
			// 管理系モジュール
			//--------------------------------------------------------------
			if($user_id === "0") return _AUTH_OTHER;
			if($user_id == $this->session->getParameter("_user_id")) {
				$role_auth_id = $this->session->getParameter("_role_auth_id");
			} else {
				$users =& $this->getdata->getParameter("users");
				if(!isset($users[$user_id])) {
					$users[$user_id] = $this->usersView->getUserById($user_id);
					if($users[$user_id] === false || !isset($users[$user_id]['user_id'])) return _AUTH_OTHER;
					$this->getdata->setParameter("users", $users);
				}
				$role_auth_id = $users[$user_id]['role_authority_id'];
			}
			$module_id = ($module_id === null) ? $this->request->getParameter("module_id") : $module_id;
			$auth_id = $this->authoritiesView->getSystemAuthorityIdById($role_auth_id, $module_id);
			if($auth_id == false) {
				//使用不可モジュール
				//エラー
				return _AUTH_OTHER;
			}
			return $auth_id;
		} else {
			//--------------------------------------------------------------
			// 一般系モジュール
			//--------------------------------------------------------------
			$page_id = ($page_id === null) ? $this->request->getParameter("page_id") : $page_id;
			if($page_id == 0) return _AUTH_GUEST;

			if($this->session->getParameter("_user_id") == $user_id) {
				$pages = $this->getdata->getParameter("pages");
				if(!isset($pages[$page_id])) {
					$pages[$page_id] = $this->pagesView->getPageById($page_id, $user_id);
					if($pages[$page_id] === false || !isset($pages[$page_id]['page_id'])) return _AUTH_OTHER;
					$this->getdata->setParameter("pages", $pages);
				}
			} else {
				// ログインIDではない場合、毎回、取得
				$pages[$page_id] = $this->pagesView->getPageById($page_id, $user_id);
				if($pages[$page_id] === false || !isset($pages[$page_id]['page_id'])) return _AUTH_OTHER;
			}

			$space_type = $pages[$page_id]['space_type'];
			$default_entry_flag = $pages[$page_id]['default_entry_flag'];
			$authority_id = $pages[$page_id]['authority_id'];
			$role_authority_id = $pages[$page_id]['role_authority_id'];
			$private_flag = $pages[$page_id]['private_flag'];
			$display_flag = $pages[$page_id]['display_flag'];
			if($user_id === "0") {
				// ログインしていない
				if($space_type == _SPACE_TYPE_PUBLIC) {
					// パブリックスペース
					$ret = _AUTH_GUEST;
				} else if($private_flag == _ON &&
					($this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_PUBLIC ||
					 ($this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC && $default_entry_flag == _ON))) {
					// プライベートスペース-パブリックスペース化
					$ret = _AUTH_GUEST;
				} else {
					$ret = _AUTH_OTHER;
				}
			} else if($role_authority_id === _ROLE_AUTH_OTHER) {
				// 不参加
				$ret = _AUTH_OTHER;
			} else if($default_entry_flag == _ON && $role_authority_id === null) {
				//if($page_id == $this->session->getParameter("_main_page_id")) {
				//	$ret = $this->session->getParameter("_default_entry_auth");
				//} else {
					if($private_flag == _ON) {
						$ret = $this->session->getParameter("_default_entry_auth_private");
					} else if($space_type == _SPACE_TYPE_PUBLIC) {
						$ret = $this->session->getParameter("_default_entry_auth_public");
					} else {
						$ret = $this->session->getParameter("_default_entry_auth_group");
					}
				//}
			} else if($private_flag == _ON &&
						(($this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_GROUP ||
						$this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_PUBLIC) ||
						(($this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_GROUP ||
						$this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_PUBLIC) && $default_entry_flag == _ON))) {
				// プライベートスペース-ログイン会員ならば表示可
				if($authority_id !== null) {
					$ret = $authority_id;
				} else {
					$ret = $this->session->getParameter("_default_entry_auth_private");
				}
			} else if($authority_id === null) {
				$ret = _AUTH_OTHER;
			} else {
				$ret = $authority_id;
			}

			if($display_flag == _OFF && $ret < _AUTH_CHIEF) {
				// 準備中
				$ret = _AUTH_OTHER;
			} else if($display_flag == _PAGES_DISPLAY_FLAG_DISABLED) {
				// 使用不可
				$ret = _AUTH_OTHER;
			}
			if($this->session->getParameter("_user_auth_id") == _AUTH_GUEST && $ret == _AUTH_GENERAL) {
				$ret = _AUTH_GUEST;
			}
		}
		return $ret;
	}


	/**
	 * 会員の権限の階層（上下関係：hierarchy）を取得
	 * @param int user_id (default:login_user)
	 * @param int page_id (default:request-page_id)			一般系モジュールの場合、使用
	 * @param int module_id (default:request-module_id)		管理系モジュールの場合、使用
	 * @return	int auth_id (page_idを指定していない場合、array[page_id][key_name]) エラーの場合、0
	 * @access	public
	 **/
	function getPageHierarchy($user_id = null, $page_id = null, $module_id = null) {
		$user_id = ($user_id === null) ? $this->session->getParameter("_user_id") : $user_id;
		$system_flag = $this->session->getParameter("_system_flag");
		if($page_id != null && $module_id == null) {
			// 管理系からのリクエストであっても一般の権限取得とみなす
			$system_flag = _OFF;
		}
		if($system_flag == _ON) {
			//--------------------------------------------------------------
			// 管理系モジュール
			// 管理系の場合、上下関係はベース権限のみ
			// 必ず0を返却
			//--------------------------------------------------------------
			$ret = _HIERARCHY_OTHER;
		} else {
			//--------------------------------------------------------------
			// 一般系モジュール
			//--------------------------------------------------------------
			$page_id = ($page_id === null) ? $this->request->getParameter("page_id") : $page_id;
			if($page_id == 0) return _HIERARCHY_OTHER;

			if($this->session->getParameter("_user_id") == $user_id) {
				$pages = $this->getdata->getParameter("pages");
				if(!isset($pages[$page_id])) {
					$pages[$page_id] = $this->pagesView->getPageById($page_id, $user_id);
					if($pages[$page_id] === false || !isset($pages[$page_id]['page_id'])) return _HIERARCHY_OTHER;
					$this->getdata->setParameter("pages", $pages);
				}
			} else {
				// ログインIDではない場合、毎回、取得
				$pages[$page_id] = $this->pagesView->getPageById($page_id, $user_id);
				if($pages[$page_id] === false || !isset($pages[$page_id]['page_id'])) return _HIERARCHY_OTHER;
				if($pages[$page_id]['hierarchy'] == _HIERARCHY_OTHER) {
					$container =& DIContainerFactory::getContainer();
					$usersView =& $container->getComponent("usersView");
					$buf_users = $usersView->getUserById($user_id);
					if(isset($buf_users['user_authority_id'])) {
						if($buf_users['user_authority_id'] >= _AUTH_MODERATE) {
							$pages[$page_id]['hierarchy'] = _HIERARCHY_CHIEF;
						} else if($buf_users['user_authority_id'] == _AUTH_GENERAL) {
							$pages[$page_id]['hierarchy'] = _HIERARCHY_GENERAL;
						}
					} else {
						$pages[$page_id]['hierarchy'] = _HIERARCHY_CHIEF;
					}
				}
			}
			$space_type = $pages[$page_id]['space_type'];
			$default_entry_flag = $pages[$page_id]['default_entry_flag'];
			$hierarchy = $pages[$page_id]['hierarchy'];
			$private_flag = $pages[$page_id]['private_flag'];
			$display_flag = $pages[$page_id]['display_flag'];

			if($user_id === "0") {
				// ログインしていない
				if($space_type == _SPACE_TYPE_PUBLIC) {
					// パブリックスペース
					$ret = _HIERARCHY_GUEST;
				} else if($private_flag == _ON && $this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_PUBLIC) {
					// プライベートスペース-パブリックスペース化
					$ret = _HIERARCHY_GUEST;
				} else {
					$ret = _HIERARCHY_OTHER;
				}
			} else if($default_entry_flag == _ON && $hierarchy === null) {
				//if($page_id == $this->session->getParameter("_main_page_id")) {
				//	$ret = $this->session->getParameter("_default_entry_hierarchy");
				//} else {
					if($private_flag == _ON) {
						$ret = $this->session->getParameter("_default_entry_hierarchy_private");
					} else if($space_type == _SPACE_TYPE_PUBLIC) {
						$ret = $this->session->getParameter("_default_entry_hierarchy_public");
					} else {
						$ret = $this->session->getParameter("_default_entry_hierarchy_group");
					}
				//}
			} else if($private_flag == _ON && $this->session->getParameter("_open_private_space") == _OPEN_PRIVATE_SPACE_GROUP) {
				// プライベートスペース-ログイン会員ならば表示可
				if($hierarchy !== null) {
					$ret = $hierarchy;
				} else {
					$ret = $this->session->getParameter("_default_entry_hierarchy_private");
				}
			} else if($hierarchy === null) {
				$ret = _HIERARCHY_OTHER;
			} else {
				$ret = $hierarchy;
			}
			if($display_flag == _OFF && $ret < _AUTH_CHIEF) {
				// 準備中
				$ret = _HIERARCHY_OTHER;
			} else if($display_flag == _PAGES_DISPLAY_FLAG_DISABLED) {
				// 使用不可
				$ret = _HIERARCHY_OTHER;
			}
		}
		return $ret;
	}


	/**
	 * 会員のルーム作成権限を取得
	 * @param int user_id (default:login_user)
	 * @param int page_id (default:request-page_id)
	 * @return	int createroom_flag 1 or 0 (page_idを指定していない場合、array[page_id][key_name]) エラーの場合、0
	 * @access	public
	 **/
	function getPageCreateroomFlag($user_id = null, $page_id = null) {
		$user_id = ($user_id === null) ? $this->session->getParameter("_user_id") : $user_id;
		$page_id = ($user_id === null) ? $this->request->getParameter("page_id") : $page_id;

		if($page_id == 0) return _OFF;
		if($this->session->getParameter("_user_id") == $user_id) {
			$pages =& $this->getdata->getParameter("pages");
			if(!isset($pages[$page_id])) {
				$pages[$page_id] = $this->pagesView->getPageById($page_id, $user_id);
				if($pages[$page_id] === false || !isset($pages[$page_id]['page_id'])) return _OFF;
				$this->getdata->setParameter("pages", $pages);
			}
		} else {
			// ログインIDではない場合、毎回、取得
			$pages[$page_id] = $this->pagesView->getPageById($page_id, $user_id);
			if($pages[$page_id] === false || !isset($pages[$page_id]['page_id'])) return _OFF;
		}

		if($pages[$page_id]['thread_num'] == 0) {
			$private_flag = $pages[$page_id]['private_flag'];
			$space_type = $pages[$page_id]['space_type'];
			$users =& $this->getdata->getParameter("users");
			if(!isset($users[$user_id])) {
				$users[$user_id] = $this->usersView->getUserById($user_id);
				if($users[$user_id] === false || !isset($users[$user_id]['user_id'])) return _OFF;
			}
			if($private_flag == _ON) {
				//private_createroom_flag
				$ret = _OFF;
			} else if($space_type == _SPACE_TYPE_PUBLIC) {
				$ret = $users[$user_id]['public_createroom_flag'];
			} else {
				$ret = $users[$user_id]['group_createroom_flag'];
			}
		} else if($pages[$page_id]['thread_num'] == 1) {
			$ret = $pages[$page_id]['createroom_flag'];
		} else {
			// 深さが１より大きい場合
			$ret = _OFF;
		}
		return $ret;
	}

	/**
	 * 権限チェックを行う
	 * @param action_name
	 * @param page_id
	 * @param block_id
	 * @return	boolean
	 * @access	public
	 **/
	function AuthCheck($action_name, $page_id, $block_id) {
		if($action_name != "") {
	    	$pathList = explode("_", $action_name);
		} else {
			// エラー
			return false;
		}

		//TODO:他サイト間通信で使用予定。現在、未使用。
    	//$_redirect_url =  $this->request->getParameter("_redirect_url");
    	//$_req_sig = $this->request->getParameter("_sig");
	    //$_req_ts =  $this->request->getParameter("_ts");
	    //$_req_user_id =  $this->request->getParameter("_user_id");
	    //$_req_auth_id =  $this->request->getParameter("_auth_id");
	    //$_req_token =  $this->request->getParameter("_token");

		//システム系の画面かいなか
		$system_flag = $this->session->getParameter("_system_flag");

		//
		//携帯チェック
		//
		$mobile_flag = $this->session->getParameter("_mobile_flag");
		$isMobileAction = ($pathList[0] == 'common'
							&& $pathList[1] == 'mobile');
		$isMobileAction = ($isMobileAction
							|| $pathList[2] == 'mobile');
		if ($isMobileAction
			&& empty($mobile_flag)) {
			return false;
		}

		$isSystemException = ($pathList[0] == 'userinf');
		if (!$isSystemException
			&& $mobile_flag == _ON
			&& $system_flag == _ON) {
			return false;
		}

    	//
    	// active_flagチェック
    	//
    	$user_id = $this->session->getParameter("_user_id");
    	if($user_id !== "0") {
    		$users =& $this->getdata->getParameter("users");
			if(!isset($users[$user_id])) {
				$users[$user_id] = $this->usersView->getUserById($user_id);
				if($users[$user_id] === false || !isset($users[$user_id]['user_id'])) {
					// 強制ログアウト
					$this->session->close();
					return false;
				}
				$this->getdata->setParameter("users", $users);
			}
			if($users[$user_id]['active_flag'] != _USER_ACTIVE_FLAG_ON) {
				// 強制ログアウト
				$this->session->close();
				return false;
			}
			if($users[$user_id]['system_flag'] == _ON) {
				$this->session->setParameter("_system_user_id",$user_id);
			}
    	}
    	$_system_user_id = $this->session->getParameter("_system_user_id");
    	if(!isset($_system_user_id)) {
    		$where_params = array("{users}.active_flag"=> _USER_ACTIVE_FLAG_ON, "{users}.system_flag"=> _ON);
			$sys_users = $this->usersView->getUsers($where_params);
			if($sys_users === false || !isset($sys_users[0]['user_id'])) return false;
			if(isset($sys_users[0]['user_id'])) {
				$this->session->setParameter("_system_user_id",$sys_users[0]['user_id']);
			}
    	}

    	if($action_name == "control_view_main") return true;

		// リクエストパラメータにblock_idがなければ、
		// ショートカットとして評価
		$shortcut_flag = _ON;
		if($block_id != 0) {
    		$blocks = $this->getdata->getParameter("blocks");
    		if(isset($blocks[$block_id]['action_name'])) {
	    		$pathListBlockobj = explode("_", $blocks[$block_id]['action_name']);
	    		//アクションとブロックオブジェクトのアクションが異なる
	    		//但し、ダイアログ、ページ表示アクションの場合はチェックしない
	    		if($pathList[0] != $pathListBlockobj[0] && $pathList[0] != "comp" && $pathList[0] != "dialog" && $pathList[0] != "pages") {
	    			//エラー
	    			return false;
	    		}
	    		// block_idがあれば、block_idからpage_idをセット（blocksテーブル優先）
	    		$page_id = $blocks[$block_id]['page_id'];

	    		$shortcut_flag = $blocks[$block_id]['shortcut_flag'];
    		}
    	}

    	if($block_id == 0 && $pathList[0] == "login") {
    		//ログイン
    		$auth_id = _AUTH_GUEST;
    		$hierarchy = 0;
    	} else {
			if($system_flag == _ON) {
				$auth_id = $this->getPageAuthId($user_id);
			} else {
				$auth_id = $this->getPageAuthId($user_id, $page_id);
			}
			$hierarchy = $this->getPageHierarchy($user_id, $page_id);
    	}
		$this->session->setParameter("_auth_id",$auth_id);
		$this->session->setParameter("_hierarchy",$hierarchy);

		$pages = $this->getdata->getParameter("pages");
		$room_id = isset($pages[$page_id]['room_id']) ? $pages[$page_id]['room_id'] : 0;
		$space_type = isset($pages[$page_id]['space_type']) ? $pages[$page_id]['space_type'] : _SPACE_TYPE_GROUP;

    	//TODO:現状、未仕様
    	//if($_redirect_url && $_req_sig && $_req_user_id && $_req_auth_id && $_req_ts && $_req_token) {
    		//
    		// 他サーバショートカット
    		//
    		//exit;
    	//}

		//
		// 自サイト
		//
		if($auth_id ==_AUTH_OTHER) {
			return false;
		}
		//if($auth_id ==_AUTH_OTHER && $user_id === "0") {
			// 管理系ならば、コントロールパネルに遷移
			// それ以外、pages_view_mainに遷移
			/*
			if($system_flag) {
				$redirect_url = "?_sub_action=control_view_main";
				$current_page_id = $this->request->getParameter("current_page_id");
				if($current_page_id != null && $current_page_id != 0) {
					$redirect_url .= "@current_page_id=". $current_page_id;
				}
			} else {
				$redirect_url = "?_sub_action=" . DEFAULT_ACTION;
				$page_id = $this->request->getParameter("page_id");
				if($page_id != null && $page_id != 0) {
					$redirect_url .= "@page_id=". $page_id;
				}
			}
			*/
			//ログイン画面表示
			//print "<script type=\"text/javascript\">
			//		location.href = '".BASE_URL.INDEX_FILE_NAME."?action=login_view_main_init&error_mes="._ON."&_redirect_url=".$redirect_url."';
			//		</script>";
			//ログインしていない
			//$url = htmlspecialchars(str_replace("?action=","?_sub_action=",str_replace("&","@",BASE_URL.INDEX_FILE_NAME.$this->request->getStrParameters(false))), ENT_QUOTES);
			//ログイン画面表示
			//print "<script type=\"text/javascript\">
			//		location.href = '".BASE_URL.INDEX_FILE_NAME."?action=login_view_main_init&error_mes="._ON."&_redirect_url=".str_replace("?action=","?_sub_action=",str_replace("&","@",$url))."';
			//		</script>";
			//エラー
			//return false;
		//} else if($auth_id ==_AUTH_OTHER) {
		//	return false;
		//}

		//
        // room_idの値をActionに移す
        //
        //$buf_room_id =  $this->request->getParameter("room_id");
        //if(!isset($buf_room_id)) {
        	$this->request->setParameter("room_id", $room_id);
        //}
        if($system_flag == _OFF) {
	        //------------------------------------------------------------------------
	    	// XXXX_XXXX_Edit_XXXX_･･･のアクションは、権限が主担以上の場合だけ許す
	    	// block_idがパラメータにあり、ショートカットのブロックならばXXXX_XXXX_Edit_Init_･･･のアクションは許さない
	    	// 基本的にmaple.iniでValidateDefのauthcheck,moduleShortcutを行わない仕様とする
	    	//------------------------------------------------------------------------
	    	if(is_array($pathList) && isset($pathList[2])) {
	    		if($pathList[0] == "menu") {
	    			if($user_id === "0" && $pathList[2] == "edit") {
	    				return false;
	    			}
	    		} else {
		    		if(($pathList[2] == "edit") && $auth_id < _AUTH_CHIEF) {
		    			return false;
		    		}

		    		//XXX_View(Action)_XXXX_Init_･･･ならばショートカットは許さない
		    		if($pathList[2] == "edit" && isset($pathList[3]) &&
		    			$pathList[3] == "init" && $shortcut_flag == _ON) {
		    			return false;
		    		}
	    		}
	    	}
        }
		//
   		//レイアウトモード
   		//
   		$_layoutmode =  $this->request->getParameter("_layoutmode");	//on or off
    	$_layoutmode_onetime =  $this->request->getParameter("_layoutmode_onetime");
   		if($auth_id >= _AUTH_CHIEF) {
	   		if(($_layoutmode == "on" || $_layoutmode == "off")) {
				$this->session->setParameter("_layoutmode",$_layoutmode);
			}
			//$buf_layoutmode = $this->session->getParameter("_layoutmode");
			//if(($buf_layoutmode != "on" && $buf_layoutmode != "off")) {
			//	$this->session->setParameter("_layoutmode","off");
			//}
			if(isset($_layoutmode_onetime)) {
				if(($_layoutmode_onetime != "on" && $_layoutmode_onetime != "off")) {
					$this->request->setParameter("_layoutmode_onetime","off");
				}
			}
   		} else {
   			$this->session->setParameter("_layoutmode","off");
   			if(isset($_layoutmode_onetime)) {
   				$this->request->setParameter("_layoutmode_onetime","off");
   			}
   		}

		//
   		//ショートカットフラグ
   		//
		$this->session->setParameter("_shortcut_flag",$shortcut_flag);

		//
		// センターカラムに拡大表示しているかどうか
		//
		$_show_main_flag = $this->request->getParameter("_show_main_flag");
		if($_show_main_flag == _ON) {
			$this->session->setParameter("_show_main_flag",_ON);
		} else {
			$this->session->setParameter("_show_main_flag",_OFF);
		}
		return true;
	}
}
?>
