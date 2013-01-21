<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action
{
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Sessionを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var モジュール管理を保持
	 *
	 * @access	private
	 */
	var $_modulesView = null;

	/**
	 * @var モジュール管理を保持
	 *
	 * @access	private
	 */
	var $_mobile_obj = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Mobile_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_modulesView =& $this->_container->getComponent("modulesView");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_mobile_obj = $this->_modulesView->getModuleByDirname("mobile");
	}

	/**
	 * 携帯モジュールの登録
	 *
	 * @access	public
	 */
	function insertMobile($module_id, $params) 
	{
		if (!$this->_mobile_obj) { return true; }
    	$base_params = array(
			"module_id" => $module_id,
			"upload_id" => 0,
			"mobile_action_name" => "",
			"use_flag" => _ON,
			"display_position" => _DISPLAY_POSITION_CENTER,
			"display_sequence" => 0
		);
		
		$params = array_merge($base_params, $params);
    	$result = $this->_db->insertExecute("mobile_modules", $params, true);
    	if ($result === false) {
    		return false;
    	}
		return true;
	}

	/**
	 * 携帯モジュールの登録
	 *
	 * @access	public
	 */
	function updateMobile($module_id, $params) 
	{
		if (!$this->_mobile_obj) { return true; }
		$result = $this->_db->updateExecute("mobile_modules", $params, array("module_id"=>$module_id));
    	if ($result === false) {
    		return false;
    	}
		return true;
	}

	/**
	 * 携帯モジュールの登録
	 *
	 * @access	public
	 */
	function deleteMobile($module_id) 
	{
		if (!$this->_mobile_obj) { return true; }
		$result = $this->_db->deleteExecute("mobile_modules", array("module_id"=>$module_id));
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * ログイン
	 *
	 * @access	public
	 */
	function setLogin($user_id, $login_id, $password, $user_name) 
	{
		$mobile_info = $this->_session->getParameter("_mobile_info");
		if ($mobile_info["autologin"] != _AUTOLOGIN_OK) { return true; }
		if (empty($mobile_info["tel_id"])) { return true; }

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent('Request');
		$mobileAutoLogin = $request->getParameter('mobile_auto_login');
		if ($mobileAutoLogin != _ON) {
			return true;
		}
		
		$result = $this->_db->deleteExecute("mobile_users", array("user_id"=>$user_id));
		if ($result === false) {
			return false;
		}
		$result = $this->_db->deleteExecute("mobile_users", array("tel_id"=>$mobile_info["tel_id"]));
		if ($result === false) {
			return false;
		}
		$time = timezone_date();
		$params = array(
			"user_id" => $user_id,
			"tel_id" => $mobile_info["tel_id"],
			"login_id" => $login_id,
			"password" => $password,
			"insert_time" =>$time,
			"insert_site_id" => 0,
			"insert_user_id" => $user_id,
			"insert_user_name" => $user_name,
			"update_time" =>$time,
			"update_site_id" => 0,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name
		);
    	$result = $this->_db->insertExecute("mobile_users", $params);
    	if ($result === false) {
    		return false;
    	}
		return true;
	}

	/**
	 * ログアウト
	 *
	 * @access	public
	 */
	function setLogout() 
	{
		$mobile_info = $this->_session->getParameter("_mobile_info");
		if ($mobile_info["autologin"] != _AUTOLOGIN_OK) { return true; }

		$result = $this->_db->deleteExecute("mobile_users", array("user_id"=>$this->_session->getParameter("_user_id")));
		if ($result === false) {
			return false;
		}

		$result = $this->_db->deleteExecute("mobile_users", array("tel_id"=>$mobile_info["tel_id"]));
		if ($result === false) {
			return false;
		}
		return true;
	}
}
?>
