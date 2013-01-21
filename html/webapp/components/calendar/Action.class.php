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
class Calendar_Action
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
	function Calendar_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_wday_array = array("SU","MO","TU","WE","TH","FR","SA");
	}

	/**
	 * 日付フォーマットする
	 *
	 * @access	public
	 */
	function dateFormat($time, $timezone_offset=null, $to_flag=false, $timeFormat="YmdHis")
	{
		if (isset($timezone_offset)) {
			$timezone_minute_offset = 0;
			if(round($timezone_offset) != intval($timezone_offset)) {
				$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
				$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
			}
			$timezone_offset = -1 * $timezone_offset;
			$time = date("YmdHis", mktime(intval(substr($time, 8, 2)) + $timezone_offset, intval(substr($time, 10, 2)) + $timezone_minute_offset, intval(substr($time, 12, 2)),
							intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4))));
		} else {
			$time = timezone_date($time, true, "YmdHis");
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
		return date($timeFormat, $timestamp);
	}

	/**
	 * 予定の追加
	 *
	 * @access	public
	 */
	function insertPlan(&$plan_params)
	{
        if (!isset($plan_params["timezone_offset"])) {
	        $plan_params["timezone_offset"] = $this->_session->getParameter("_timezone_offset");
        }
		if (isset($plan_params["start_date"]) && !isset($plan_params["start_time_full"])) {
			$plan_params["start_time_full"] = $plan_params["start_date"].$plan_params["start_time"];
		} elseif (!isset($plan_params["start_date"]) && isset($plan_params["start_time_full"])) {
			$plan_params["start_date"] = substr($plan_params["start_time_full"], 0, 8);
			$plan_params["start_time"] = substr($plan_params["start_time_full"], 8);
		}
		if (isset($plan_params["end_date"]) && !isset($plan_params["end_time_full"])) {
			$plan_params["end_time_full"] = $plan_params["end_date"].$plan_params["end_time"];
		} elseif (!isset($plan_params["end_date"]) && isset($plan_params["end_time_full"])) {
			$plan_params["end_date"] = substr($plan_params["end_time_full"], 0, 8);
			$plan_params["end_time"] = substr($plan_params["end_time_full"], 8);
		}

		$params = array(
			"location" => (isset($plan_params["location"]) ? $plan_params["location"] : ""),
			"contact" =>  (isset($plan_params["contact"]) ? $plan_params["contact"] : ""),
			"description" =>  (isset($plan_params["description"]) ? $plan_params["description"] : ""),
			"rrule" =>  (isset($plan_params["rrule"]) ? $plan_params["rrule"] : ""),
			"room_id" => (isset($plan_params["room_id"]) ? $plan_params["room_id"] : 0)
		);
    	$plan_id = $this->_db->insertExecute("calendar_plan_details", $params, false, "plan_id");
    	if ($plan_id === false) {
    		return false;
    	}
		$plan_params = array_merge($plan_params, $params);

    	$params = array(
			"plan_id" => $plan_id,
			"room_id" => (isset($plan_params["room_id"]) ? $plan_params["room_id"] : 0),
			"user_id" => $this->_session->getParameter("_user_id"),
			"user_name" => $this->_session->getParameter("_handle"),
			"title" => (isset($plan_params["title"]) ? $plan_params["title"] : ""),
			"title_icon" => (isset($plan_params["title_icon"]) ? $plan_params["title_icon"] : ""),
			"allday_flag" => (isset($plan_params["allday_flag"]) ? $plan_params["allday_flag"] : _OFF),
			"start_date" => $plan_params["start_date"],
			"start_time" => $plan_params["start_time"],
			"start_time_full" => $plan_params["start_time_full"],
			"end_date" => $plan_params["end_date"],
			"end_time" => $plan_params["end_time"],
			"end_time_full" => $plan_params["end_time_full"],
			"timezone_offset" => (isset($plan_params["timezone_offset"]) ? $plan_params["timezone_offset"] : $this->_session->getParameter("_timezone_offset")),
			"link_module" => (isset($plan_params["link_module"]) ? $plan_params["link_module"] : ""),
			"link_id" => (isset($plan_params["link_id"]) ? $plan_params["link_id"] : _OFF),
			"link_action_name" => (isset($plan_params["link_action_name"]) ? $plan_params["link_action_name"] : "")
		);
    	$calendar_id = $this->_db->insertExecute("calendar_plan", $params, true, "calendar_id");
    	if ($calendar_id === false) {
    		return false;
    	}
		$plan_params = array_merge($plan_params, $params);

    	if ($plan_params["link_module"] != "") {
    		$plan_params["calendar_id"] = $calendar_id;
	    	$result = $this->updateLink($plan_params);
	    	if ($result === false) {
	    		return false;
	    	}
    	}

    	if ($plan_params["rrule"] != "") {
			$result = $this->insertRRule($calendar_id, $plan_params["rrule"]);
			if ($result === false) {
				return false;
			}
    	}
		return $calendar_id;
	}

	/**
	 * 予定の変更
	 *
	 * @access	public
	 */
	function updatePlan($calendar_id, &$plan_params, $edit_rrule=0)
	{
		if (isset($plan_params["start_date"]) && !isset($plan_params["start_time_full"])) {
			$plan_params["start_time_full"] = $plan_params["start_date"].$plan_params["start_time"];
		} elseif (!isset($plan_params["start_date"]) && isset($plan_params["start_time_full"])) {
			$plan_params["start_date"] = substr($plan_params["start_time_full"], 0, 8);
			$plan_params["start_time"] = substr($plan_params["start_time_full"], 8);
		}
		if (isset($plan_params["end_date"]) && !isset($plan_params["end_time_full"])) {
			$plan_params["end_time_full"] = $plan_params["end_date"].$plan_params["end_time"];
		} elseif (!isset($plan_params["end_date"]) && isset($plan_params["end_time_full"])) {
			$plan_params["end_date"] = substr($plan_params["end_time_full"], 0, 8);
			$plan_params["end_time"] = substr($plan_params["end_time_full"], 8);
		}

		$result =& $this->_db->selectExecute("calendar_plan", array("calendar_id"=>$calendar_id));
        if (empty($result)) {
        	return true;
        }
		$plan_id = $result[0]["plan_id"];
		if (!isset($plan_params["timezone_offset"])) {
			$plan_params["timezone_offset"] = $result[0]["timezone_offset"];
		}
		$plan_params["insert_time"] = $result[0]["insert_time"];
		$plan_params["insert_site_id"] = $result[0]["insert_site_id"];
		$plan_params["insert_user_id"] = $result[0]["insert_user_id"];
		$plan_params["insert_user_name"] = $result[0]["insert_user_name"];

		$params = array(
			"location" => (isset($plan_params["location"]) ? $plan_params["location"] : ""),
			"contact" =>  (isset($plan_params["contact"]) ? $plan_params["contact"] : ""),
			"description" =>  (isset($plan_params["description"]) ? $plan_params["description"] : ""),
			"rrule" =>  (isset($plan_params["rrule"]) ? $plan_params["rrule"] : ""),
			"room_id" => (isset($plan_params["room_id"]) ? $plan_params["room_id"] : 0)
		);
		$plan_params = array_merge($plan_params, $params);
		if (defined("CALENDAR_PLAN_EDIT_ALL") && $edit_rrule == CALENDAR_PLAN_EDIT_ALL) {
			$result = $this->_db->updateExecute("calendar_plan_details", $params, array("plan_id"=>$plan_id));
			if ($result === false) {
				return false;
			}
		} elseif (defined("CALENDAR_PLAN_EDIT_AFTER") && $edit_rrule == CALENDAR_PLAN_EDIT_AFTER) {
			$plan_id = $this->updatePlanByAfter($calendar_id, $plan_id, $plan_params);
	    	if ($plan_id === false) {
	    		return false;
	    	}
		} else {
	    	$plan_id = $this->_db->insertExecute("calendar_plan_details", $params, false, "plan_id");
	    	if ($plan_id === false) {
	    		return false;
	    	}
		}

    	$params = array(
			"plan_id" => $plan_id,
			"room_id" => (isset($plan_params["room_id"]) ? $plan_params["room_id"] : 0),
			"title" => (isset($plan_params["title"]) ? $plan_params["title"] : ""),
			"title_icon" => (isset($plan_params["title_icon"]) ? $plan_params["title_icon"] : ""),
			"allday_flag" => (isset($plan_params["allday_flag"]) ? $plan_params["allday_flag"] : _OFF),
			"start_date" => $plan_params["start_date"],
			"start_time" => $plan_params["start_time"],
			"start_time_full" => $plan_params["start_time_full"],
			"end_date" => $plan_params["end_date"],
			"end_time" => $plan_params["end_time"],
			"end_time_full" => $plan_params["end_time_full"],
			"timezone_offset" => (isset($plan_params["timezone_offset"]) ? $plan_params["timezone_offset"] : $this->_session->getParameter("_timezone_offset")),
			"link_module" => (isset($plan_params["link_module"]) ? $plan_params["link_module"] : ""),
			"link_id" => (isset($plan_params["link_id"]) ? $plan_params["link_id"] : _OFF),
			"link_action_name" => (isset($plan_params["link_action_name"]) ? $plan_params["link_action_name"] : "")
		);
		$plan_params = array_merge($plan_params, $params);
		if (isset($plan_params["insert_time"])) {
			$params["insert_time"] = $plan_params["insert_time"];
		}
		if (isset($plan_params["insert_site_id"])) {
			$params["insert_site_id"] = $plan_params["insert_site_id"];
		}
		if (isset($plan_params["insert_user_id"])) {
			$params["insert_user_id"] = $plan_params["insert_user_id"];
		}
		if (isset($plan_params["insert_user_name"])) {
			$params["insert_user_name"] = $plan_params["insert_user_name"];
		}
		$where_params = array(
			"calendar_id" => $calendar_id
		);
    	$result = $this->_db->updateExecute("calendar_plan", $params, $where_params, true);
    	if ($result === false) {
    		return false;
    	}

		if ($edit_rrule != 0) {
			$result = $this->insertRRule($calendar_id, $plan_params["rrule"]);
			if ($result === false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 予定の変更
	 *
	 * @access	public
	 */
	function updatePlanByAfter($calendar_id, $plan_id, &$plan_params)
	{
		$sql = "DELETE FROM {calendar_plan} " .
				"WHERE plan_id = ? " .
				"AND start_time_full >= ? " .
				"AND calendar_id <> ?";
    	$params = array(
			"plan_id" => $plan_id,
			"start_time_full" => $plan_params["start_time_full"],
			"calendar_id" => $calendar_id
		);
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$sql = "SELECT COUNT(*) FROM {calendar_plan} " .
				"WHERE plan_id = ? " .
				"AND calendar_id <> ?";
    	$params = array(
			"plan_id" => $plan_id,
			"calendar_id" => $calendar_id
		);
		$result = $this->_db->execute($sql, $params, null, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if ($result[0][0] == 0) {
	    	$result = $this->_db->deleteExecute("calendar_plan_details", array("plan_id"=>$plan_id));
	    	if ($result === false) {
	    		return false;
	    	}
		} else {
			$result =& $this->_db->selectExecute("calendar_plan_details", array("plan_id"=>$plan_id));
	        if ($result === false) {
	        	return false;
	        }
			$rrule_arr = $this->parseRRule($result[0]["rrule"]);
			$freq = $rrule_arr["FREQ"];

			$rrule_arr["FREQ"] = $freq;

			$timestamp = mktime(0,0,0,
						substr($plan_params["start_time_full"],4,2),
						substr($plan_params["start_time_full"],6,2),
						substr($plan_params["start_time_full"],0,4));
			$rrule_arr["UNTIL"] = date("Ymd", $timestamp)."T".substr($plan_params["start_time_full"],8);

			$rrule_before_str = $this->concatRRule($rrule_arr);
			$result = $this->_db->updateExecute("calendar_plan_details", array("rrule"=>$rrule_before_str), array("plan_id"=>$plan_id));
	        if ($result === false) {
	        	return false;
	        }
		}
		$params = array(
			"location" => (isset($plan_params["location"]) ? $plan_params["location"] : ""),
			"contact" =>  (isset($plan_params["contact"]) ? $plan_params["contact"] : ""),
			"description" =>  (isset($plan_params["description"]) ? $plan_params["description"] : ""),
			"rrule" =>  (isset($plan_params["rrule"]) ? $plan_params["rrule"] : ""),
			"room_id" => (isset($plan_params["room_id"]) ? $plan_params["room_id"] : 0)
		);
	    $plan_id = $this->_db->insertExecute("calendar_plan_details", $params, false, "plan_id");
	    return $plan_id;
	}

	/**
	 * 予定の削除
	 *
	 * @access	public
	 */
	function deletePlan($calendar_id, $edit_rrule=0)
	{
		$result =& $this->_db->selectExecute("calendar_plan", array("calendar_id"=>$calendar_id));
        if (empty($result)) {
        	return true;
        }
        $plan_id = $result[0]["plan_id"];
        $start_time_full = $result[0]["start_time_full"];
        $link_module = $result[0]["link_module"];

    	if ($link_module != "") {
        	$link_params["calendar_id"] = $result[0]["calendar_id"];
        	$link_params["link_module"] = $result[0]["link_module"];
        	$link_params["link_id"] = $result[0]["link_id"];
    		$result = $this->clearLink($link_params);
	    	if ($result === false) {
	    		return false;
	    	}
    	}

        if (defined("CALENDAR_PLAN_EDIT_ALL") && $edit_rrule == CALENDAR_PLAN_EDIT_ALL) {
	    	$params = array(
				"plan_id" => $plan_id
			);
			$result =& $this->_db->selectExecute("calendar_plan", $params);
	        if ($result === false) {
	        	return false;
	        }
	        foreach ($result as $i=>$plan_obj) {
	        	if ($link_module != "") {
		        	$link_params["calendar_id"] = $plan_obj["calendar_id"];
		        	$link_params["link_module"] = $plan_obj["link_module"];
		        	$link_params["link_id"] = $plan_obj["link_id"];
		        	$result = $this->clearLink($link_params);
			        if ($result === false) {
			        	return false;
			        }
	        	}
	        }
	    	$result = $this->_db->deleteExecute("calendar_plan", $params);

        } elseif (defined("CALENDAR_PLAN_EDIT_AFTER") && $edit_rrule == CALENDAR_PLAN_EDIT_AFTER) {
	    	$params = array(
				"plan_id" => $plan_id,
				"start_time_full" => $start_time_full
			);

			$sql = "SELECT * FROM {calendar_plan} " .
					"WHERE plan_id = ? " .
					"AND start_time_full >= ? ";
    		$result = $this->_db->execute($sql, $params, null, null, true);
	        if ($result === false) {
	        	return false;
	        }
	        foreach ($result as $i=>$plan_obj) {
	        	if ($link_module != "") {
		        	$link_params["calendar_id"] = $plan_obj["calendar_id"];
		        	$link_params["link_module"] = $plan_obj["link_module"];
		        	$link_params["link_id"] = $plan_obj["link_id"];
		        	$result = $this->clearLink($link_params);
			        if ($result === false) {
			        	return false;
			        }
				}
	        }
			$sql = "DELETE FROM {calendar_plan} " .
					"WHERE plan_id = ? " .
					"AND start_time_full >= ? ";
	    	$params = array(
				"plan_id" => $plan_id,
				"start_time_full" => $start_time_full
			);
			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->addError();
				return false;
			}
			$result =& $this->_db->selectExecute("calendar_plan_details", array("plan_id"=>$plan_id));
	        if ($result === false) {
	        	return false;
	        }
			$rrule_arr = $this->parseRRule($result[0]["rrule"]);

			$timestamp = mktime(0,0,0,substr($start_time_full,4,2),substr($start_time_full,6,2),substr($start_time_full,0,4));
			$rrule_arr["UNTIL"] = date("Ymd", $timestamp)."T".substr($start_time_full, 8);

			$rrule_str = $this->concatRRule($rrule_arr);
			$result = $this->_db->updateExecute("calendar_plan_details", array("rrule"=>$rrule_str), array("plan_id"=>$plan_id));

        } else {
	    	$params = array(
				"calendar_id" => $calendar_id
			);
	    	$result = $this->_db->deleteExecute("calendar_plan", $params);
        }
    	if ($result === false) {
    		return false;
    	}
		return true;
	}

	/**
	 * リンク先の更新
	 *
	 * @access	public
	 */
	function updateLink($link_params)
	{
    	if (!empty($link_params) && $link_params["link_module"] != "") {
    		$params = array(
    			"calendar_id" => $link_params["calendar_id"]
    		);
    		switch ($link_params["link_module"]) {
    			case CALENDAR_LINK_TODO:
					$table_name = CALENDAR_LINK_TABLE_TODO;
 		    		$where_params = array(
		    			CALENDAR_LINK_COLUMN_TODO => $link_params["link_id"]
		    		);
    				break;
    			case CALENDAR_LINK_RESERVATION:
					$table_name = CALENDAR_LINK_TABLE_RESERVATION;
 		    		$where_params = array(
		    			CALENDAR_LINK_COLUMN_RESERVATION => $link_params["link_id"],
		    			"start_time_full" => $link_params["start_time_full"]
		    		);
    				break;
    		}
    		if ($table_name != "") {
		    	$result = $this->_db->updateExecute($table_name, $params, $where_params);
		    	if ($result === false) {
		    		return false;
		    	}
    		}
    	}
		return true;
	}


	/**
	 * リンク先の更新
	 *
	 * @access	public
	 */
	function clearLink($link_params)
	{
    	if (!empty($link_params) && $link_params["link_module"] != "") {
    		$params = array(
    			"calendar_id" => _OFF
    		);
    		$where_params = array(
    			"calendar_id" => $link_params["calendar_id"]
    		);
    		switch ($link_params["link_module"]) {
    			case CALENDAR_LINK_TODO:
					$table_name = CALENDAR_LINK_TABLE_TODO;
    				break;
    			case CALENDAR_LINK_RESERVATION:
					$table_name = CALENDAR_LINK_TABLE_RESERVATION;
    				break;
    		}
    		if ($table_name != "") {
		    	$result = $this->_db->updateExecute($table_name, $params, $where_params);
		    	if ($result === false) {
		    		return false;
		    	}
    		}
    	}
		return true;
	}

	/**
	 * パース処理
	 *
	 * @access	public
	 */
	function &parseRRule($rrule_str="")
	{
		$result_array = array();
		if ($rrule_str == "") {
			$rrule_str = "FREQ=NONE";
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
				$result_array[$key] = intval($val);
				continue;
			}
			$result_array[$key] = explode(",", $val);
		}
        return $result_array;
	}
	/**
	 * 文字列にする処理
	 *
	 * @access	public
	 */
	function concatRRule($rrule)
	{
		if (empty($rrule)) {
			return "";
		}
		$result = array();
		switch ($rrule["FREQ"]) {
			case "NONE":
				$result = array();
				break;
			case "YEARLY":
				$result = array("FREQ=YEARLY");
				$result[] = "INTERVAL=".intval($rrule["INTERVAL"]);
				$result[] = "BYMONTH=".implode(",", $rrule["BYMONTH"]);
				if (!empty($rrule["BYDAY"])) {
					$result[] = "BYDAY=".implode(",", $rrule["BYDAY"]);
				}
				break;
			case "MONTHLY":
				$result = array("FREQ=MONTHLY");
				$result[] = "INTERVAL=".intval($rrule["INTERVAL"]);
				if (!empty($rrule["BYDAY"])) {
					$result[] = "BYDAY=".implode(",", $rrule["BYDAY"]);
				}
				if (!empty($rrule["BYMONTHDAY"])) {
					$result[] = "BYMONTHDAY=".implode(",", $rrule["BYMONTHDAY"]);
				}
				break;
			case "WEEKLY":
				$result = array("FREQ=WEEKLY");
				$result[] = "INTERVAL=".intval($rrule["INTERVAL"]);
				$result[] = "BYDAY=".implode(",", $rrule["BYDAY"]);
				break;
			case "DAILY":
				$result = array("FREQ=DAILY");
				$result[] = "INTERVAL=".intval($rrule["INTERVAL"]);
				break;
			default:
				return false;
		}
		if (isset($rrule["UNTIL"])) {
			$result[] = "UNTIL=".$rrule["UNTIL"];
		} elseif (isset($rrule["COUNT"])) {
			$result[] = "COUNT=".intval($rrule["COUNT"]);
		}
        return implode(";", $result);
	}

	/**
	 * 登録処理
	 *
	 * @access	public
	 */
	function insertRRule($calendar_id, $rrule)
	{
		$this->_calendarView =& $this->_container->getComponent("calendarView");
		if (empty($this->_calendarView)) {
			return true;
		}

		if (!is_array($rrule)) {
			$rrule = $this->parseRRule($rrule);
		}
		$result = $this->_db->selectExecute("calendar_plan", array("calendar_id"=>$calendar_id));
		if ($result === false) {
	       	return $result;
		}
		$start_params = $result[0];

		$sql = "DELETE FROM {calendar_plan} " .
				"WHERE plan_id = ? " .
				"AND calendar_id <> ?";
		$result = $this->_db->execute($sql, array("plan_id"=>$start_params["plan_id"], "calendar_id"=>$calendar_id));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}

		$rrule["INDEX"] = 1;
		switch ($rrule["FREQ"]) {
			case "YEARLY":
				$result = $this->_insertYearly($start_params, $rrule, true);
				break;
			case "MONTHLY":
				if (isset($rrule["BYMONTHDAY"])) {
					$result = $this->_insertMonthlyByMonthday($start_params, $rrule, true);
				} else {
					$result = $this->_insertMonthlyByDay($start_params, $rrule, true);
				}
				break;
			case "WEEKLY":
				$result = $this->_insertWeekly($start_params, $rrule, true);
				break;
			case "DAILY":
				$result = $this->_insertDaily($start_params, $rrule);
				break;
			default:
				$result = true;
		}
       	return $result;
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _insertYearly($params, &$rrule, $first=false, $bymonthday=0)
	{
		$s_time = timezone_date($params["start_date"].$params["start_time"], false, "YmdHis");
		$e_time = timezone_date($params["end_date"].$params["end_time"], false, "YmdHis");

		$start_timestamp = mktime(0, 0, 0, substr($s_time,4,2), substr($s_time,6,2), substr($s_time,0,4));
		$end_timestamp = mktime(0, 0, 0, substr($e_time,4,2), substr($e_time,6,2), substr($e_time,0,4));
		$diff_num = ($end_timestamp - $start_timestamp) / 86400;

		if ($first) {
			$start_timestamp = mktime(substr($s_time,8,2), substr($s_time,10,2), substr($s_time,12,2),
								substr($s_time,4,2), substr($s_time,6,2), substr($s_time,0,4));
			$end_timestamp = mktime(substr($e_time,8,2), substr($e_time,10,2), substr($e_time,12,2),
								substr($e_time,4,2), substr($e_time,6,2), substr($e_time,0,4));
		} else {
			$start_timestamp = mktime(substr($s_time,8,2), substr($s_time,10,2), substr($s_time,12,2),
								1, 1, substr($s_time,0,4) + $rrule["INTERVAL"]);
			$end_timestamp = mktime(substr($e_time,8,2), substr($e_time,10,2), substr($e_time,12,2),
								1, 1 + $diff_num, substr($e_time,0,4) + $rrule["INTERVAL"]);
		}
		$start_date = date("Ymd", $start_timestamp);
		$start_time = date("His", $start_timestamp);

		$end_date = date("Ymd", $end_timestamp);
		$end_time = date("His", $end_timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		if ($first && empty($rrule["BYDAY"])) {
			$bymonthday = intval(substr($start_date,6,2));
		}

		$current_month = intval(substr($start_date,4,2));
		foreach ($rrule["BYMONTH"] as $i=>$month) {
			if ($first && $current_month > $month) { continue; }
			if ($first && $current_month == $month) {
				$params["start_date"] = $start_date;
				$params["start_time"] = $start_time;
				$params["end_date"] = $end_date;
				$params["end_time"] = $end_time;
			} else {
				$params["start_date"] = substr($start_date,0,4).sprintf("%02d",$month)."01";
				$params["start_time"] = $start_time;
				$params["end_date"] = substr($end_date,0,4).sprintf("%02d",$month).sprintf("%02d", 1 + $diff_num);
				$params["end_time"] = $end_time;
			}
			if (!empty($rrule["BYDAY"]) && count($rrule["BYDAY"]) > 0) {
				$result = $this->__insertYearlyByday($params, $rrule, $first);
			} else {
				$result = $this->__insertYearlyByMonthday($params, $rrule, $bymonthday, $first);
			}
			if ($result === false) {
				return false;
			}
		}
		$params["start_date"] = timezone_date($start_date.$start_time, true, "Ymd");
		$params["start_time"] = timezone_date($start_date.$start_time, true, "His");
		$params["end_date"] = timezone_date($end_date.$end_time, true, "Ymd");
		$params["end_time"] = timezone_date($end_date.$end_time, true, "His");

		if (!empty($rrule["BYDAY"]) && count($rrule["BYDAY"]) > 0) {
			return $this->_insertYearly($params, $rrule);
		} else {
			return $this->_insertYearly($params, $rrule, false, $bymonthday);
		}
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function __insertYearlyByMonthday($params, &$rrule, $bymonthday, $first)
	{
		$rrule["INDEX"]++;

		$s_time = $params["start_date"].$params["start_time"];
		$start_timestamp = mktime(0, 0, 0, substr($s_time,4,2), substr($s_time,6,2), substr($s_time,0,4));

		$current_day = intval(substr($s_time,6,2));
		$first_timestamp = mktime(0, 0, 0, substr($s_time,4,2), 1, substr($s_time,0,4));

		if ($bymonthday > date("t", $first_timestamp)) {
			$interval_day = $bymonthday - date("t", $first_timestamp);
		} else {
			$interval_day = 0;
		}

		if ($first && $current_day >= $bymonthday) {
			$rrule["INDEX"]--;
			return true;
		}
		$e_time = $params["end_date"].$params["end_time"];
		$end_timestamp = mktime(0, 0, 0, substr($e_time,4,2), substr($e_time,6,2), substr($e_time,0,4));

		$diff_num = ($end_timestamp - $start_timestamp) / 86400;


		$start_timestamp = mktime(substr($s_time,8,2), substr($s_time,10,2), substr($s_time,12,2),
							substr($s_time,4,2), $bymonthday - $interval_day, substr($s_time,0,4));
		$start_date = date("Ymd", $start_timestamp);
		$start_time = date("His", $start_timestamp);

		$end_timestamp = mktime(substr($e_time,8,2), substr($e_time,10,2), substr($e_time,12,2),
							substr($s_time,4,2), $bymonthday - $interval_day + $diff_num, substr($s_time,0,4));
		$end_date = date("Ymd", $end_timestamp);
		$end_time = date("His", $end_timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		$r_params = array();
		$calendar_id = $this->_insert($params, $r_params, $start_date.$start_time, $end_date.$end_time);
    	if ($calendar_id === false) {
    		return false;
    	} else {
    		return true;
    	}
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function __insertYearlyByday($params, &$rrule, $first=false)
	{
		$rrule["INDEX"]++;

		$wday_num = array_search(substr($rrule["BYDAY"][0], -2), $this->_wday_array);
		$week = intval(substr($rrule["BYDAY"][0], 0, -2));

		$s_time = $params["start_date"].$params["start_time"];
		$e_time = $params["end_date"].$params["end_time"];

		$timestamp = mktime(0, 0, 0, substr($s_time,4,2), 1, substr($s_time,0,4));
		$year = date("Y", $timestamp);
		$month = date("m", $timestamp);
    	if ($week == -1) {
			$last_day = date("t", $timestamp);
			$timestamp = mktime(0, 0, 0, $month, $last_day, $year);
			$w_last_day = date("w", $timestamp);
			$w_last_day = ($wday_num <= $w_last_day ? $w_last_day : 7 + $w_last_day);
     		$timestamp = mktime(0, 0, 0, $month, $last_day - $w_last_day + $wday_num, $year);
    	} else {
			$w_1day = date("w", $timestamp);
			$w_1day = ($w_1day <= $wday_num ? 7 + $w_1day : $w_1day);
			$day = $week * 7 + $wday_num + 1;
			$timestamp = mktime(0, 0, 0, $month, $day - $w_1day, $year);
    	}
		$byday = date("YmdHis", $timestamp);
		if ($first && $s_time >= $byday) {
			$rrule["INDEX"]--;
			return true;
		}

		$start_timestamp = mktime(0, 0, 0, substr($s_time,4,2), substr($s_time,6,2), substr($s_time,0,4));
		$end_timestamp = mktime(0, 0, 0, substr($e_time,4,2), substr($e_time,6,2), substr($e_time,0,4));

		$diff_num = ($end_timestamp - $start_timestamp) / 86400;

		$timestamp = mktime(substr($s_time,8,2), substr($s_time,10,2), substr($s_time,12,2),
							substr($byday,4,2), substr($byday,6,2), substr($byday,0,4));
		$start_date = date("Ymd", $timestamp);
		$start_time = date("His", $timestamp);

		$timestamp = mktime(substr($e_time,8,2), substr($e_time,10,2), substr($e_time,12,2),
							substr($byday,4,2), substr($byday,6,2) + $diff_num, substr($byday,0,4));
		$end_date = date("Ymd", $timestamp);
		$end_time = date("His", $timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		$r_params = array();
		$calendar_id = $this->_insert($params, $r_params, $start_date.$start_time, $end_date.$end_time);
    	if ($calendar_id === false) {
    		return false;
    	} else {
    		return true;
    	}
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _insertMonthlyByMonthday($params, &$rrule, $first=false)
	{
		$rrule["INDEX"]++;

		$s_time = timezone_date($params["start_date"].$params["start_time"], false, "YmdHis");
		$start_timestamp = mktime(0, 0, 0, substr($s_time,4,2), substr($s_time,6,2), substr($s_time,0,4));
		$current_day = intval(substr($s_time,6,2));

		if ($first && $current_day < $rrule["BYMONTHDAY"][0]) {
			$interval = 0;
		} else {
			$interval = $rrule["INTERVAL"];
		}
		$first_timestamp = mktime(0, 0, 0, substr($s_time,4,2) + $interval, 1, substr($s_time,0,4));

		if ($rrule["BYMONTHDAY"][0] > date("t", $first_timestamp)) {
			$interval_day = $rrule["BYMONTHDAY"][0] - date("t", $first_timestamp);
		} else {
			$interval_day = 0;
		}

		$e_time = timezone_date($params["end_date"].$params["end_time"], false, "YmdHis");
		$end_timestamp = mktime(0, 0, 0, substr($e_time,4,2), substr($e_time,6,2), substr($e_time,0,4));

		$diff_num = ($end_timestamp - $start_timestamp) / 86400;


		$start_timestamp = mktime(substr($s_time,8,2), substr($s_time,10,2), substr($s_time,12,2),
							substr($s_time,4,2) + $interval, $rrule["BYMONTHDAY"][0] - $interval_day, substr($s_time,0,4));
		$start_date = date("Ymd", $start_timestamp);
		$start_time = date("His", $start_timestamp);

		$end_timestamp = mktime(substr($e_time,8,2), substr($e_time,10,2), substr($e_time,12,2),
							substr($s_time,4,2) + $interval, $rrule["BYMONTHDAY"][0] - $interval_day + $diff_num, substr($s_time,0,4));
		$end_date = date("Ymd", $end_timestamp);
		$end_time = date("His", $end_timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		$r_params = array();
		$calendar_id = $this->_insert($params, $r_params, $start_date.$start_time, $end_date.$end_time);
    	if ($calendar_id === false) {
    		return false;
    	}
		return $this->_insertMonthlyByMonthday($r_params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _insertMonthlyByDay($params, &$rrule, $first=false)
	{
		$rrule["INDEX"]++;

		$wday_num = array_search(substr($rrule["BYDAY"][0], -2), $this->_wday_array);
		$week = intval(substr($rrule["BYDAY"][0], 0, -2));

		$s_time = timezone_date($params["start_date"].$params["start_time"], false, "YmdHis");
		$e_time = timezone_date($params["end_date"].$params["end_time"], false, "YmdHis");

		$timestamp = mktime(0, 0, 0, substr($s_time,4,2) + ($first ? 0 : $rrule["INTERVAL"]), 1, substr($s_time,0,4));
		$year = date("Y", $timestamp);
		$month = date("m", $timestamp);
    	if ($week == -1) {
			$last_day = date("t", $timestamp);
			$timestamp = mktime(0, 0, 0, $month, $last_day, $year);
			$w_last_day = date("w", $timestamp);
			$w_last_day = ($wday_num <= $w_last_day ? $w_last_day : 7 + $w_last_day);
    		$timestamp = mktime(0, 0, 0, $month, $last_day - $w_last_day + $wday_num, $year);
    	} else {
			$w_1day = date("w", $timestamp);
			$w_1day = ($w_1day <= $wday_num ? 7 + $w_1day : $w_1day);
			$day = $week * 7 + $wday_num + 1;
			$timestamp = mktime(0, 0, 0, $month, $day - $w_1day, $year);
    	}
		$byday = date("YmdHis", $timestamp);
		if ($first && $s_time >= $byday) {
			$rrule["INDEX"]--;
			return $this->_insertMonthlyByDay($params, $rrule);
		}

		$start_timestamp = mktime(0, 0, 0, substr($s_time,4,2), substr($s_time,6,2), substr($s_time,0,4));
		$end_timestamp = mktime(0, 0, 0, substr($e_time,4,2), substr($e_time,6,2), substr($e_time,0,4));

		$diff_num = ($end_timestamp - $start_timestamp) / 86400;

		$timestamp = mktime(substr($s_time,8,2), substr($s_time,10,2), substr($s_time,12,2),
							substr($byday,4,2), substr($byday,6,2), substr($byday,0,4));
		$start_date = date("Ymd", $timestamp);
		$start_time = date("His", $timestamp);

		$timestamp = mktime(substr($e_time,8,2), substr($e_time,10,2), substr($e_time,12,2),
							substr($byday,4,2), substr($byday,6,2) + $diff_num, substr($byday,0,4));
		$end_date = date("Ymd", $timestamp);
		$end_time = date("His", $timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		$r_params = array();
		$calendar_id = $this->_insert($params, $r_params, $start_date.$start_time, $end_date.$end_time);
    	if ($calendar_id === false) {
    		return false;
    	}
		return $this->_insertMonthlyByDay($r_params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _insertWeekly($params, &$rrule, $first=false)
	{
		$time = timezone_date($params["start_date"].$params["start_time"], false, "YmdHis");
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + ($first ? 0 : (7 * $rrule["INTERVAL"])), substr($time,0,4));
		$current_week = date("w", $timestamp);
		$sunday_timestamp = $timestamp - $current_week * 86400;
		$params["start_date"] = date("Ymd", $sunday_timestamp);
		$params["start_time"] = date("His", $sunday_timestamp);

		$time = timezone_date($params["end_date"].$params["end_time"], false, "YmdHis");
		$end_timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + ($first ? 0 : (7 * $rrule["INTERVAL"])), substr($time,0,4));
		$params["end_date"] = date("Ymd", $end_timestamp - $current_week * 86400);
		$params["end_time"] = date("His", $end_timestamp - $current_week * 86400);

		if (!$this->_isRepeatable($rrule, $params["start_date"].$params["start_time"], $params["timezone_offset"])) {
			return true;
		}
		foreach ($rrule["BYDAY"] as $i=>$val) {
			$index = array_search($val, $this->_wday_array);
			if ($first && $current_week >= $index) { continue; }
			$result = $this->__insertWeekly($params, $rrule, $index);
			if ($result === false) {
				return $result;
			}
		}

		$start_time = $params["start_date"].$params["start_time"];
		$end_time = $params["end_date"].$params["end_time"];

		$params["start_date"] = timezone_date($start_time, true, "Ymd");
		$params["start_time"] = timezone_date($start_time, true, "His");
		$params["end_date"] = timezone_date($end_time, true, "Ymd");
		$params["end_time"] = timezone_date($end_time, true, "His");

		return $this->_insertWeekly($params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function __insertWeekly(&$params, &$rrule, $interval)
	{
		$rrule["INDEX"]++;

		$time = $params["start_date"].$params["start_time"];
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + $interval, substr($time,0,4));
		$start_date = date("Ymd", $timestamp);
		$start_time = date("His", $timestamp);

		$time = $params["end_date"].$params["end_time"];
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + $interval, substr($time,0,4));
		$end_date = date("Ymd", $timestamp);
		$end_time = date("His", $timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		$r_params = array();
		$calendar_id = $this->_insert($params, $r_params, $start_date.$start_time, $end_date.$end_time);
    	if ($calendar_id === false) {
    		return false;
    	} else {
			return true;
    	}
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _insertDaily($params, &$rrule)
	{
		$rrule["INDEX"]++;

		$time = timezone_date($params["start_date"].$params["start_time"], false, "YmdHis");
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + $rrule["INTERVAL"], substr($time,0,4));
		$start_date = date("Ymd", $timestamp);
		$start_time = date("His", $timestamp);

		$time = timezone_date($params["end_date"].$params["end_time"], false, "YmdHis");
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + $rrule["INTERVAL"], substr($time,0,4));
		$end_date = date("Ymd", $timestamp);
		$end_time = date("His", $timestamp);

		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return true;
		}

		$r_params = array();
		$calendar_id = $this->_insert($params, $r_params, $start_date.$start_time, $end_date.$end_time);
    	if ($calendar_id === false) {
    		return false;
    	}
		return $this->_insertDaily($r_params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _insert($params, &$r_params, $start_time, $end_time)
	{
		if (empty($this->details_param)) {
			$result = $this->_db->selectExecute("calendar_plan_details", array("plan_id"=>$params["plan_id"]));
			if ($result === false) {
		       	return $result;
			}
			$this->details_param = $result[0];
		}

		$insert_start_time = timezone_date($start_time, true, "YmdHis");
		$insert_end_time = timezone_date($end_time, true, "YmdHis");
    	$r_params = array(
			"plan_id" => $params["plan_id"],
			"room_id" => $params["room_id"],
			"user_id" => $params["user_id"],
			"user_name" => $params["user_name"],
			"title" => $params["title"],
			"title_icon" => $params["title_icon"],
			"allday_flag" => $params["allday_flag"],
			"start_date" => substr($insert_start_time, 0, 8),
			"start_time" => substr($insert_start_time, 8),
			"start_time_full" => $insert_start_time,
			"end_date" => substr($insert_end_time, 0, 8),
			"end_time" => substr($insert_end_time, 8),
			"end_time_full" => $insert_end_time,
			"timezone_offset" => $params["timezone_offset"],
			"link_module" => $params["link_module"],
			"link_id" => $params["link_id"],
			"link_action_name" => $params["link_action_name"],
		);
		if (isset($params["insert_time"])) {
			$r_params["insert_time"] = $params["insert_time"];
		}
		if (isset($params["insert_site_id"])) {
			$r_params["insert_site_id"] = $params["insert_site_id"];
		}
		if (isset($params["insert_user_id"])) {
			$r_params["insert_user_id"] = $params["insert_user_id"];
		}
		if (isset($params["insert_user_name"])) {
			$r_params["insert_user_name"] = $params["insert_user_name"];
		}
		if (isset($params["update_time"])) {
			$r_params["update_time"] = $params["update_time"];
		}
		if (isset($params["update_site_id"])) {
			$r_params["update_site_id"] = $params["update_site_id"];
		}
		if (isset($params["update_user_id"])) {
			$r_params["update_user_id"] = $params["update_user_id"];
		}
		if (isset($params["update_user_name"])) {
			$r_params["update_user_name"] = $params["update_user_name"];
		}
		$calendar_id = $this->_db->insertExecute("calendar_plan", $r_params, false, "calendar_id");

    	if ($r_params["link_module"] != "") {
    		$r_params["calendar_id"] = $calendar_id;
	    	$result = $this->updateLink($r_params);
	    	if ($result === false) {
	    		return false;
	    	}
    	}

    	return $calendar_id;
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _isRepeatable($rrule, $start_time, $timezone_offset)
	{
		if (isset($rrule["UNTIL"])) {
			$until = $this->_calendarView->dateFormat(substr($rrule["UNTIL"],0,8).substr($rrule["UNTIL"],-6), $timezone_offset, false);
			if ($start_time >= $until) {
				return false;
			}
		} else {
			$count = isset($rrule["COUNT"]) ? intval($rrule["COUNT"]) : 3;
			if ($rrule["INDEX"] > $count) {
				return false;
			}
		}
		return true;
	}

}
?>
