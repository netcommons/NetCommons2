<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Chat_Action_Main_Clear extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $chat_id = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	
	// 値をセットするため

	/**
	 * [[機能説明]]
	 *
	 * @access  public
	 */
	function execute()
	{
		$params = array(
			"block_id" => $this->block_id,
		);
		
		$params = array(
			"chat_id" => $this->chat_id,
			"block_id" => $this->block_id
		);
		$sql_delete = "DELETE FROM {chat_contents} ".
					"WHERE chat_id<=? ".
					"AND block_id=?";
		$result = $this->db->execute($sql_delete,$params);
		if($result === false) {
			return 'error';
		}
		
		return 'success';
	}
}
?>
