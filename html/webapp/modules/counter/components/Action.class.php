<?php
/**
 * カウンタテーブル登録用クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Counter_Components_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Counter_Components_Action() {
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * カウンターのデフォルト値を登録する
	 *
	 * @return boolean true or false
	 * @access	public
	 */
	function setCounter()
	{
        $params = array(
			"block_id" => $this->_request->getParameter("block_id"),
			"counter_digit" => intval($this->_request->getParameter("counter_digit")),
			"show_type" => $this->_request->getParameter("show_type"),
			"show_char_before" => $this->_request->getParameter("show_char_before"),
			"show_char_after" => $this->_request->getParameter("show_char_after"),
			"comment" => $this->_request->getParameter("comment")
		);

		if ($this->_request->getParameter("zero_flag") == _ON) {
			$params["counter_num"] = "0";
		}

		if (!$this->_db->updateExecute("counter", $params, "block_id", true)) {
			return false;
		}

		return true;
	}

	/**
	 * カウンターのデフォルト値を登録する
	 *
	 * @return boolean true or false
	 * @access	public
	 */
	function setDefault()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);
		if ($config === false) {
        	return $config;
        }

		$params = array(
			"block_id" => $this->_request->getParameter("block_id"),
			"counter_digit" => $config["counter_digit"]["conf_value"],
			"counter_num" => "0",
			"show_type" => $config["show_type"]["conf_value"],
			"show_char_before" => SHOW_CHAR_BEFORE,
			"show_char_after" => SHOW_CHAR_AFTER,
			"comment" => OTHER_DISP_CHAR
		);

		if (!$this->_db->insertExecute("counter", $params, true)) {
			return false;
		}

		return true;
	}

	/**
	 * カウンターをインクリメントする
	 *
	 * @return boolean true or false
	 * @access	public
	 */
	function incrementCounter()
	{
        $container =& DIContainerFactory::getContainer();
        $session =& $container->getComponent("Session");

		$params = array(
			timezone_date(),
			$session->getParameter("_site_id"),
			$session->getParameter("_user_id"),
			$session->getParameter("_handle"),
			$this->_request->getParameter("block_id")
		);

		$sql = "UPDATE {counter} SET ".
						"counter_num = counter_num + 1, ".
						"update_time = ?, ".
						"update_site_id = ?, ".
						"update_user_id = ?, ".
						"update_user_name = ? ".
					" WHERE block_id = ?";
        if (!$this->_db->execute($sql, $params)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}
}
?>
