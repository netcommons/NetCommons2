<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	var $_session = null;
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Whatsnew_Components_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_request =& $this->_container->getComponent("Request");
	}

	/**
	 * 既読にする
	 *
	 * @access	public
	 */
	function setRead() 
	{
    	$_user_id = $this->_session->getParameter("_user_id");
		if (empty($_user_id)) {
			return true;
		}
		$whatsnew = $this->_request->getParameter("whatsnew");
		if (empty($whatsnew)) {
			return false;
		}

		$params = array(
			"whatsnew_id"=>$whatsnew["whatsnew_id"], 
			"user_id" => $_user_id,
			'room_id' => $whatsnew['room_id']
		);
		$count = $this->_db->countExecute("whatsnew_user", $params);
		if ($count > 0) {
			return true;
		}
		
		$result = $this->_db->insertExecute("whatsnew_user", $params, false);
		if (!$result) {
			return false;
		}
        return true;
	}

	/**
	 * ルーム指定
	 *
	 * @access	public
	 */
	function setSelectRoom() 
	{
		$block_id = $this->_request->getParameter("block_id");

		$select_room = intval($this->_request->getParameter("select_room"));
		if ($select_room == _ON) {
			$not_enroll_room = $this->_session->getParameter(array("whatsnew", "not_enroll_room", $block_id));
			$enroll_room = $this->_session->getParameter(array("whatsnew", "enroll_room", $block_id));
			
			if (!isset($not_enroll_room) && !isset($enroll_room)) {
				$enroll_room = array($this->_session->getParameter("_main_room_id"));
			}
			
			$whatsnewView =& $this->_container->getComponent("whatsnewView");
	    	$whatsnew_obj = $whatsnewView->getBlock($block_id);
	    	if (!$whatsnew_obj) {
	    		return false;
	    	}
	    	if (!empty($whatsnew_obj["select_room_list"]) && !empty($not_enroll_room)) {
	    		foreach ($not_enroll_room as $i=>$room_id) {
	    			if (in_array($room_id, $whatsnew_obj["select_room_list"])) {
						$params = array(
							"block_id" => $block_id,
							"room_id" => $room_id
						);
						$result = $this->_db->deleteExecute("whatsnew_select_room", $params);
						if (!$result) {
							return false;
						}
	    			}
	    		}
	    	}
	    	if (!empty($enroll_room)) {
	    		foreach ($enroll_room as $i=>$room_id) {
	    			if (empty($whatsnew_obj["select_room_list"]) || !in_array($room_id, $whatsnew_obj["select_room_list"])) {
						$params = array(
							"block_id" => $block_id,
							"room_id" => $room_id
						);
						$result = $this->_db->insertExecute("whatsnew_select_room", $params);
						if (!$result) {
							return false;
						}
	    			}
	    		}
	    	}
	    	
		} else {
			$params = array(
				"block_id" => $block_id
			);
			$result = $this->_db->deleteExecute("whatsnew_select_room", $params);
			if (!$result) {
				return false;
			}
		}
        return true;
	}
}
?>