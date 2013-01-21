<?php
/**
 * チャットテーブル表示用クラス
 *
 * @package     [[package名]]
 * @author      Ryuji Masukawa
 * @copyright   copyright (c) 2006 NetCommons.org
 * @license     [[license]]
 * @access      public
 */
class Chat_Components_View {
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
	function Chat_Components_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * ブロックIDからチャット管理データ取得
	 * @param int block_id
	 * @access	public
	 */
	function &getChatById($id) {
		$params = array(
			"block_id" => $id
		);

		$sql = "SELECT {chat}.*,{chat_login}.block_id as login " .
			   "FROM {chat} LEFT OUTER JOIN {chat_login} " .
			   "ON {chat}.block_id={chat_login}.block_id " .
			   "WHERE {chat}.block_id=? ";

		$result = $this->_db->execute($sql ,$params);
		if($result === false) {
			return $result;
		}
		return $result[0];
	}

	/**
	 * チャット用デフォルトデータを取得する
	 *
     * @return array	チャット用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultChat() {
		$configView =& $this->_container->getComponent("configView");
		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
        	return $config;
        }

		$chat = array(
			"height" => $config["height"]["conf_value"],
			"width" => $config["width"]["conf_value"],
			"reload" => $config["reload"]["conf_value"],
			"status" => constant($config["status"]["conf_value"]),
			"display_type" => constant($config["display_type"]["conf_value"]),
			"line_num" => $config["line_num"]["conf_value"]
		);

		return $chat;
	}

	/**
	 * ブロックIDからチャットテキストデータ取得
	 * @param int block_id,chat_id
	 * @access	public
	 */
	function &getChatText($block_id, $line_num, $chat_id = "" ) {
		$params = array(
			"block_id" => $block_id
		);

		$sql = "SELECT * " .
				"FROM {chat_contents} " .
				"WHERE {chat_contents}.block_id=?";

		if ($chat_id) {
			$now_chat_id = $this->_db->countExecute("chat_contents", $params);
			if($now_chat_id < $chat_id) {
				$chat_arr = array("chat_id" => 0);
			}else {
				$chat_arr = array("chat_id" => $chat_id);
			}
			$params = array_merge($params, $chat_arr);
			$sql .= " AND {chat_contents}.chat_id>?";
		}

		$sql .= " ORDER BY {chat_contents}.chat_id DESC";

		if (!$chat_id && $line_num) {
			$sql .= " LIMIT ".$line_num;
		}

		$result = $this->_db->execute($sql ,$params);
		return $result;
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr)
	{
		$sql = "SELECT block.*" .
				" FROM {blocks} block" .
				" WHERE block.block_id IN (".implode(",",$block_id_arr).")" .
				" ORDER BY block.col_num ASC, block.row_num ASC";

        return $this->_db->execute($sql, null);
	}

}
?>
