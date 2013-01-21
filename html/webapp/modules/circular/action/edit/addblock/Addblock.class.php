<?php

/**
 * ブロック追加処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Action_Edit_Addblock extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $db = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$params = array(
			'room_id' => $this->room_id,
			'block_id' => $this->block_id,
			'visible_row' => CIRCULAR_DEFAULT_VISIBLE_ROW,
			'block_type' => CIRCULAR_BLOCK_TYPE_NORMAL
		);
		$result = $this->db->insertExecute('circular_block', $params, true);
		if($result === false) {
			return 'error';
		}

		$params = array(
			'room_id' => $this->room_id
		);
		$result = $this->db->selectExecute('circular_config', $params);
		if($result === false) {
			return 'error';
		}
		if (!isset($result[0])) {
			$params = array(
				'room_id' => $this->room_id,
				'create_authority' => _AUTH_CHIEF,
				'mail_subject' => CIRCULAR_MAIL_SUBJECT,
				'mail_body' => CIRCULAR_MAIL_BODY
			);
			$result = $this->db->insertExecute('circular_config', $params, true);
			if($result === false) {
				return 'error';
			}
		}

		return 'success';
	}
}
?>
