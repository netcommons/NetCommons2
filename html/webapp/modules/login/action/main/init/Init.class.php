<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ログインモジュール-loginモジュール:ログインボタン押下時
 *
 * @package     NetCommons Action
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Login_Action_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $login_id = null;
	var $password = null;
	var $rememberme = null;

	// バリデートによりセット
	var $handle = null;
	var $role_authority_id = null;
	var $user_id = null;
	var $timezone_offset = null;
	var $last_login_time = null;
	var $system_flag = null;

	var $role_authority_name = null;
	var $user_authority_id = null;
	var $allow_attachment = null;
	var $allow_video = null;
	var $allow_htmltag_flag = null;
	var $allow_layout_flag = null;
	var $max_size = null;
	var $lang_dirname = null;

	// コンポーネントを受け取るため
	var $usersAction = null;
	var $session = null;
	var $configView = null;
	var $request = null;
	var $db = null;
	var $getdata = null;
	var $pagesView = null;
	var $commonMain = null;

	var $redirect_url = null;

    /**
     * ログインモジュール-loginモジュール:ログインボタン押下時
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	$this->session->setParameter("_user_id",$this->user_id);
    	$this->session->setParameter("_login_id",$this->login_id);
		$this->session->setParameter("_site_id",0);
		$this->session->setParameter("_handle",$this->handle);
		$this->session->setParameter("_role_auth_id",$this->role_authority_id);
		$this->session->setParameter("_timezone_offset",$this->timezone_offset);

		//role_authority_idよりデフォルト権限をセッションにセット
		$this->session->setParameter("_role_authority_name",$this->role_authority_name);
		$this->session->setParameter("_user_auth_id",$this->user_authority_id);

		if(!empty($this->lang_dirname)) {
			$this->session->setParameter("_lang",$this->lang_dirname);
		}

		// 添付関連をセッションに保存
		$this->session->setParameter("_allow_attachment_flag", $this->allow_attachment);
	    $this->session->setParameter("_allow_htmltag_flag", $this->allow_htmltag_flag);

	    $this->session->setParameter("_allow_video_flag", $this->allow_video);

	    // レイアウトできるかどうか(ヘッダー、左右カラムの表示非表示切り替え)
	    // この値がON＋主担であれば切り替え可能
	    $this->session->setParameter("_allow_layout_flag", $this->allow_layout_flag);

	    // プライベートスペースに対する
		// アップロードの最大容量
	    $this->session->setParameter("_private_max_size", $this->max_size);

		//最終ログイン日時、前回ログイン日時更新
	    if(!empty($this->user_id)) {
			$params = array(
				"last_login_time" => timezone_date(),
				"previous_login_time" => $this->last_login_time
			);
			$where_params = array("user_id" => $this->user_id);
			$result = $this->usersAction->updUsers($params, $where_params, false);
			if($result === false) return 'db_error';
	    }
		if ($mobile_flag == _ON) {
		} else {
			$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
			$path = ini_get("session.cookie_path");
			$domain = ini_get("session.cookie_domain");
			$secure = ini_get("session.cookie_secure");

			$lifetime = time() + _AUTOLOGIN_LIFETIME; 	// 1 week default
			$autologin_login_cookie_name = $config['autologin_login_cookie_name']['conf_value'];
			$autologin_pass_cookie_name = $config['autologin_pass_cookie_name']['conf_value'];
			if(($config['autologin_use']['conf_value'] == _AUTOLOGIN_LOGIN_ID || $config['autologin_use']['conf_value'] == _AUTOLOGIN_OK) &&
				$autologin_login_cookie_name != "") {
				setcookie($autologin_login_cookie_name, $this->login_id, $lifetime, $path, $domain, $secure);
			}
			if($this->rememberme == _ON && $config['autologin_use']['conf_value'] == _AUTOLOGIN_OK &&
				$autologin_login_cookie_name != "" && $autologin_pass_cookie_name != "") {
				setcookie($autologin_pass_cookie_name, md5($this->password), $lifetime, $path, $domain, $secure);
				setcookie($autologin_login_cookie_name, $this->login_id, $lifetime, $path, $domain, $secure);
			}
		}

		//
		// 固定リンク用
		//
		$result = $this->db->selectExecute("pages", array("space_type IN ("._SPACE_TYPE_PUBLIC.","._SPACE_TYPE_GROUP.") " => null,
									"private_flag" => _ON, "thread_num" => 0, "insert_user_id" => $this->user_id), array("default_entry_flag" => "DESC"), 2, 0);

		$_permalink_flag = $this->session->getParameter("_permalink_flag");

		if(isset($result[0])) {
			$this->session->setParameter("_self_myroom_page", $result[0]);
		}
		//if(isset($result[1])) {
		//	$this->session->setParameter("_self_my_page", $result[1]);
		//}
		$_redirect_url = $this->request->getParameter("_redirect_url");
		if((!isset($_redirect_url) || $_redirect_url == "")) {
			//  && isset($result[0])

			//
			// リダイレクト先がないならば、デフォルト表示するページIDを取得
			//
			$active_page = $this->_getDefaultPage();
			if($active_page['permalink'] != "") {
				$active_page['permalink'] .= '/';
			}
			//$this->redirect_url = BASE_URL.'/'.$active_page['permalink'];
			if($_permalink_flag) {
				$this->request->setParameter("_redirect_url", $active_page['permalink']);
			} else {
				$this->request->setParameter("_redirect_url", "?".ACTION_KEY."=".$active_page['action_name']."&page_id=".$active_page['page_id'].$active_page['parameters']);
			}
		}

    	return 'success';
    }
    /**
     * デフォルトのpage_idを取得
     *
     * @return array page
     * @access  public
     */
    function _getDefaultPage() {
    	$page = array();
    	$page_id_arr = array();
    	$_user_id = $this->session->getParameter("_user_id");
		$config = $this->getdata->getParameter("config");
		$first_choice_startpage = intval($config[_GENERAL_CONF_CATID]['first_choice_startpage']['conf_value']);
  		$second_choice_startpage = intval($config[_GENERAL_CONF_CATID]['second_choice_startpage']['conf_value']);
  		$third_choice_startpage = intval($config[_GENERAL_CONF_CATID]['third_choice_startpage']['conf_value']);
		$default_private_space = 0;

  		if($first_choice_startpage == 0) {
  			//指定なし
  			;
  		} elseif($first_choice_startpage != -1) {
  			$page_id_arr[] = $first_choice_startpage;
  		} else {
  			//プライベートスペース
  			$default_private_space = 1;
  		}
  		if($second_choice_startpage == 0) {
  			//指定なし
  			;
  		} elseif($second_choice_startpage != -1) {
  			$page_id_arr[] = $second_choice_startpage;
  		} else {
  			//プライベートスペース
  			if($default_private_space == 0) $default_private_space = 2;
  		}
  		if($third_choice_startpage == 0) {
  			//指定なし
  			;
  		} elseif($third_choice_startpage != -1) {
  			$page_id_arr[] = $third_choice_startpage;
  		} else {
  			//プライベートスペース
  			if($default_private_space == 0) $default_private_space = 3;
  		}

  		$buf_pages_obj =& $this->pagesView->getPageById($page_id_arr);
  		$buf_page_obj = "";
		$show_page_id = 0;
		$set_default_private_space = 4;
		foreach($buf_pages_obj as $page_obj) {
			if(($page_obj['space_type'] == _SPACE_TYPE_PUBLIC && $page_obj['display_flag'] == _ON) ||
					($page_obj['space_type'] == _SPACE_TYPE_GROUP && $page_obj['default_entry_flag'] == _ON && $page_obj['display_flag'] == _ON && $_user_id != "0" &&
					(!isset($page_obj['role_authority_id']) || $page_obj['role_authority_id'] != _ROLE_AUTH_OTHER)) ||
					($page_obj['space_type'] == _SPACE_TYPE_GROUP && $page_obj['display_flag'] == _ON && $_user_id != "0") && (isset($page_obj['authority_id']))) {
				//閲覧できるpage_id有
				if($first_choice_startpage == $page_obj['page_id']) {
					$show_page_id = $page_obj['page_id'];
					$buf_pages_obj[$show_page_id] = $page_obj;
					$set_default_private_space = 1;
				} else if($second_choice_startpage == $page_obj['page_id'] && $set_default_private_space > 2) {
					$show_page_id = $page_obj['page_id'];
					$buf_pages_obj[$show_page_id] = $page_obj;
					$set_default_private_space = 2;
				} else if($third_choice_startpage == $page_obj['page_id'] && $set_default_private_space > 3) {
					$show_page_id = $page_obj['page_id'];
					$buf_pages_obj[$show_page_id] = $page_obj;
					$set_default_private_space = 3;
				}
			}
		}

		//優先順位がプライベートスペースのほうが高い場合
		if(($set_default_private_space == _OFF || $set_default_private_space > $default_private_space) && $default_private_space != 0 && $_user_id != "0") {
			//マイページからpage_id取得
			$buf_page_obj_private =& $this->pagesView->getPrivateSpaceByUserId($_user_id, 1);
			if($buf_page_obj_private) {
				$show_page_id = $buf_page_obj_private[0]['page_id'];
				$buf_pages_obj[$show_page_id] = $buf_page_obj_private[0];
			}
		}
		if($show_page_id != 0) {
			$page_id = $show_page_id;
			$page = $buf_pages_obj[$page_id];
		}
		if(isset($page_id) && $page_id != 0){
			if(isset($buf_pages_obj[$page_id]) && $buf_pages_obj[$page_id]['node_flag'] == _ON && $buf_pages_obj[$page_id]['action_name'] == "") {
				//指定したpage_idがnodeであるならば
				//nodeの子供のうち最も近いページIDを取得
				if($buf_pages_obj[$page_id]['root_id'] == 0) {
					$root_id = $buf_pages_obj[$page_id]['page_id'];
				} else {
					$root_id = $buf_pages_obj[$page_id]['root_id'];
				}
				$where_params = array(
					"action_name!=''"=>null,
					"display_sequence!=0"=>null,
					"display_flag"=>_ON,
					"root_id"=>$root_id,
					"display_position"=>$buf_pages_obj[$page_id]['display_position'],
					"thread_num>".$buf_pages_obj[$page_id]['thread_num']=>null
				);
				$order_params =array(
										"{pages}.thread_num" => "ASC",
										"{pages}.display_sequence" => "ASC"
									);

				$buf_pages_obj_child =& $this->pagesView->getShowPagesList($where_params, $order_params, 1, 0, array($this->pagesView, 'fetchcallback'));
				if($buf_pages_obj_child && isset($buf_pages_obj_child[0])) {
					//親ノードの子供
					$page_id = $buf_pages_obj_child[0]['page_id'];
					$buf_pages_obj[$page_id] = $buf_pages_obj_child[0];
					$page = $buf_pages_obj[$page_id];
				}
			}
		}

		if(!isset($page_id)) {
			//デフォルトページがみつからない
			//見れるページIDを取得
			$where_params = array(
				"action_name!=''"=>null,
				"display_flag"=>_ON,
				"display_sequence!"=>0
			);
			$order_params =array(
									"{pages}.thread_num" => "ASC",
									"{pages}.display_sequence" => "ASC"
									);
			$buf_pages_obj_sub =& $this->pagesView->getShowPagesList($where_params, $order_params, 1, 0, array($this->pagesView, 'fetchcallback'));

			//少なくともバブリックページは１ページはあるとして処理
			$page_id = $buf_pages_obj_sub[0]['page_id'];
			$buf_pages_obj[$page_id] = $buf_pages_obj_sub[0];
			$page = $buf_pages_obj[$page_id];
		}
		return $page;
    }
}
?>
