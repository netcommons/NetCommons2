<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯用クラスFilter
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Mobile extends Filter
{
	var $_classname = "Filter_Mobile";

	var $_container;
	var $_log;
	var $_filterChain;
	var $_actionChain;
	var $_db;
	var $_session;
	var $_request;
	var $_modulesView;
	//var $_mobile_obj;
	var $_usersView;
	var $_clear_page_id = array("pages_view_main", "login_action_mobile_init", "login_action_main_logout", "pages_view_mobile");
	var $_clear_reader = array("login_action_main_logout");

	/**
	 * コンストラクター
	 *
	 * @access  public
	 */
	function Filter_Mobile()
	{
		parent::Filter();
	}

	/**
	 * Mobile用クラス実行
	 *
	 * @access  public
	 *
	 */
	function execute()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_log =& LogFactory::getLog();
		$this->_filterChain =& $this->_container->getComponent("FilterChain");
		$this->_actionChain =& $this->_container->getComponent("ActionChain");
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_modulesView =& $this->_container->getComponent("modulesView");
		//$this->_mobile_obj = $this->_modulesView->getModuleByDirname("mobile");
		$this->_usersView =& $this->_container->getComponent("usersView");

		//mb_stringがロードされているかどうか
		if (!extension_loaded('mbstring') && !function_exists("mb_convert_encoding")) {
			include_once MAPLE_DIR  . '/includes/mbstring.php';
		} else if(function_exists("mb_detect_order")){
			mb_detect_order(_MB_DETECT_ORDER_VALUE);
		}
		if (function_exists("mb_internal_encoding")) {
			mb_internal_encoding(INTERNAL_CODE);
		}
		if (function_exists("mb_language")) {
			mb_language("Japanese");
		}

		$this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
		$this->_preFilter();

		$this->_filterChain->execute();

		$this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
		$this->_postFilter();
	}

	/**
	 * プレフィルタ
	 *
	 * @access  private
	 */
	function _preFilter()
	{
		require_once( WEBAPP_DIR . "/config/mobile.inc.php" );

		$this->_session->setParameter("_mobile_flag", _OFF);
		$this->_session->setParameter("_smartphone_flag", _OFF);
		//if (!$this->_mobile_obj) { return; }

		// 読み上げソフト対応
		$actionName = $this->_actionChain->getCurActionName();
		if ($actionName == DEFAULT_MOBILE_ACTION) {
			$reader_flag = $this->_request->getParameter("reader_flag");
			if (isset($reader_flag)) {
				$reader_flag = intval($reader_flag);
			} else {
				$reader_flag = intval($this->_session->getParameter("_reader_flag"));
			}
		} else {
			$reader_flag = intval($this->_session->getParameter("_reader_flag"));
		}
		// スマートフォンPCビューアー切り替え対応
		if($actionName == DEFAULT_ACTION) {
			$pcviewer_flag = $this->_request->getParameter('pcviewer_flag');
			if (isset($pcviewer_flag)) {
				$pcviewer_flag = intval($pcviewer_flag);
				$this->_session->setParameter('_pcviewer_flag', $pcviewer_flag);
			} else {
				$pcviewer_flag = intval($this->_session->getParameter('_pcviewer_flag'));
			}
		} else {
			$pcviewer_flag = intval($this->_session->getParameter('_pcviewer_flag'));
		}
		if($pcviewer_flag == _ON){
			return;
		}

		if (in_array($actionName, $this->_clear_page_id)) {
			$this->_session->removeParameter("_mobile_page_id");
			$this->_session->removeParameter("_mobile_room_id");
			$this->_session->removeParameter("_mobile_module_id");
		}
		if (in_array($actionName, $this->_clear_reader)) {
			$this->_session->removeParameter("_reader_flag");
		} else {
			$this->_session->setParameter("_reader_flag", $reader_flag);
		}

		$mobileCheck =& MobileCheck::getInstance();
		if ($mobileCheck->isMobile() == _OFF) { return; }
		$mobile_info = $mobileCheck->getMobileInfo();
		$mobile_obj = $this->_modulesView->getModuleByDirname("mobile");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($mobile_obj["module_id"], false);
		if ($config == false) { return; }
		if ($config["allow_emulator"]["conf_value"] == _OFF && !$mobile_info["proper_route"]) { return; }

		// この人物が携帯をどのようなモードで見ようとしているかを取得
		$texthtml_mode = -1;
		$imgdsp_size = -1;
		$user_id = $this->_session->getParameter("_user_id");
		if( $user_id != '0' ) {
			$texthtml_mode_item_id = $this->_usersView->getItemIdByTagName( "mobile_texthtml_mode" );
			$imgdsp_size_item_id = $this->_usersView->getItemIdByTagName( "mobile_imgdsp_size" );
			$user_items = $this->_usersView->getUserItemLinkById( $user_id );
			if( isset( $user_items[ $texthtml_mode_item_id ] ) && $user_items[ $texthtml_mode_item_id ]['content'] != "" ) {
				$texthtml_mode = constant( str_replace( "USER_ITEM", "MOBILE", trim( $user_items[ $texthtml_mode_item_id ]['content'], "|" ) ) );
			}
			if( isset( $user_items[ $imgdsp_size_item_id ] ) && $user_items[ $imgdsp_size_item_id ]['content'] != "" ) {
				$imgdsp_size = constant( str_replace( "USER_ITEM", "MOBILE", trim( $user_items[ $imgdsp_size_item_id ]['content'], "|" ) ) );
			}
		}
		if( $texthtml_mode == -1 ) {
			$texthtml_mode = $config["mobile_text_html_mode"]["conf_value"];
		}
		if( $imgdsp_size == -1 ) {
			$imgdsp_size = $config["mobile_imgdsp_size"]["conf_value"];
		}
		$this->_session->setParameter("_mobile_text_html_mode", $texthtml_mode);
		$this->_session->setParameter("_mobile_imgdsp_size", $imgdsp_size);

		$this->_session->setParameter("_mobile_info", $mobile_info);
		$this->_session->setParameter("_mobile_flag", _ON);
		if($mobileCheck->isSmartPhone() == true) {
			$this->_session->setParameter('_smartphone_flag', _ON);
		}

		$action_name = $this->_request->getParameter("action");
		$page_id = intval($this->_request->getParameter("page_id"));
		$room_id = intval($this->_request->getParameter("room_id"));
		if (!isset($page_id)) {
			$page_id = intval($this->_session->getParameter("_mobile_page_id"));
		}
		$room_id = $this->_request->getParameter("room_id");
		if (!isset($room_id)) {
			$room_id = intval($this->_session->getParameter("_mobile_room_id"));
		}
		$block_id = intval($this->_request->getParameter("block_id"));


		$mobileView =& $this->_container->getComponent("mobileView");
		$getdata =& $this->_container->getComponent("GetData");

		$mobile_modules = $mobileView->getModules(null, array($this,"_callbackFunc"));
		$getdata->setParameter("mobile_modules", $mobile_modules);

		$active_action = $this->_request->getParameter("active_action");
		if ($active_action != "") {
			$params = $this->_request->getParameters();

			$this->_request->setParameter("action", $active_action);
			$this->_request->setParameter("active_action", null);
			$this->_request->setParameter(_ABBREVIATE_URL_REQUEST_KEY, null);

			$params = $this->_request->getParameters();
			$str_params = "";
			foreach($params as $key => $value) {
				if (empty($value) || is_array($value)) { continue; }
				if (substr($key, 0, 1) == "_") { continue; }

				$key = htmlspecialchars($key, ENT_QUOTES);
				if ($key == session_name()) {
					$value = session_id();
				} else {
					$value = rawurlencode($value);
				}
				$str_params .= "&" . $key."=".$value;
			}

			if ($this->_session->getParameter("_user_id") == "" && $block_id > 0) {
				$blocksView =& $this->_container->getComponent("blocksView");
				$blocks = $blocksView->getBlockById($block_id);
				if (empty($blocks)) {
					header('Location: '.BASE_URL.INDEX_FILE_NAME);
					exit;
				}

				$authCheck =& $this->_container->getComponent("authCheck");
				$result = $authCheck->getPageAuthId(null, $blocks["page_id"]);
				if ($result == _AUTH_OTHER) {
					if ($reader_flag == _ON) {
						$session_param = "";
					} else {
						$session_param = "&amp;".session_name()."=".session_id();
					}
					$this->_session->setParameter("_mobile_redirect_url", $str_params);
					header('Location: '.BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".$mobile_modules[_DISPLAY_POSITION_HEADER]["login"]["mobile_action_name"].$session_param);
					exit;
				}
			}

			$this->_session->removeParameter("_mobile_redirect_url");
			header('Location: '.BASE_URL.INDEX_FILE_NAME."?".substr($str_params, 1));
			exit;

		} elseif ($action_name == DEFAULT_ACTION) {
			$parameters = (!empty($page_id) ? "&page_id=".$page_id."&room_id=".intval($room_id) : "").(!empty($block_id) ? "&block_id=".$block_id : "");
			if ($reader_flag == _OFF) {
				$parameters .= "&".session_name()."=".session_id();
			}
			$action_name = DEFAULT_MOBILE_ACTION;
			if (!empty($block_id)) {
				$blocksView =& $this->_container->getComponent("blocksView");
				$modulesView =& $this->_container->getComponent("modulesView");
				$blocks = $blocksView->getBlockById($block_id);
				if (!empty($blocks)) {
					$modules = $modulesView->getModulesById($blocks["module_id"]);
					if (!empty($modules)) {
						$action_name = $modules["action_name"];
					}
				}
			}
			header('Location: '.BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".$action_name.$parameters);
			exit;
		}

		$this->_session->setParameter("_mobile_default_module", $config["default_module"]["conf_value"]);

		/*
		 * リクエストの変換
		 */
		$action =& $this->_actionChain->getCurAction();
		$attributes = $this->getAttributes();
		//if (empty($attributes)) { return; }

		$params = $this->_request->getParameters();
		$this->_strtoconvert($params);
		$this->_request->clear();
		$this->_request->setParameters($params);

		foreach ($attributes as $key=>$value) {
			$keyArr = explode(":", $key);
			$parameter = null;
			if ($keyArr[0] == "date" || $keyArr[0] == "input_date" || $keyArr[0] == "time" || $keyArr[0] == "time12" || $keyArr[0] == "full_date" || $keyArr[0] == "full_time") {
				$valArr = explode(",", $keyArr[1]);
				if (($keyArr[0] == "date" || $keyArr[0] == "input_date") && count($valArr) == 3 ||
					$keyArr[0] == "time" && count($valArr) <= 3 ||
					$keyArr[0] == "time12" && count($valArr) > 0 && count($valArr) <= 4 && (strtolower($valArr[0]) == "am" || strtolower($valArr[0]) == "pm") ||
					($keyArr[0] == "full_date" || $keyArr[0] == "full_time") && count($valArr) == 6) {

					foreach ($valArr as $i=>$val) {
						$valArr[$i] = $this->_request->getParameter($val);
					}
					if ($keyArr[0] == "date") {
						list($month, $day, $year) = $valArr;
						list($hour, $min, $sec) = array(0,0,0);
					} elseif ($keyArr[0] == "input_date") {
						list($month, $day, $year) = $valArr;
						list($hour, $min, $sec) = array(0,0,0);
					} elseif ($keyArr[0] == "time") {
						list($month, $day, $year) = array(date("m"), date("d"), date("Y"));
						if (count($valArr) < 1) { $valArr[0] = 0; }
						if (count($valArr) < 2) { $valArr[1] = 0; }
						if (count($valArr) < 3) { $valArr[2] = 0; }
						list($hour, $min, $sec) = $valArr;
					} elseif ($keyArr[0] == "time12") {
						list($month, $day, $year) = array(date("m"), date("d"), date("Y"));
						if (count($valArr) < 2) { $valArr[1] = 0; }
						if (count($valArr) < 3) { $valArr[2] = 0; }
						if (count($valArr) < 4) { $valArr[3] = 0; }
						list($am_pm, $hour, $min, $sec) = $valArr;
						if (strtolower($am_pm) == "pm") {
							$hour = intval($hour) + 12;
						}
					} else {
						list($hour, $min, $sec, $month, $day, $year) = $valArr;
					}
					if ($keyArr[0] == "time" || $keyArr[0] == "time12" || $keyArr[0] != "time" && $keyArr[0] != "time12" && checkdate(intval($month),intval($day),intval($year))) {
						if ($keyArr[0] == "date" && empty($keyArr[2])) { $keyArr[2] = _DATE_FORMAT; }
						if ($keyArr[0] == "input_date" && empty($keyArr[2])) { $keyArr[2] = _INPUT_DATE_FORMAT; }
						if (($keyArr[0] == "time" || $keyArr[0] == "time12") && empty($keyArr[2])) { $keyArr[2] = _TIME_FORMAT; }
						if (($keyArr[0] == "full_date" || $keyArr[0] == "full_time") && empty($keyArr[2])) { $keyArr[2] = _FULL_DATE_FORMAT; }
						$parameter = date($keyArr[2], mktime(intval($hour), intval($min), intval($sec), intval($month), intval($day), intval($year)));
						$this->_request->setParameter($value, $parameter);
						continue;
					} else {
						$paramArr = explode(",", $keyArr[1]);
					}
				} else {
					$paramArr = explode(",", $keyArr[1]);
				}
			} else {
				$paramArr = explode(",", $key);
			}
			foreach ($paramArr as $i=>$keyVal) {
				$str = $this->_request->getParameter($keyVal);
				if (!isset($str)) { continue; }
				$parameter .= $str;
			}
			if (!isset($parameter)) { continue; }
			$this->_request->setParameter($value, $parameter);
		}

	}
	/**
	 * 携帯モジュール取得
	 *
	 * @access  private
	 */
	function _callbackFunc(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$pathList = explode("_", $row["mobile_action_name"]);
			$row["dir_name"] = $pathList[0];
			$row["module_name"] = $this->_modulesView->loadModuleName($row["dir_name"]);
			$result[$row["display_position"]][$row["dir_name"]] = $row;
		}
		return $result;
	}
	/**
	 * 携帯モジュール取得
	 *
	 * @access  private
	 */
	function _strtoconvert(&$params)
	{
		foreach (array_keys($params) as $key) {
			if (is_array($params[$key])) {
				$this->_strtoconvert($params[$key]);
			} else {
				$params[$key] = mb_convert_encoding($params[$key], "utf-8", "auto");
			}
		}
	}

	/**
	 * ポストフィルタ
	 *
	 * @access  private
	 */
	function _postFilter()
	{
		//if (!$this->_mobile_obj) { return true; }

		$tel_id = $this->_session->getParameter("_mobile_tel_id");
		if (!empty($tel_id)) {
			$user_id = $this->_session->getParameter("_user_id");
			$result = $this->_db->updateExecute("mobile_users", array(), array("user_id"=>$user_id), true);
			if ($result === false) {
				return false;
			}

			if (rand(0, 10) != 0) {
				$maxlifetime = $this->_session->getParameter('_session_gc_maxlifetime');
				$time = timezone_date();
				$timestamp = mktime(intval(substr($time,8,2)),intval(substr($time,10,2))-$maxlifetime,intval(substr($time,12,2)),
									intval(substr($time,4,2)),intval(substr($time,6,2)),intval(substr($time,0,4)));
				$sql = "DELETE FROM {mobile_users} WHERE update_time < ?";
				$result = $this->_db->execute($sql, array("update_time"=>date("YmdHis", $timestamp)));
				if ($result === false) {
					$this->_db->addError();
					return false;
				}
			}
		}

		$page_id = intval($this->_request->getParameter("page_id"));
		if ($page_id > 0) {
			$this->_session->setParameter("_mobile_page_id", $page_id);
		}
		$room_id = intval($this->_request->getParameter("room_id"));
		if ($room_id > 0) {
			$this->_session->setParameter("_mobile_room_id", $room_id);
		}
		$module_id = intval($this->_request->getParameter("module_id"));
		if ($module_id > 0) {
			$this->_session->setParameter("_mobile_module_id", $module_id);
		}
	}
}
?>
