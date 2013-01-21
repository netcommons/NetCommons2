<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Online取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Online_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Online_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_container =& $container;
		$this->_db =& $container->getComponent("DbObject");
	}
	
	function &getTotalMember(){
		
		$sql = "SELECT count(*) as cnt ".
  				 "FROM {users} ";
        $result = $this->_db->execute($sql);
		if ($result === false) {
			$this->_db->addError();
			return "error";
		}				
		return $result[0]["cnt"];
	}
	
	function &getUserMember(){
		
		$request =& $this->_container->getComponent("Request");		
		$session =& $this->_container->getComponent("Session");	
		$configView =& $this->_container->getComponent("configView");
		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfigByConfname($module_id, "onlineTime");
		$onlineTime = $config["conf_value"];

		$baseSessionID = $session->getParameter("_base_sess_id");
		if (empty($baseSessionID)) {
			$baseSessionID = session_id();
		}

		$date = date("YmdHis", time() - $onlineTime);
		$params = array(
			$date,
			$baseSessionID,
			_OFF
		);
		$sql = "SELECT base_sess_id, sess_data ".
				"FROM {session} ".
				"WHERE sess_updated > ? ".
				"AND base_sess_id != ? ".
				"AND old_flag = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return "error";
		}

		$sessionIDs = array();
		$member = 0;
		foreach (array_keys($result) as $key) {
			if (in_array($result[$key]["base_sess_id"], $sessionIDs)) {
				continue;
			}
			
			$sessionIDs[] = $result[$key]["base_sess_id"];
			
			if (preg_match('/;_user_id\|s:40:"([0-9a-zA-Z]+)";/', $result[$key]["sess_data"], $matches)) {
				$member++;
			}

		}
		$user = count($sessionIDs) + 1;

		$userID = $session->getParameter("_user_id");
		if (!empty($userID)) {
			$member++;
		}
		
		$userMember = array("user" => $user,
						   "member" => $member);
		
		return $userMember;
	}

}
?>