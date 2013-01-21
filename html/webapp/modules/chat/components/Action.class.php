<?php
/**
 * チャットテーブル登録用クラス
 *
 * @package     [[package名]]
 * @author      Ryuji Masukawa
 * @copyright   copyright (c) 2006 NetCommons.org
 * @license     [[license]]
 * @access      public
 */
class Chat_Components_Action {
	/**
	 *
	 *
	 * @access	public
	 */
	var $_db = null;
	var $_container = null;

	var $site_id = null;
	var $user_id = null;
	var $user_name = null;
	var $time = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Chat_Components_Action() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");

		$session =& $this->_container->getComponent("Session");
		$this->site_id = $session->getParameter("_site_id");
		$this->user_id = $session->getParameter("_user_id");
		$this->user_name = $session->getParameter("_handle");
		$this->time = date("YmdHis");
	}

	/**
	 * チャットログイン
	 * @param int, bool
	 * @return array
	 * @access	public
	 */
	function setChatLogin($block_id) {
		$request =& $this->_container->getComponent("Request");
		$session =& $this->_container->getComponent("Session");
		$sess_id = $session->getID();
		$is_login = $this->checkLogin($block_id);

		if ($session->getParameter("_mobile_flag") == _ON) {
			//携帯の場合、Ajaxが利用できないため、5分間は入室中とする
			$time = date("YmdHis", mktime() + 60*5);
		} else {
			$time = $this->time;
		}

		if($is_login) {
			$params = array(
				"block_id" => $block_id,
				"sess_id" => $sess_id,
				"update_time" => $time
			);
			$result = $this->updChatLogin($params);
		} else {
			if(!empty($this->user_id)) {
				$params = array(
					"block_id" => $block_id,
					"sess_id" => $sess_id,
					"room_id" => $request->getParameter("room_id"),
					"insert_time" => $time,
					"insert_site_id" => $this->site_id,
					"insert_user_id" => $this->user_id,
					"insert_user_name" => $this->user_name,
					"update_time" => $time,
					"update_site_id" => $this->site_id,
					"update_user_id" => $this->user_id,
					"update_user_name" => $this->user_name
				);
				$result = $this->insChatLogin($params);
			}else {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * チャットログアウト
	 * @param int, int
	 * @return array
	 * @access	public
	 */
	function setChatLogout($block_id, $reload) {
		$this->delChatLogout(array("block_id"=>$block_id));
		$ret = $this->getChatLogin($block_id, $reload);
		return $ret;
	}

	/**
	 * チャットログイン
	 * @param array
	 * @return boolean
	 * @access	public
	 */
	function insChatLogin($params=array()) {
    	$result = $this->_db->insertExecute("chat_login", $params);
    	if($result === false) {
    		return false;
    	}
		return true;
	}

	/**
	 * チャットログイン
	 * @param array
	 * @return boolean
	 * @access	public
	 */
	function updChatLogin($params=array())
	{
		$where_params = array(
			"block_id" => $params["block_id"],
			"update_user_id" => $this->user_id
		);
    	$result = $this->_db->updateExecute("chat_login", $params, $where_params);
    	if($result === false) {
    		return false;
    	}
    	return true;
	}

	/**
	 * リロード時間×2秒アクセスがないログインデータを削除
	 * @param array
	 * @return boolean
	 * @access	public
	 */
	function delChatLogin($block_id, $reload) {
		$local_time = date("YmdHis",
								mktime(substr($this->time, 8, 2),
								substr($this->time, 10, 2),
								substr($this->time, 12, 2) - ($reload * 2),
								substr($this->time, 4, 2),
								substr($this->time, 6, 2),
								substr($this->time, 0, 4)));
		//$update_time = timezone_date($local_time, true, "YmdHis");
		$params = array(
			"block_id" => $block_id,
			"update_time" => $local_time
		);
		$sql_delete = "DELETE FROM {chat_login} ".
					"WHERE block_id=? ".
					"AND update_time<?";
		$result = $this->_db->execute($sql_delete,$params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}
		return true;
	}

	/**
	 * ログアウトした者のログインデータを削除
	 * @param array
	 * @return boolean
	 * @access	public
	 */
	function delChatLogout($params=array()) {
		$params_where = array(
			"update_site_id" => $this->site_id,
			"update_user_id" => $this->user_id
		);
		$params = array_merge($params,$params_where);
		$result = $this->_db->deleteExecute("chat_login", $params);
		if($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * ブロックIDからチャットにログイン中のユーザ名を取得
	 * @param array
	 * @return array
	 * @access	public
	 */
	function getChatLogin($block_id, $reload) {
		$this->delChatLogin($block_id, $reload);

		$params = array(
			"block_id" => $block_id
		);

		$order = array(
			"insert_time" => "DESC"
		);
    	$result = $this->_db->selectExecute("chat_login", $params, $order);
    	if($result === false) {
    		return false;
    	}

		return $result;
	}

	function checkLogin($block_id) {
		$params = array(
			"block_id" => $block_id,
			"update_user_id" => $this->user_id
		);

    	$result = $this->_db->selectExecute("chat_login", $params);
    	if($result === false || empty($result)) {
    		return false;
    	}

		return true;
	}

	function getUserId() {
		return $this->user_id;
	}
}
?>
