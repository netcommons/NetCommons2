<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール操作時(move,copy,shortcut)に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Action_Admin_Operation extends Action
{
	var $mode = null;	//move or shortcut or copy
	// 移動元
	var $block_id = null;
	var $page_id = null;
	var $module_id = null;
	//var $unique_id = null;
	var $user_id = null;
	var $room_id = null;

	// 移動先
	var $move_page_id = null;
	var $move_room_id = null;
	var $move_block_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $commonOperation = null;

	var $uploadsAction = null;
	var $pmView = null;
	var $pmAction  = null;

	function execute()
	{
		switch ($this->mode) {
    		case "move":

				break;

			case "delete":
				$user_id = false;
				$room_id  = false;

				if($user_id == false){
					$user_id = $this->pmView->getUserIdByPageId($this->room_id, false);
				}

				if($user_id == false){
					$user_id = $this->pmView->getUserIdByPageId($this->page_id, true);
				}

				if($room_id == false){
					$room_id = $this->room_id;
				}

				if($room_id == false){
					$room_id = $this->pmView->getRoomIdByPageId($this->page_id);
				}

				if($user_id != false){
					// pm_message_tag_link
					$result = $this->db->deleteExecute("pm_message_tag_link", array("insert_user_id" => $user_id));
					if ($result === false) {
						return 'error';
					}

					// pm_tag
					$result = $this->db->deleteExecute("pm_tag", array("insert_user_id" => $user_id));
					if ($result === false) {
						return 'error';
					}

					// pm_filter_action_link
					$result = $this->db->deleteExecute("pm_filter_action_link", array("insert_user_id" => $user_id));
					if ($result === false) {
						return 'error';
					}

					// pm_filter
					$result = $this->db->deleteExecute("pm_filter", array("insert_user_id" => $user_id));
					if ($result === false) {
						return 'error';
					}

					// pm_forward
					$result = $this->db->deleteExecute("pm_forward", array("insert_user_id" => $user_id));
					if ($result === false) {
						return 'error';
					}

					// pm_message
					$messageIds = $this->pmView->getMessageIdsByUserId($user_id);
					for($i = 0; $i < sizeof($messageIds); $i++){
						if($this->pmView->isDropedMessage($messageIds[$i], $user_id)){
							/*
							$container =& DIContainerFactory::getContainer();
							$getdata =& $container->getComponent("GetData");
							$modules = $getdata->getParameter("modules");
							$module_id = $modules["pm"]["module_id"];
							*/
							$upload_id = $this->pmView->getUploadId($this->module_id, $messageIds[$i]);
							if($upload_id != false){
								$this->uploadsAction->delUploadsById($upload_id);
							}

							$result = $this->db->deleteExecute("pm_message", array("message_id" => $messageIds[$i]));
							if ($result === false) {
								return 'error';
							}
						}
					}

					// pm_message_receiver
					$result = $this->db->deleteExecute("pm_message_receiver", array("receiver_user_id" => $user_id));
					if ($result === false) {
						return 'error';
					}
				}
				break;

			default:
				break;
		}

		return "true";
	}
}
?>