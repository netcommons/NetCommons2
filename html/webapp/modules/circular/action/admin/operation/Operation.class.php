<?php

/**
 * モジュール操作
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Circular_Action_Admin_Operation extends Action
{
	// move or shortcut or copy
	var $mode = null;

	// 移動元
	var $room_id = null;
	var $block_id = null;

	// 移動先
	var $move_room_id = null;
	var $move_block_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $circularAction = null;
	var $commonOperation = null;

	function execute()
	{
		$room_id = intval($this->room_id);
		$block_id = intval($this->block_id);
		$move_room_id = intval($this->move_room_id);
		$move_block_id = intval($this->move_block_id);

		switch ($this->mode) {

			case 'move':
				$upd_params = array(
					'room_id'  => $move_room_id
				);
				$cond_params = array(
					'room_id'  => $room_id
				);
				$table_list = array(
					'circular',
					'circular_user'
				);
				foreach ($table_list as $table) {
					$result = $this->db->updateExecute($table, $upd_params, $cond_params, false);
					if ($result === false) {
						return 'false';
					}
				}
				$upd_params["block_id"] = $move_block_id;
				$cond_params["block_id"] = $block_id;
				$result = $this->db->updateExecute("circular_block", $upd_params, $cond_params, false);
				if ($result === false) {
					return 'false';
				}

				$cond_params = array(
					'room_id' => $move_room_id
				);
				$result = $this->db->selectExecute('circular_config', $cond_params);
				if($result === false) {
					return 'error';
				}
				if (!isset($result[0])) {
					$params = array(
						'room_id' => $move_room_id,
						'create_authority' => _AUTH_CHIEF,
						'mail_subject' => CIRCULAR_MAIL_SUBJECT,
						'mail_body' => CIRCULAR_MAIL_BODY
					);
					$result = $this->db->insertExecute('circular_config', $params, true);
					if($result === false) {
						return 'error';
					}
				}

				$circular = $this->db->selectExecute('circular', $cond_params);
				if($circular === false) {
					return 'false';
				}
				$upload_id_array = $this->commonOperation->getWysiwygUploads('circular_body', $circular);
				$result = $this->commonOperation->updWysiwygUploads($upload_id_array, $move_room_id);
				if($result === false) {
					return 'false';
				}
				break;

			default:
				return 'false';
		}
		return 'true';
	}
}
?>