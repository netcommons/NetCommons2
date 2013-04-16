<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレンダー取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Components_View
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
	 * @var デフォルト値を保持
	 *
	 * @access	private
	 */
	var $_default = null;

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
	 * @var _user_idを保持
	 *
	 * @access	private
	 */
	var $_user_id = null;

	/**
	 * @var _allow_plan_flagを保持
	 *
	 * @access	private
	 */
	var $_allow_plan_flag = null;

	/**
	 * @var 週KEYを保持
	 *
	 * @access	private
	 */
	var $_wday_array = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Calendar_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_user_id = $this->_session->getParameter("_user_id");
		$this->_wday_array = array("SU","MO","TU","WE","TH","FR","SA");
	}
	/**
	 * 時間の差を計算する
	 *
	 * @access	private
	 */
	function _TimeDiff($from_time, $to_time)
	{
		$dateTimeBegin = mktime(substr($from_time,0,2), substr($from_time,2,2), substr($from_time,4));
		$dateTimeEnd  = mktime(substr($to_time,0,2), substr($to_time,2,2), substr($to_time,4));

		$diff = $dateTimeEnd - $dateTimeBegin;
		if ($diff < 0) {
			# error condition
			return false;
		}
		return round($diff / 3600, 2);
	}
	/**
	 * 日付フォーマットする
	 *
	 * @access	public
	 */
	function dateFormat($time, $timezone_offset=null, $insert_flag=false, $timeFormat="YmdHis", $to_flag=false)
	{
		if (isset($timezone_offset)) {
			$timezone_minute_offset = 0;
			if(round($timezone_offset) != intval($timezone_offset)) {
				$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
				$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
			}
			if ($insert_flag) {
				$timezone_offset = -1 * $timezone_offset;
			}
			$time = date("YmdHis", mktime(intval(substr($time, 8, 2)) + $timezone_offset, intval(substr($time, 10, 2)) + $timezone_minute_offset, intval(substr($time, 12, 2)),
							intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4))));
		} else {
			$time = timezone_date($time, $insert_flag, "YmdHis");
		}
		if ($to_flag && substr($time, 8) == "000000") {
			$timeFormat = str_replace("H", "24", $timeFormat);
			$timeFormat = str_replace("is", "0000", $timeFormat);
			$timeFormat = str_replace("i", "00", $timeFormat);
			$timestamp = mktime(0,0,0,
						intval(substr($time, 4, 2)),intval(substr($time, 6, 2)),intval(substr($time, 0, 4)));
			$timestamp = $timestamp - 1;
		} else {
			$timestamp = mktime(intval(substr($time, 8, 2)),intval(substr($time, 10, 2)),intval(substr($time, 12, 2)),
						intval(substr($time, 4, 2)),intval(substr($time, 6, 2)),intval(substr($time, 0, 4)));
		}
		if (!defined("CALENDAR_WEEK")) {
			$weekNameArray = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
		} else {
			$weekNameArray = explode("|", CALENDAR_WEEK);
		}
		$week = date("w", $timestamp);
		return date(sprintf($timeFormat, $weekNameArray[$week]), $timestamp);
	}

	/**
	 * カレンダー管理取得
	 *
	 * @access	public
	 */
	function &getManage($room_id=null)
	{
		$room_id_arr = $this->_request->getParameter("room_id_arr");
		if ($this->_user_id != "") {
			$room_id_arr[] = CALENDAR_ALL_MEMBERS_ID;
		}
		$params = isset($room_id) ? array("room_id"=>$room_id) : array("room_id IN (".implode(",", $room_id_arr).")"=>null);

		$result = $this->_db->selectExecute("calendar_manage", $params, null, null, null, array($this,"_callbackManage"));
        return $result;
	}
	/**
	 * カレンダー管理取得
	 *
	 * @access	private
	 */
	function &_callbackManage(&$recordSet)
	{
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			$ret[$row["room_id"]] = $row;
		}
		return $ret;
	}

	/**
	 * 新着で表示するブロックIDを取得
	 *
	 * @access	public
	 */
	function getBlockIdByWhatsnew()
	{
		$plan_room_id = $this->_request->getParameter("plan_room_id");
		if (!is_null($plan_room_id) || $plan_room_id != 0) {
			$params = array("room_id"=>$plan_room_id);
			$result = $this->_db->selectExecute("calendar_block", $params, null, 1);
			if (!empty($result)) {
				return $result[0]["block_id"];
			}
		}
		$params = array("room_id"=>_SPACE_TYPE_PUBLIC);
		$result = $this->_db->selectExecute("calendar_block", $params, null, 1);
		if (!empty($result)) {
			return $result[0]["block_id"];
		}
		return $this->_request->getParameter("block_id");
	}

	/**
	 * 予定取得
	 *
	 * @access	public
	 */
	function &getCalendar($calendar_id)
	{
		$room_id_arr = $this->_request->getParameter("room_id_arr");
		if (!empty($this->_user_id)) {
			$room_id_arr[] = CALENDAR_ALL_MEMBERS_ID;
		}

		$calendar_block = $this->getBlock();
		if (empty($calendar_block)) {
			$calendar_block = false;
			return $calendar_block;
		}

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($calendar_block["select_room"] == _ON && strpos(strtolower($actionName), "calendar_view") !== false && empty($calendar_block["select_room_list"])) {
			$room_id_arr = empty($calendar_block["select_room_list"]) ? array() : array_intersect($room_id_arr, $calendar_block["select_room_list"]);
		}
		if (empty($room_id_arr)) {
			$result = array();
			return $result;
		}

		$sql = "SELECT plan.*, " .
					"details.location, details.contact, details.description, details.rrule, " .
					"page.private_flag, page.space_type, page.page_name".
				" FROM {calendar_plan} plan";
		$sql .= " INNER JOIN {calendar_plan_details} details ".
					"ON (plan.plan_id = details.plan_id)".
				" LEFT JOIN {pages} page " .
					"ON (plan.room_id = page.page_id)";
		$sql .= " WHERE plan.calendar_id = ?";
		$sql .= " AND plan.room_id IN (".implode(",",$room_id_arr).")";

		$params = array(
			"calendar_id" => $calendar_id
		);
        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackCalendar"));

		if ($result === false && $actionName != "calendar_view_main_init") {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * カレンダー管理取得
	 *
	 * @access	private
	 */
	function &_callbackCalendar(&$recordSet)
	{
		$mobile_flag = $this->_session->getParameter("_mobile_flag");

		$commonMain =& $this->_container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");
		$sess_timezone_offset = $this->_session->getParameter("_timezone_offset");

		$row = $recordSet->fetchRow();
		if (empty($row)) {
			$row = false;
			return $row;
		}
		if ($row["space_type"] == _SPACE_TYPE_PUBLIC) {
			$row["plan_flag"] = CALENDAR_PLAN_PUBLIC;
		} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _ON) {
			$row["plan_flag"] = CALENDAR_PLAN_PRIVATE;
		} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _OFF) {
			$row["plan_flag"] = CALENDAR_PLAN_GROUP;
		} else {
			$row["plan_flag"] = CALENDAR_PLAN_MEMBERS;
			$row["page_name"] = CALENDAR_ALL_MEMBERS_LANG;
		}

		$row["hasModify"] = $this->hasEditAuthority($row);

		if ($mobile_flag == _ON) {
			$row["start_date_str"] = $this->dateFormat($row["start_date"].$row["start_time"], null, false, CALENDAR_MOBILE_DATE_FORMAT, false);
			$row["end_date_str"] = $this->dateFormat($row["end_date"].$row["end_time"], null, false, CALENDAR_MOBILE_DATE_FORMAT, true);
			$row["start_time_str"] = $this->dateFormat($row["start_date"].$row["start_time"], null, false, CALENDAR_TIME_FORMAT, false);
			$row["end_time_str"] = $this->dateFormat($row["end_date"].$row["end_time"], null, false, CALENDAR_TIME_FORMAT, true);
		} else {
			if ($row["allday_flag"] == _ON && $row["timezone_offset"] == $sess_timezone_offset) {
				$row["start_time_str"] = $this->dateFormat($row["start_date"].$row["start_time"], null, false, CALENDAR_DATE_FORMAT, false);
				$row["end_time_str"] = $this->dateFormat($row["end_date"].$row["end_time"], null, false, CALENDAR_DATE_FORMAT, true);
			} else {
				$row["start_time_str"] = $this->dateFormat($row["start_date"].$row["start_time"], null, false, CALENDAR_DATE_FORMAT." ".CALENDAR_TIME_FORMAT, false);
				$row["end_time_str"] = $this->dateFormat($row["end_date"].$row["end_time"], null, false, CALENDAR_DATE_FORMAT." ".CALENDAR_TIME_FORMAT, true);
			}
		}

		$row["timezone_offset_key"] = $timezoneMain->getLangTimeZone($row["timezone_offset"], false);

		$row["input_start_date"] = $this->dateFormat($row["start_date"].$row["start_time"], $row["timezone_offset"], false, _INPUT_DATE_FORMAT, false);
		$row["input_end_date"] = $this->dateFormat($row["end_date"].$row["end_time"], $row["timezone_offset"], false, _INPUT_DATE_FORMAT, true);

		$row["db_start_date"] = $row["start_date"];
		$row["db_start_time"] = $row["start_time"];

		$start_time = $this->dateFormat($row["start_date"].$row["start_time"], $row["timezone_offset"], false, "YmdHis", false);
		$row["start_date"] = substr($start_time, 0, 8);
		$row["start_time"] = substr($start_time, 8);

		$row["db_end_date"] = $row["end_date"];
		$row["db_end_time"] = $row["end_time"];

		$end_time = $this->dateFormat($row["end_date"].$row["end_time"], $row["timezone_offset"], false, "YmdHis", true);
		$row["end_date"] = substr($end_time, 0, 8);
		$row["end_time"] = substr($end_time, 8);

		$row["link_action_name"] = str_replace(array("{link_id}"), array($row["link_id"]), $row["link_action_name"]);

		return $row;
	}

	/**
	 * 予定区分の使用許可のデフォルト値を取得
	 *
	 * @access	public
	 */
	function getAllowPlanList()
	{
		$container =& DIContainerFactory::getContainer();
		$getdata =& $this->_container->getComponent("GetData");
		$modules = $getdata->getParameter("modules");
//    	if (isset($modules["calendar"])) {
//			$module_id = $modules["calendar"]["module_id"];
//    	} else {
//    		$modulesView =& $this->_container->getComponent("modulesView");
//			$module_obj = $modulesView->getModuleByDirname("calendar");
//			$module_id = $module_obj["module_id"];
//    	}
		$module_id = $this->_request->getParameter("module_id");

		$this->_allow_plan_flag = array();
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return false;
    	}
    	$this->_allow_plan_flag[CALENDAR_PLAN_PRIVATE] = $config['calendar_plan_private']['conf_value'];
    	if (defined($this->_allow_plan_flag[CALENDAR_PLAN_PRIVATE])) {
    		$this->_allow_plan_flag[CALENDAR_PLAN_PRIVATE] = constant($this->_allow_plan_flag[CALENDAR_PLAN_PRIVATE]);
    	}
    	$this->_allow_plan_flag[CALENDAR_PLAN_GROUP] = $config['calendar_plan_group']['conf_value'];
    	if (defined($this->_allow_plan_flag[CALENDAR_PLAN_GROUP])) {
    		$this->_allow_plan_flag[CALENDAR_PLAN_GROUP] = constant($this->_allow_plan_flag[CALENDAR_PLAN_GROUP]);
    	}
    	$this->_allow_plan_flag[CALENDAR_PLAN_MEMBERS] = $config['calendar_plan_members']['conf_value'];
    	if (defined($this->_allow_plan_flag[CALENDAR_PLAN_MEMBERS])) {
    		$this->_allow_plan_flag[CALENDAR_PLAN_MEMBERS] = constant($this->_allow_plan_flag[CALENDAR_PLAN_MEMBERS]);
    	}
    	$this->_allow_plan_flag[CALENDAR_PLAN_PUBLIC] = $config['calendar_plan_public']['conf_value'];
    	if (defined($this->_allow_plan_flag[CALENDAR_PLAN_PUBLIC])) {
    		$this->_allow_plan_flag[CALENDAR_PLAN_PUBLIC] = constant($this->_allow_plan_flag[CALENDAR_PLAN_PUBLIC]);
    	}
		return $this->_allow_plan_flag;
	}

	/**
	 * 予定区分の使用許可のデフォルト値を取得
	 *
	 * @access	public
	 */
	function getAllowPlan($plan_flag)
	{
		if (!isset($this->_allow_plan_flag)) {
			$this->_allow_plan_flag = $this->getAllowPlanList();
		}
		return $this->_allow_plan_flag[$plan_flag];
	}

	/**
	 * デフォルト値取得
	 *
	 * @access	public
	 */
	function getDefaultBlock()
	{
		if (!empty($this->_default)) {
			return $this->_default;
		}

		$configView =& $this->_container->getComponent("configView");
    	$getdata =& $this->_container->getComponent("GetData");
//    	$modules = $getdata->getParameter("modules");
//    	if (isset($modules["calendar"])) {
//	    	$module_obj = $modules["calendar"];
//    	} else {
//    		$modulesView =& $this->_container->getComponent("modulesView");
//			$module_obj = $modulesView->getModuleByDirname("calendar");
//    	}
		$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
//		$config = $configView->getConfig($module_obj["module_id"], false);
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return false;
    	}
    	$ret = array();
    	if (defined($config['display_type']['conf_value'])) {
    		$display_type = constant($config['display_type']['conf_value']);
    	} else {
    		$display_type = $config['display_type']['conf_value'];
    	}
    	if (defined($config['start_pos_yearly']['conf_value'])) {
    		$start_pos_yearly = constant($config['start_pos_yearly']['conf_value']);
    	} else {
    		$start_pos_yearly = $config['start_pos_yearly']['conf_value'];
    	}
    	if (defined($config['start_pos_weekly']['conf_value'])) {
    		$start_pos_weekly = constant($config['start_pos_weekly']['conf_value']);
    	} else {
    		$start_pos_weekly = $config['start_pos_weekly']['conf_value'];
    	}
    	if (defined($config['mail_send']['conf_value'])) {
    		$mail_send = constant($config['mail_send']['conf_value']);
    	} else {
    		$mail_send = $config['mail_send']['conf_value'];
    	}

    	switch ($display_type) {
    		case CALENDAR_YEARLY:
    			$start_pos = $start_pos_yearly;
    			break;
    		case CALENDAR_WEEKLY:
    		case CALENDAR_T_SCHEDULE:
    		case CALENDAR_U_SCHEDULE:
    			$start_pos = $start_pos_weekly;
    			break;
    		default:
    			$start_pos = "0";
    	}
    	if (defined($config['display_count']['conf_value'])) {
    		$display_count = constant($config['display_count']['conf_value']);
    	} else {
    		$display_count = $config['display_count']['conf_value'];
    	}

    	if (defined($config['show_help']['conf_value'])) {
    		$show_help = constant($config['show_help']['conf_value']);
    	} else {
    		$show_help = intval($config['show_help']['conf_value']);
    	}

    	if (defined($config['time_visible']['conf_value'])) {
    		$time_visible = constant($config['time_visible']['conf_value']);
    	} else {
    		$time_visible = intval($config['time_visible']['conf_value']);
    	}

    	$this->_default = array(
    		"block_id" => 0,
    		"display_type" => $display_type,
    		"start_pos" => $start_pos,
    		"start_pos_yearly" => $start_pos_yearly,
    		"start_pos_weekly" => $start_pos_weekly,
    		"display_count" => $display_count,
			"select_room" => _OFF,
			"myroom_flag" => _OFF,
    		"show_help" => $show_help,
    		"schedule_open" => $config['schedule_open']['conf_value'],
    		"time_visible" => $time_visible,
    		"daily_time_height" => $config['daily_time_height']['conf_value'],
    		"daily_start_time" => $config['daily_start_time']['conf_value'],
    		"mail_send" => $mail_send,
    		"myroom_use_flag" => _OFF
    	);

    	$authoritiesView =& $this->_container->getComponent("authoritiesView");
    	$role_auth_id = $this->_session->getParameter("_role_auth_id");
    	$authorities =& $authoritiesView->getAuthorityById($role_auth_id);
		if($authorities === false || !isset($authorities['user_authority_id'])) return $this->_default;

		$this->_default["myroom_use_flag"] = $authorities['myroom_use_flag'];

    	return $this->_default;
	}

	/**
	 * デフォルト値取得
	 *
	 * @access	public
	 */
	function getBlock($display_type=null)
	{
		$calendar_block = $this->_request->getParameter("calendar_block");
		if (!empty($calendar_block)) {
			return $calendar_block;
		}

		$default = $this->getDefaultBlock();
		$block_id = $this->_request->getParameter("block_id");

    	$result =& $this->_db->selectExecute("calendar_block", array("block_id"=>$block_id));
        if (empty($result)) {
        	$result[0] = array();
        }
		$block = array_merge($default, $result[0]);
    	switch ($block["display_type"]) {
    		case CALENDAR_YEARLY:
    			$block["start_pos_yearly"] = $block["start_pos"];
    			break;
    		case CALENDAR_WEEKLY:
    		case CALENDAR_T_SCHEDULE:
    		case CALENDAR_U_SCHEDULE:
    			$block["start_pos_weekly"] = $block["start_pos"];
    			break;
    	}
		if (isset($display_type) && $display_type > 0) {
			$block["display_type"] = $display_type;
		}

		if ($block["select_room"] == _ON) {
			$sql = "SELECT room_id" .
					" FROM {calendar_select_room}" .
					" WHERE block_id = ?";
			$params = array("block_id" => $block_id);
	        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackSelectRoom"));
			if ($result === false) {
		       	$this->_db->addError();
		       	return $result;
			}
			$block["select_room_list"] = $result;
		} else {
			$block["myroom_flag"] = _OFF;
		}

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($block["select_room"] == _OFF && strpos(strtolower($actionName), "calendar_view") !== false && empty($block["select_room_list"])) {
			$block["select_room_list"] = array($this->_session->getParameter("_main_room_id"));
		}
    	if (!isset($block["block_id"])) {
    		$block["block_id"] = _OFF;
    	}

    	return $block;
	}

	/**
	 * ブロックのデータを取得
	 *
	 * @access	private
	 */
	function _callbackSelectRoom(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[] = $row["room_id"];
		}
		return $result;
	}

	/**
	 * 予定データ件数取得
	 *
	 * @access	public
	 */
	function getPlanCountByDate($from_date, $to_date=null, $display_type=0)
	{
		if (!isset($to_date)) {
			$to_date = $from_date;
		}

		$room_id_arr = $this->_request->getParameter("room_id_arr");
		if (!empty($this->_user_id)) {
			$room_id_arr[] = CALENDAR_ALL_MEMBERS_ID;
		}

		$calendar_block = $this->getBlock();
		if (empty($calendar_block)) {
			$calendar_block = false;
			return $calendar_block;
		}
		if ($calendar_block["select_room"] == _ON) {
			$room_id_arr =  empty($calendar_block["select_room_list"]) ? array() : array_intersect($room_id_arr, $calendar_block["select_room_list"]);
		}
		if (empty($room_id_arr)) {
			$result = array();
			return $result;
		}

		$sql = "SELECT plan.room_id, plan.start_date, plan.start_time, plan.end_date, plan.end_time, plan.allday_flag, " .
						"page.private_flag, page.space_type, COUNT(*) AS cnt " .
				"FROM {calendar_plan} plan " .
				"LEFT JOIN {pages} page ON (plan.room_id=page.page_id) ";
		$sql .= "WHERE plan.room_id IN (". implode(",",$room_id_arr) .") ";
		$sql .= "AND ((plan.start_date >= ? AND plan.start_date <= ?) " .
					"OR (plan.end_date >= ? AND plan.end_date <= ?) " .
					"OR (plan.start_date <= ? AND plan.end_date >= ?)) ";
		$sql .= "GROUP BY plan.room_id, plan.start_date, plan.end_date, plan.allday_flag ";
		$sql .= "ORDER BY plan.allday_flag DESC, plan.start_date, plan.start_time, plan.end_date, plan.end_time, plan.plan_id";

		$from_date = timezone_date($from_date."000000", true, "Ymd");
		$to_date = timezone_date($to_date."240000", true, "Ymd");
		$params = array(
			"from_date0" => $from_date,
			"to_date0" => $to_date,
			"from_date1" => $from_date,
			"to_date1" => $to_date,
			"from_date2" => $from_date,
			"from_date3" => $from_date
		);
        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlanCount"), array($display_type));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * 予定データ件数取得
	 *
	 * @access	private
	 */
	function &_callbackPlanCount(&$recordSet, $params)
	{
		$ret = array();
		$display_type = $params[0];
		if ($display_type == CALENDAR_YEARLY) {
			$format = "Ym";
		} else {
			$format = "Ymd";
		}
		while ($row = $recordSet->fetchRow()) {
			$start_time = timezone_date($row["start_date"].$row["start_time"], false, "YmdHis");
			$row["start_date"] = substr($start_time, 0, 8);
			$row["start_time"] = substr($start_time, 8);
			$start_timestamp = mktime(substr($start_time,8,2),substr($start_time,10,2),substr($start_time,12,2),
									substr($start_time,4,2),substr($start_time,6,2),substr($start_time,0,4));

			$end_time = timezone_date($row["end_date"].$row["end_time"], false, "YmdHis");
			$row["end_date"] = substr($end_time, 0, 8);
			$row["end_time"] = substr($end_time, 8);
			$end_timestamp = mktime(substr($end_time,8,2),substr($end_time,10,2),substr($end_time,12,2),
									substr($end_time,4,2),substr($end_time,6,2),substr($end_time,0,4));

			if ($row["end_time"] != "000000" && $row["start_time"] > $row["end_time"]) {
				$num = intval(($end_timestamp+86400 - $start_timestamp) / 86400);
			} elseif ($row["end_time"] == "000000") {
				$num = intval(($end_timestamp-1 - $start_timestamp) / 86400);
			} else {
				$num = intval(($end_timestamp - $start_timestamp) / 86400);
			}
			for ($i=0; $i<=$num; $i++) {
				if ($i == 0) {
					$date = date($format, $start_timestamp);
				} else {
					$date = date($format, $start_timestamp + $i * 86400);
				}
				if (!isset($ret[$date])) {
					$ret[$date] = array(
						"total" => 0,
						"public" => 0,
						"members" => 0,
						"group" => 0,
						"private" => 0
					);
				}
				$ret[$date]["total"] += $row["cnt"];
				if ($row["space_type"] == _SPACE_TYPE_PUBLIC) {
					$ret[$date]["public"] += $row["cnt"];
				} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _ON) {
					$ret[$date]["private"] += $row["cnt"];
				} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _OFF) {
					$ret[$date]["group"] += $row["cnt"];
				} elseif ($row["room_id"] == CALENDAR_ALL_MEMBERS_ID) {
					$ret[$date]["members"] += $row["cnt"];
				}
			}
		}
		return $ret;
	}

	/**
	 * 予定データ取得
	 *
	 * @access	public
	 */
	function getPlanByDate($from_date, $to_date=null, $display_type=0)
	{
		if (!isset($to_date)) {
			$to_date = $from_date;
		}

		$room_id_arr = $this->_request->getParameter("room_id_arr");
		if (!empty($this->_user_id)) {
			$room_id_arr[] = CALENDAR_ALL_MEMBERS_ID;
		}

		$calendar_block = $this->getBlock();
		if (empty($calendar_block)) {
			$calendar_block = false;
			return $calendar_block;
		}
		if ($calendar_block["select_room"] == _ON) {
			$room_id_arr =  empty($calendar_block["select_room_list"]) ? array() : array_intersect($room_id_arr, $calendar_block["select_room_list"]);
		}
		if (empty($room_id_arr)) {
			$result = array();
			return $result;
		}

		$mobile_flag = $this->_session->getParameter("_mobile_flag");

		$sql = "SELECT plan.*, page.private_flag, page.space_type " .
				"FROM {calendar_plan} plan " .
				"LEFT JOIN {pages} page ON (plan.room_id=page.page_id) ";
		$sql .= "WHERE plan.room_id IN (". implode(",",$room_id_arr) .") ";
		$sql .= "AND ((plan.start_date >= ? AND plan.start_date <= ?) " .
					"OR (plan.end_date >= ? AND plan.end_date <= ?) " .
					"OR (plan.start_date <= ? AND plan.end_date >= ?)) ";

    	switch ($display_type) {
    		case CALENDAR_U_SCHEDULE:
				$sql .= "ORDER BY plan.user_id, plan.start_date, plan.end_date, plan.allday_flag DESC, plan.start_time, plan.end_time, plan.plan_id";
				break;
    		case CALENDAR_WEEKLY:
    			if ($mobile_flag == _OFF) {
					$sql .= "ORDER BY plan.room_id, plan.start_date, plan.end_date, plan.allday_flag DESC, plan.start_time, plan.end_time, plan.plan_id";
					break;
    			}
    		case CALENDAR_YEARLY:
    		case CALENDAR_S_MONTHLY:
    		case CALENDAR_L_MONTHLY:
    		case CALENDAR_DAILY:
    		case CALENDAR_T_SCHEDULE:
    		default:
				$sql .= "ORDER BY plan.allday_flag DESC, plan.start_date, plan.start_time, plan.end_date, plan.end_time, plan.plan_id";
    	}

		$from_date = timezone_date($from_date."000000", true, "Ymd");
		$to_date = timezone_date($to_date."240000", true, "Ymd");
		$params = array(
			"from_date0" => $from_date,
			"to_date0" => $to_date,
			"from_date1" => $from_date,
			"to_date1" => $to_date,
			"from_date2" => $from_date,
			"from_date3" => $from_date
		);

    	switch ($display_type) {
    		case CALENDAR_U_SCHEDULE:
	        	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlan"), array("user"));
				break;
    		case CALENDAR_WEEKLY:
    			if ($mobile_flag == _ON) {
		        	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlan"), array("mobile_weekly"));
    			} else {
		        	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlan"), array("weekly"));
    			}
				break;
    		case CALENDAR_DAILY:
    			if ($mobile_flag == _ON) {
		        	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlan"), array("mobile_daily"));
    			} else {
		        	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlan"), array("daily"));
    			}
				break;
    		case CALENDAR_YEARLY:
    		case CALENDAR_S_MONTHLY:
    		case CALENDAR_L_MONTHLY:
    		case CALENDAR_DAILY:
    		case CALENDAR_T_SCHEDULE:
    		default:
	        	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackPlan"));
    	}
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * 予定データ取得
	 *
	 * @access	private
	 */
	function &_callbackPlan(&$recordSet, $params=null)
	{
		$ret = array();
		$as_key = !empty($params) ? $params[0] : null;
		$sess_timezone_offset = $this->_session->getParameter("_timezone_offset");

		while ($row = $recordSet->fetchRow()) {
			$start_time = timezone_date($row["start_date"].$row["start_time"], false, "YmdHis");
			$row["start_date"] = substr($start_time, 0, 8);
			$row["start_time"] = substr($start_time, 8);
			$start_timestamp = mktime(substr($start_time,8,2),substr($start_time,10,2),substr($start_time,12,2),
									substr($start_time,4,2),substr($start_time,6,2),substr($start_time,0,4));

			$end_time = timezone_date($row["end_date"].$row["end_time"], false, "YmdHis");
			$row["end_date"] = substr($end_time, 0, 8);
			$row["end_time"] = substr($end_time, 8);
			$end_timestamp = mktime(substr($end_time,8,2),substr($end_time,10,2),substr($end_time,12,2),
									substr($end_time,4,2),substr($end_time,6,2),substr($end_time,0,4));

			if ($row["end_time"] != "000000" && $row["start_time"] > $row["end_time"]) {
				$num = intval(($end_timestamp+86400 - $start_timestamp) / 86400);
			} elseif ($row["end_time"] == "000000") {
				$num = intval(($end_timestamp-1 - $start_timestamp) / 86400);
			} else {
				$num = intval(($end_timestamp - $start_timestamp) / 86400);
			}
			if ($row["space_type"] == _SPACE_TYPE_PUBLIC) {
				$row["plan_flag"] = CALENDAR_PLAN_PUBLIC;
			} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _ON) {
				$row["plan_flag"] = CALENDAR_PLAN_PRIVATE;
			} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _OFF) {
				$row["plan_flag"] = CALENDAR_PLAN_GROUP;
			} elseif ($row["room_id"] == CALENDAR_ALL_MEMBERS_ID) {
				$row["plan_flag"] = CALENDAR_PLAN_MEMBERS;
			}
			for ($i=0; $i<=$num; $i++) {
				if ($i == 0) {
					$date = date("Ymd", $start_timestamp);
					$row["start_time"] = date("His", $start_timestamp);
					$row["start_time_str"] = date(CALENDAR_TIME_FORMAT, $start_timestamp);
				} else {
					$date = date("Ymd", $start_timestamp + $i * 86400);
					$row["start_time"] = date("His", mktime(0,0,0));
					$row["start_time_str"] = date(CALENDAR_TIME_FORMAT, mktime(0,0,0));
				}

				if ($i == $num) {
					if (date("Hi",$end_timestamp+1) == "0000") {
						$timeFormat = "240000";
						$timeStrFormat = str_replace("H", "24", CALENDAR_TIME_FORMAT);
						$timeStrFormat = str_replace("i", "00", $timeStrFormat);
					} else {
						$timeFormat = "His";
						$timeStrFormat = CALENDAR_TIME_FORMAT;
					}
					$row["end_time"] = date($timeFormat, $end_timestamp);
					$row["end_time_str"] = date($timeStrFormat, $end_timestamp);
				} else {
					$row["end_time"] = "240000";
					$timeStrFormat = str_replace("H", "24", CALENDAR_TIME_FORMAT);
					$timeStrFormat = str_replace("i", "00", $timeStrFormat);
					$row["end_time_str"] = date($timeStrFormat, mktime(0,0,0));
				}

				$row["start_date"] = $date;
				$row["end_date"] = $date;

				$title = $row["title"];
				$row["short_title"] = mb_substr($title, 0, CALENDAR_SHORT_STRLEN, INTERNAL_CODE);
				if ($row["short_title"] != $title) {
					$row["short_title"] .= _SEARCH_MORE;
				}

				switch ($as_key) {
					case "user":
						if (!isset($ret[$date])) {
							$ret[$date] = array();
						}
						if (!isset($ret[$date][$row["user_id"]])) {
							$ret[$date][$row["user_id"]] = array();
						}
						$ret[$date][$row["user_id"]][] = $row;
						break;
					case "weekly":
						if (!isset($ret[$row["room_id"]])) {
							$ret[$row["room_id"]] = array();
						}
						if (!isset($ret[$row["room_id"]][$date])) {
							$ret[$row["room_id"]][$date] = array();
						}
						$ret[$row["room_id"]][$date][] = $row;
						break;
					case "daily":
						if (!isset($ret[$date][$row["allday_flag"]])) {
							$ret[$date][$row["allday_flag"]] = array();
						}
						$ret[$date][_ON][] = $row;
						if ($row["allday_flag"] == _OFF || $sess_timezone_offset != $row["timezone_offset"]) {
							$row["allday_flag"] = _OFF;
							$row["top"] = $this->_TimeDiff("000000", $row["start_time"]);
							$row["height"] = $this->_TimeDiff($row["start_time"], $row["end_time"]);
							$row["bottom"] = $this->_TimeDiff("000000", $row["end_time"]);
							$this->__callbackPlanByDaily($ret, $row, $date, 0);
						}
						break;
					default:
						if (!isset($ret[$date])) {
							$ret[$date] = array();
						}
						$ret[$date][] = $row;
				}
			}
		}
		return $ret;
	}
	/**
	 * 予定データ取得
	 *
	 * @access	private
	 */
	function __callbackPlanByDaily(&$ret, &$row, $date, $branch)
	{
		if (!isset($ret[$date][$row["allday_flag"]][$branch])) {
			$ret[$date][$row["allday_flag"]][$branch] = array();
			$ret[$date][$row["allday_flag"]][$branch][] = $row;
			return true;
		} else {
			foreach ($ret[$date][$row["allday_flag"]][$branch] as $i=>$val) {
				if ($val["start_time"] <= $row["start_time"] && $val["end_time"] > $row["start_time"] ||
						$val["start_time"] < $row["end_time"] && $val["end_time"] >= $row["end_time"]) {

					return $this->__callbackPlanByDaily($ret, $row, $date, $branch + 1);
				}
			}
			$ret[$date][$row["allday_flag"]][$branch][] = $row;
			return true;
		}
	}

	/**
	 * 繰返しのデフォルト値
	 *
	 * @access	public
	 */
	function &defaultRRule()
	{
		$date = $this->_request->getParameter("date");
		if (!empty($date)) {
			$date = timezone_date($date."000000", true, "YmdHis");
		} else {
			$date = timezone_date($date, true, "YmdHis");
		}

		$timestamp = mktime(substr($date,8,2), substr($date,10,2), substr($date,12,2),
							substr($date,4,2), substr($date,6,2), substr($date,0,4));

		$result_array = array(
			"FREQ" => "NONE",
			"NONE" => array(),
			"YEARLY" => array("FREQ"=>"YEARLY"),
			"MONTHLY" => array("FREQ"=>"MONTHLY"),
			"WEEKLY" => array("FREQ"=>"WEEKLY"),
			"DAILY" => array("FREQ"=>"DAILY"),
			"COUNT" => 3,
			"UNTIL" => date("YmdHis", $timestamp),
			"UNTIL_VIEW" => timezone_date($date, false, _DATE_FORMAT),
			"REPEAT_COUNT" => _ON,
			"REPEAT_UNTIL" => _OFF
		);

		$wday = timezone_date($date, false, "w");
		$month = timezone_date($date, false, "m");
		$result_array["YEARLY"] = array(
			"INTERVAL" => 1,
			"BYDAY" => array(),
			"BYMONTH" => array(intval($month))
		);
		$result_array["MONTHLY"] = array(
			"INTERVAL" => 1,
			"BYDAY" => array(),
			"BYMONTHDAY" => array()
		);
		$result_array["WEEKLY"] = array(
			"INTERVAL" => 1,
			"BYDAY" => array($this->_wday_array[$wday])
		);
		$result_array["DAILY"] = array(
			"INTERVAL" => 1
		);
        return $result_array;
	}

	/**
	 * パース処理
	 *
	 * @access	public
	 */
	function &parseRRule($rrule_str="", $base_flag=false)
	{
		$result_array = array();
		if ($base_flag) {
			$result_array =& $this->defaultRRule();
		}
		if ($rrule_str == "") {
			return $result_array;
		}
		$matches = array();
		$result = preg_match("/FREQ=(NONE)/", $rrule_str, $matches);
		$result = (!$result ? preg_match("/FREQ=(YEARLY)/", $rrule_str, $matches) : $result);
		$result = (!$result ? preg_match("/FREQ=(MONTHLY)/", $rrule_str, $matches) : $result);
		$result = (!$result ? preg_match("/FREQ=(WEEKLY)/", $rrule_str, $matches) : $result);
		$result = (!$result ? preg_match("/FREQ=(DAILY)/", $rrule_str, $matches) : $result);
		if ($result) {
			$freq = $matches[1];
		} else {
			$freq = "NONE";
		}
		$array = explode(";", $rrule_str);
		foreach ($array as $rrule) {
			list($key, $val) = explode("=", $rrule);
			if ($key == "FREQ" || $key == "COUNT" || $key == "UNTIL") {
				$result_array[$key] = $val;
				if ($key == "UNTIL") {
					if (preg_match("/^([0-9]{8})[^0-9]*([0-9]{6})/i", $val, $matches)) {
						$result_array[$key] = $matches[1].$matches[2];
						$result_array["UNTIL_VIEW"] = $this->dateFormat($matches[1].$matches[2], null, false, _DATE_FORMAT, true);
					}
				}
				if ($key == "COUNT") {
					$result_array["REPEAT_COUNT"] = _ON;
					$result_array["REPEAT_UNTIL"] = _OFF;
				}
				if ($key == "UNTIL") {
					$result_array["REPEAT_COUNT"] = _OFF;
					$result_array["REPEAT_UNTIL"] = _ON;
				}
				continue;
			}
			if ($key == "INTERVAL") {
				$result_array[$freq][$key] = intval($val);
				continue;
			}
			$result_array[$freq][$key] = explode(",", $val);
		}
        return $result_array;
	}
	/**
	 * 文言にする処理
	 *
	 * @access	public
	 */
	function stringRRule($rrule)
	{
		$this->_wdayname_array = explode("|", CALENDAR_RRULE_WDAY);
		$result_str = "";
		if (!is_array($rrule)) {
			$rrule = $this->parseRRule($rrule);
			if (empty($rrule)) { return ""; }
		}
		$freq = $rrule["FREQ"];
		if (!isset($rrule[$freq])) {
			return "";
		}

		$bymonth_str = "";
		if (isset($rrule[$freq]["BYMONTH"])) {
			foreach ($rrule[$freq]["BYMONTH"] as $i=>$val) {
				$bymonth_str .= CALENDAR_RRULE_PAUSE.sprintf(CALENDAR_RRULE_MONTH, $val);
			}
		}

		$byday_str = "";
		if (isset($rrule[$freq]["BYDAY"])) {
			foreach ($rrule[$freq]["BYDAY"] as $i=>$val) {
				$w = substr($val, -2);
				$n = intval(substr($val, 0, -2));
				$index = array_search($w, $this->_wday_array);
				if ($index !== false && $index !== null) {
					$w_name = $this->_wdayname_array[$index];
				} else {
					continue;
				}
				if ($freq == "WEEKLY") {
					$byday_str .= CALENDAR_RRULE_PAUSE;
				} else {
					switch ($n) {
						case 1:
							$byday_str .= ($freq == "MONTHLY" ? CALENDAR_RRULE_PAUSE : "<br />"). CALENDAR_RRULE_WEEK_FIRST;
							break;
						case 2:
							$byday_str .= ($freq == "MONTHLY" ? CALENDAR_RRULE_PAUSE : "<br />"). CALENDAR_RRULE_WEEK_SECOND;
							break;
						case 3:
							$byday_str .= ($freq == "MONTHLY" ? CALENDAR_RRULE_PAUSE : "<br />"). CALENDAR_RRULE_WEEK_THIRD;
							break;
						case 4:
							$byday_str .= ($freq == "MONTHLY" ? CALENDAR_RRULE_PAUSE : "<br />"). CALENDAR_RRULE_WEEK_FOURTH;
							break;
						default:
							$byday_str .= ($freq == "MONTHLY" ? CALENDAR_RRULE_PAUSE : "<br />"). CALENDAR_RRULE_WEEK_END;
					}
				}
				$byday_str .= $w_name;
			}
		}

		$bymonthday_str = "";
		if (isset($rrule[$freq]["BYMONTHDAY"])) {
			foreach ($rrule[$freq]["BYMONTHDAY"] as $i=>$val) {
				$bymonthday_str .= CALENDAR_RRULE_PAUSE. sprintf(CALENDAR_RRULE_DAY, $val);
			}
		}

		switch ($freq) {
			case "NONE":
				$result_str .= CALENDAR_RRULE_NONE;
				break;
			case "YEARLY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= CALENDAR_RRULE_EVERY_YEAR;
				} else {
					$result_str .= sprintf(CALENDAR_RRULE_INTERVAL_YEAR, $rrule[$freq]["INTERVAL"]);
				}
				$result_str .= $bymonth_str;
				if ($byday_str == "") {
					$byday_str = "<br />".CALENDAR_RRULE_STARTDATE;
				}
				$result_str .= $byday_str;
				break;
			case "MONTHLY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= CALENDAR_RRULE_EVERY_MONTH;
				} else {
					$result_str .= sprintf(CALENDAR_RRULE_INTERVAL_MONTH, $rrule[$freq]["INTERVAL"]);
				}
				$result_str .= $byday_str;
				$result_str .= $bymonthday_str;
				break;
			case "WEEKLY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= CALENDAR_RRULE_EVERY_WEEK;
				} else {
					$result_str .= sprintf(CALENDAR_RRULE_INTERVAL_WEEK, $rrule[$freq]["INTERVAL"]);
				}
				$result_str .= $byday_str;
				break;
			case "DAILY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= CALENDAR_RRULE_EVERY_DAY;
				} else {
					$result_str .= sprintf(CALENDAR_RRULE_INTERVAL_DAY, $rrule[$freq]["INTERVAL"]);
				}
				break;
			default:
		}

		if (isset($rrule["UNTIL"])) {
			$result_str .= "<br />";
			$result_str .= $this->dateFormat(substr($rrule["UNTIL"],0,8).substr($rrule["UNTIL"],-6), null, false, CALENDAR_RRULE_UNTIL, true);
		} elseif (isset($rrule["COUNT"])) {
			$result_str .= "<br />";
			$result_str .= sprintf(CALENDAR_RRULE_COUNT, $rrule["COUNT"]);
		}
        return $result_str;
	}

	/**
	 * 追加権限チェック
	 *
	 * @access	private
	 */
	function hasAddAuthority($room_id)
	{
		if ($room_id == CALENDAR_ALL_MEMBERS_ID) {
			$page_authority_id = $this->_session->getParameter("_user_auth_id");
		} else {
			$authCheck =& $this->_container->getComponent("authCheck");
			$page_authority_id = $authCheck->getPageAuthId($this->_user_id, $room_id);
			if ($page_authority_id == _AUTH_CHIEF) {
				$page_authority_id = _AUTH_ADMIN;
			}
		}
		if ($page_authority_id >= _AUTH_ADMIN) {
			return _ON;
		}

		$manage_list = $this->_request->getParameter("manage_list");
		if (isset($manage_list[$room_id]) && $page_authority_id >= $manage_list[$room_id]["add_authority_id"]) {
			return _ON;
		}
		return _OFF;
	}

	/**
	 * 編集権限チェック
	 *
	 * @access	public
	 */
	function hasEditAuthority(&$plan)
	{
    	$authCheck =& $this->_container->getComponent("authCheck");

    	if ($plan["link_id"] > 0) {
    		return _OFF;
    	}
    	if ($plan["plan_flag"] == CALENDAR_PLAN_MEMBERS) {
			$user_authority_id = $this->_session->getParameter("_user_auth_id");
			$user_hierarchy = _OFF;
			$plan_hierarchy = _OFF;
		} else {
			$plan_hierarchy = $authCheck->getPageHierarchy($plan["insert_user_id"], $plan["room_id"]);
			$user_hierarchy = $authCheck->getPageHierarchy($this->_user_id, $plan["room_id"]);
			$user_authority_id = $authCheck->getPageAuthId($this->_user_id, $plan["room_id"]);
			if ($user_authority_id == _AUTH_CHIEF) {
				$user_authority_id = _AUTH_ADMIN;
			}
		}

		if ($user_authority_id > _AUTH_CHIEF || $user_hierarchy > $plan_hierarchy || $plan["insert_user_id"] == $this->_user_id) {
			return _ON;
		} else {
			return _OFF;
		}
	}

	/**
	 * ルーム選択の配列生成
	 *
	 * @access	public
	 */
	function &getSelectRoomList()
	{
		$calendarView =& $this->_container->getComponent("calendarView");

		$calendar_block = $calendarView->getBlock();
		if ($calendar_block === false) {
			return $calendar_block;
		}

		$sess_myroom_flag = $this->_session->getParameter(array("calendar", "myroom_flag", $calendar_block["block_id"]));
		if (isset($sess_myroom_flag)) {
			$calendar_block["myroom_flag"] = intval($sess_myroom_flag);
		}

		$user_id = $this->_session->getParameter("_user_id");

		$room_arr = $this->_request->getParameter("room_arr");
		if (!empty($user_id)) {
			$room_arr[0][0][0] = array(
				"page_id" => CALENDAR_ALL_MEMBERS_ID,
				"parent_id" => 0,
				"page_name" => CALENDAR_ALL_MEMBERS_LANG,
				"thread_num" => 0,
				"space_type" => _SPACE_TYPE_UNDEFINED,
				"private_flag" => _OFF,
				"authority_id" => $this->_session->getParameter("_user_auth_id")
			);
		}

		$thread_num = 0;
		$parent_id = 0;

		$getdata =& $this->_container->getComponent("GetData");
		$pages = $getdata->getParameter("pages");

		$actionChain =& $this->_container->getComponent("ActionChain");

		$result_params = array(
			"enroll_room_arr" => array(),
			"not_enroll_room_arr" => array(),
			"room_arr" => $room_arr,
			"calendar_block" => $calendar_block,
			"private_room_id_arr" => array(),
			"action_name" => $actionChain->getCurActionName()
		);

		$sess_enroll_room = $this->_session->getParameter(array("calendar", "enroll_room", $calendar_block["block_id"]));
		foreach ($result_params["room_arr"][$thread_num][$parent_id] as $disp => $room) {
			if ($room["space_type"] == _SPACE_TYPE_GROUP && $room["private_flag"] == _ON) {
				if ($result_params["calendar_block"]["myroom_flag"] == _ON) {
					$result_params["calendar_block"]["select_room_list"][] = $room["page_id"];
				}
				if ($result_params["calendar_block"]["myroom_flag"] == _ON && strpos(strtolower($result_params["action_name"]), "calendar_view_main") !== false) {
					$result_params["enroll_room_arr"][] = $room;
				}
				continue;
			}
			if (!isset($sess_enroll_room) && in_array($room["page_id"], $result_params["calendar_block"]["select_room_list"]) ||
				!empty($sess_enroll_room) && in_array($room["page_id"], $sess_enroll_room)) {
				$result_params["enroll_room_arr"][] = $room;
			} else {
				$result_params["not_enroll_room_arr"][] = $room;
			}
			$this->_makeRoomArray($result_params, 1, $room["page_id"], $room);
		}
		$this->_request->setParameter("calendar_block", $result_params["calendar_block"]);

		$result = array(
			"enroll_room_arr" => $result_params["enroll_room_arr"],
			"not_enroll_room_arr" => $result_params["not_enroll_room_arr"]
		);
		return $result;
	}

	/**
	 * fetch時コールバックメソッド(pages)
	 *
	 * @param $thread_num
	 * @param $parent_id
	 * @param $parent_room
	 *
	 * @access	private
	 */
	function _makeRoomArray(&$result_params, $thread_num, $parent_id, &$parent_room)
	{
		if (!isset($result_params["room_arr"][$thread_num]) || !isset($result_params["room_arr"][$thread_num][$parent_id])) {
			return true;
		}

   		$sess_enroll_room = $this->_session->getParameter(array("calendar", "enroll_room", $result_params["calendar_block"]["block_id"]));
		$next_thread_num = $thread_num + 1;
		foreach ($result_params["room_arr"][$thread_num][$parent_id] as $disp=>$room) {
			if ($room["space_type"] == _SPACE_TYPE_GROUP && $room["private_flag"] == _ON) {
				if ($result_params["calendar_block"]["myroom_flag"] == _ON) {
					$result_params["calendar_block"]["select_room_list"][] = $room["page_id"];
				}
				if ($result_params["calendar_block"]["myroom_flag"] == _ON && strpos(strtolower($result_params["action_name"]), "calendar_view_main") !== false) {
					$result_params["enroll_room_arr"][] = $room;
				}
				continue;
			}
			if ($room["space_type"] != _SPACE_TYPE_GROUP || $room["space_type"] == _SPACE_TYPE_GROUP && $room["private_flag"] != _ON && $room["thread_num"] > 1) {
				$room["parent_page_name"] = $parent_room["page_name"];
			}
			if (!isset($sess_enroll_room) && in_array($room["page_id"], $result_params["calendar_block"]["select_room_list"]) ||
				!empty($sess_enroll_room) && in_array($room["page_id"], $sess_enroll_room)) {

				$result_params["enroll_room_arr"][] = $room;
			} else {
				$result_params["not_enroll_room_arr"][] = $room;
			}
			$this->_makeRoomArray($result_params, $next_thread_num, $room["page_id"], $room);
		}
		return true;
	}

}
?>