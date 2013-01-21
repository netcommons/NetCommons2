<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレンダー登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Components_Action
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

	/**
	 * @var sessionを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var requestを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var calendarViewを保持
	 *
	 * @access	private
	 */
	var $_calendarView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Calendar_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_calendarView =& $this->_container->getComponent("calendarView");
	}

    /**
     * ブロックを登録する
     *
     * @access  public
     */
    function setBlock()
    {
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$block_id = $this->_request->getParameter("block_id");

		$default = $this->_calendarView->getDefaultBlock();
    	$params = array(
    		"block_id" => $block_id,
			"display_type" => $default["display_type"],
			"start_pos" => $default["start_pos"],
			"display_count" => $default["display_count"],
			"select_room" => _OFF,
			"myroom_flag" => _OFF
		);

		$result = false;

		if ($actionName == "calendar_action_edit_addblock") {
	    	$result = $this->_db->insertExecute("calendar_block", $params, true);

		} elseif ($actionName == "calendar_action_edit_style") {
			$exists = false;
			$count = $this->_db->countExecute("calendar_block", array("block_id"=>$block_id));
			if ($count != 0) {
				$params = array();
				$exists = true;
			}

	    	$params["display_type"] = intval($this->_request->getParameter("display_type"));
	    	switch ($params["display_type"]) {
	    		case CALENDAR_YEARLY:
	    			$params["start_pos"] = intval($this->_request->getParameter("start_pos_yearly"));
	    			break;
	    		case CALENDAR_WEEKLY:
	    			$params["start_pos"] = intval($this->_request->getParameter("start_pos_weekly"));
	    			break;
	    		case CALENDAR_T_SCHEDULE:
	    		case CALENDAR_U_SCHEDULE:
	    			$params["start_pos"] = intval($this->_request->getParameter("start_pos_weekly"));
	    			$params["display_count"] = intval($this->_request->getParameter("display_count"));
	    			break;
	    	}

			$params["select_room"] = intval($this->_request->getParameter("select_room"));
			if ($params["select_room"] == _ON) {
				$myroom_flag = $this->_session->getParameter(array("calendar", "myroom_flag", $block_id));
				if (isset($myroom_flag)) {
					$params["myroom_flag"] = intval($myroom_flag);
				}
			} else {
				$params["myroom_flag"] = _OFF;
			}

	    	if ($exists) {
		    	$result = $this->_db->updateExecute("calendar_block", $params, array("block_id"=>$block_id), true);
	    	} else {
		    	$result = $this->_db->insertExecute("calendar_block", $params, true);
	    	}
		}

    	if ($result === false) {
    		return false;
    	}
    	return true;
    }

    /**
     * 権限を登録する
     *
     * @access  public
     */
    function setEditAuth()
    {
		$manage_list = $this->_request->getParameter("manage_list");
		$add_authority = $this->_request->getParameter("add_authority");

		foreach ($add_authority as $room_id=>$authority_id) {
			if (isset($manage_list[$room_id])) {
				$params = array(
					"add_authority_id" => $authority_id,
					"use_flag" => _ON
				);
		    	$result = $this->_db->updateExecute("calendar_manage", $params, array("room_id"=>$room_id), true);
			} else {
				$params = array(
					"room_id" => $room_id,
					"add_authority_id" => $authority_id,
					"use_flag" => _ON
				);
		    	$result = $this->_db->insertExecute("calendar_manage", $params, true);
			}
	    	if ($result === false) {
	    		return false;
	    	}
		}
    	return true;
    }

	/**
	 * 新着情報の更新
	 *
	 * @access	public
	 */
	function setWhatsnew($plan_params)
	{
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		//--新着情報関連 Start--
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");

		$result = true;
		if ($actionName == "calendar_action_main_plan_add" || $actionName == "calendar_action_main_plan_modify") {
			$description = "";
			if ($plan_params["location"] != "") {
				$description .= sprintf(CALENDAR_LOCATION, $plan_params["location"]);
			}
			if ($plan_params["contact"] != "") {
				$description .= sprintf(CALENDAR_CONTACT, $plan_params["contact"]);
			}
			if ($plan_params["description"] != "") {
				$description .= sprintf(CALENDAR_DESCRIPTION, $plan_params["description"]);
			}
			if (!empty($plan_params["rrule"])) {
				$rrule_str = $this->_calendarView->stringRRule($plan_params["rrule"]);
				$description .= sprintf(CALENDAR_RRULE, $rrule_str);
			}
			$block_id = $this->_calendarView->getBlockIdByWhatsnew();
			$whatsnew = array(
				"room_id" => $plan_params["room_id"],
				"unique_id" => $plan_params["plan_id"],
				"title" => $plan_params["title"],
				"description" => $description,
				"action_name" => "calendar_view_main_init",
				"parameters" => "plan_id=".$plan_params["plan_id"].
								"&block_id=".$block_id."#_".$block_id
			);

			if ($actionName == "calendar_action_main_plan_modify") {
				$plan = $this->_db->selectExecute("calendar_plan", array("plan_id"=>$plan_params["plan_id"]));
				$whatsnew["insert_time"] = $plan[0]["insert_time"];
				$whatsnew["insert_user_id"] = $plan[0]["insert_user_id"];
				$whatsnew["insert_user_name"] = $plan[0]["insert_user_name"];
			}

    		$result = $whatsnewAction->auto($whatsnew, _ON);

		} elseif ($actionName == "calendar_action_main_plan_delete" || "calendar_action_edit_ical_import") {
    		$params = array("plan_id" => $plan_params["plan_id"]);
	    	$count = $this->_db->countExecute("calendar_plan", $params);
			if ($count == 0) {
				$result = $whatsnewAction->delete($plan_params["plan_id"]);
			}
		}
		return $result;
		//--新着情報関連 End--
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
			$not_enroll_room = $this->_session->getParameter(array("calendar", "not_enroll_room", $block_id));
			$enroll_room = $this->_session->getParameter(array("calendar", "enroll_room", $block_id));
			if (!isset($not_enroll_room) && !isset($enroll_room)) {
				$enroll_room = array($this->_session->getParameter("_main_room_id"));
			}

	    	$calendar_block = $this->_calendarView->getBlock();
	    	if (!$calendar_block) {
	    		return false;
	    	}
	    	if (!empty($calendar_block["select_room_list"]) && !empty($not_enroll_room)) {
	    		foreach ($not_enroll_room as $i=>$room_id) {
	    			if (in_array($room_id, $calendar_block["select_room_list"])) {
						$params = array(
							"block_id" => $block_id,
							"room_id" => $room_id
						);
						$result = $this->_db->deleteExecute("calendar_select_room", $params);
						if (!$result) {
							return false;
						}
	    			}
	    		}
	    	}
	    	if (!empty($enroll_room)) {
	    		foreach ($enroll_room as $i=>$room_id) {
	    			if (empty($calendar_block["select_room_list"]) || !in_array($room_id, $calendar_block["select_room_list"])) {
						$params = array(
							"block_id" => $block_id,
							"room_id" => $room_id
						);
						$result = $this->_db->insertExecute("calendar_select_room", $params);
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
			$result = $this->_db->deleteExecute("calendar_select_room", $params);
			if (!$result) {
				return false;
			}
		}
        return true;
	}
	/**
	 * 予定と新着を一度全て削除。
	 *
	 * @access	public
	 */
	function deletePlanAll($plan_room_id) {
		$params = array(
				"room_id" => $plan_room_id
		);
		$allplan = $this->_db->selectExecute("calendar_plan", $params);
	    $result = $this->_db->deleteExecute("calendar_plan", $params);
		if (!$result) {	return false;}
		$result = $this->_db->deleteExecute("calendar_plan_details", $params);
		if (!$result) {	return false;}

		foreach ($allplan as $plan) {
			$result = $this->setWhatsnew($plan);
			if (!$result) { return false; }
		}

 		return true;

	}
}
?>