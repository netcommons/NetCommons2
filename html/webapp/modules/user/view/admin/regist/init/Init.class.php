<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員管理>>会員新規登録表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Regist_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    var $enroll_room = null;
    var $not_enroll_room = null;
    
	var $room_authority = null;
	var $createroom_flag = null;
	var $op = null;
	
    // 使用コンポーネントを受け取るため
    var $usersView = null;
    var $timezoneMain = null;
    var $session = null;
    var $authoritiesView = null;
    
    // 値をセットするため
    var $items = null;
    var $edit_flag = null;
    var $langTimeZone = null;
    var $lang = null;
    var $dialog_name = "";
    var $user = "";
    
    //
    // サイト運営モジュールのみ実行可能な管理者は、システムコントロールモジュールを実行可能な管理者に変更できない
    //
    var $not_chg_role_id_arr = array();
    
    var $session_params = null;
    var $session_public_params = null;
    var $session_reception_params = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	// 管理者が見れる項目を表示する
    	//$user_id = $this->session->getParameter("_user_id");
    	$user_auth_id = $this->session->getParameter("_user_auth_id");
    	$role_auth_id = $this->session->getParameter("_role_auth_id");
    	if($user_auth_id == _AUTH_ADMIN) {
    		//
    		// 管理者ならば、システムコントロールモジュール、サイト運営モジュールの選択の有無で上下を判断
    		//
    		$func = array($this, "_getSysModulesFetchcallback");
    		$authority = $this->authoritiesView->getAuthorityById($role_auth_id);
    		$system_user_flag = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($role_auth_id, array("system_flag"=>_ON), null, $func);
			if($system_user_flag === null) {
				$where_params = array("user_authority_id" => _AUTH_ADMIN, "role_authority_id !=".$role_auth_id => null);
				$authorities = $this->authoritiesView->getAuthorities($where_params);
				foreach($authorities as $buf_authority) {
					$buf_system_user_flag = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($buf_authority['role_authority_id'], array("system_flag"=>_ON), null, $func);
					if($buf_system_user_flag === true) {
						$this->not_chg_role_id_arr[$buf_authority['role_authority_id']] = true;
					}
				}
			}
    	}
    	
    	if($this->user_id == null || $this->user_id == "0") {
    		$this->user_id = "0";
    		$this->items =& $this->usersView->getShowItems("0", _AUTH_ADMIN, null);
    		if($this->items === false) return 'error';
    		// デフォルトのタイムゾーン,言語取得
    		$this->edit_flag = _OFF;
    		$this->lang = $this->session->getParameter("_lang");
    	} else {
    		// 編集
    		$this->items =& $this->usersView->getShowItems($this->user_id, _AUTH_ADMIN, null);
    		if($this->items === false) return 'error';
    		$this->user =& $this->usersView->getUserById($this->user_id, array($this, "_getUsersFetchcallback"));
    		if($this->user === false) return 'error';

    		// 会員登録基本情報をセッション保存
			//if($this->items != null) {
			//	//初期化し設定
			//	$this->session->removeParameter(array("user", "regist", $this->user_id));
			//	foreach($this->items as $col_num => $items) {
			//		foreach($items as $row_num => $item) {
			//			if($item["type"] == USER_TYPE_CHECKBOX || $item["type"] == USER_TYPE_RADIO ||
			//				$item["type"] == USER_TYPE_SELECT) {
			//				//$this->session->setParameter(array("user", "regist", $this->user_id, $item['item_id'], $key), $value);
			//			} else {
			//				//$this->session->setParameter(array("user", "regist", $this->user_id, $item['item_id']), $item['content']);
			//			}
			//		}	
			//	}
			//	
			//}
    		$this->dialog_name = USER_VIEW_ADMIN_EDIT_INIT;
    		$this->edit_flag = _ON;
    		$this->lang = $this->user['lang_dirname'];
    	}
    	
    	$this->langTimeZone = $this->timezoneMain->getLangTimeZone($this->session->getParameter("_default_TZ"));
    	// 初期化
    	$this->session_params = $this->session->getParameter(array("user", "regist", $this->user_id));
    	$this->session_public_params = $this->session->getParameter(array("user", "regist_public", $this->user_id));
    	$this->session_reception_params = $this->session->getParameter(array("user", "regist_reception", $this->user_id));
    	// セッションクリア
    	$this->session->removeParameter(array("user", "regist", $this->user_id));
    	$this->session->removeParameter(array("user", "regist_public", $this->user_id));
    	$this->session->removeParameter(array("user", "regist_reception", $this->user_id));
    	$this->session->removeParameter(array("user", "regist_confirm", $this->user_id));
    	
    	if(!isset($this->op)) {
    		$this->session->removeParameter(array("user", "selroom", $this->user_id));
    		$this->session->removeParameter(array("user", "selauth", $this->user_id));
    	}
    	// 参加ルームセッション保存
    	if($this->enroll_room != null || $this->not_enroll_room != null) {
    		$this->session->removeParameter(array("user", "selroom", $this->user_id));
    		$this->session->setParameter(array("user", "selroom", $this->user_id, "enroll_room"), $this->enroll_room);
    		$this->session->setParameter(array("user", "selroom", $this->user_id, "not_enroll_room"), $this->not_enroll_room);
    	}
    	// 権限設定をセッション保存
    	if($this->room_authority != null) {
    		$this->session->removeParameter(array("user", "selauth", $this->user_id));
    		$this->session->setParameter(array("user", "selauth", $this->user_id, "room_authority"), $this->room_authority);
    		$this->session->setParameter(array("user", "selauth", $this->user_id, "createroom_flag"), $this->createroom_flag);
    	}
        return 'success';
    }
    
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_getUsersFetchcallback($result) {
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");
		
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(defined("_TZ_GMT0")) {
				//timezone.iniがincludeされているならば
				$row['timezone_offset_lang'] = $timezoneMain->getLangTimeZone($row['timezone_offset'], false);
			}
			//if($row['authority_system_flag'] == _ON && defined($row['role_authority_name'])) {
			//	$row['role_authority_name'] = constant($row['role_authority_name']);
			//}
			$row['role_authority_name'] = $row['role_authority_id'];
			$row['active_flag_lang'] = $row['active_flag'];
			//if($row['active_flag'] == _ON) {
			//	$row['active_flag_lang'] = "USER_ITEM_ACTIVE_FLAG_ON";
			//} else {
			//	$row['active_flag_lang'] = "USER_ITEM_ACTIVE_FLAG_OFF";
			//}
			// 言語
			if(isset($languages[$row['lang_dirname']])) {
				$row['lang_dirname_lang'] = $row['lang_dirname'];	//$languages[$row['lang_dirname']];
			}
			$ret[] = $row;
		}
		return $ret;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return boolean
	 * @access	private
	 */
	function _getSysModulesFetchcallback($result) {
		$site_modules_dir_arr = explode("|", AUTHORITY_SYS_DEFAULT_MODULES_ADMIN);	
		while ($obj = $result->fetchRow()) {
			if($obj["authority_id"] === null) continue;
			$module_id = $obj["module_id"];
			
			$pathList = explode("_", $obj["action_name"]);
			if(!in_array($pathList[0], $site_modules_dir_arr)) {
				return true;	
			}
		}
		return null;
	}
}
?>
