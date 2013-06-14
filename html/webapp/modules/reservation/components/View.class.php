<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設予約取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Components_View
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
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var 週Arrayを保持
	 *
	 * @access	private
	 */
	var $_weekNameArray = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Reservation_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_session =& $this->_container->getComponent("Session");

		if (!isset($this->_weekNameArray) && defined("RESERVATION_WDAY")) {
			$this->_wdayArrary = explode("|", RESERVATION_WDAY);
		} else {
			$this->_wdayArrary = array("SU", "MO", "TU", "WE", "TH", "FR", "SA");
		}
		if (!isset($this->_weekNameArray) && defined("RESERVATION_WEEK_NAME")) {
			$this->_weekNameArray = explode("|", RESERVATION_WEEK_NAME);
		} else {
			$this->_weekNameArray = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
		}
		if (!isset($this->_weekLongNameArray) && defined("RESERVATION_WEEK_LONG_NAME")) {
			$this->_weekLongNameArray = explode("|", RESERVATION_WEEK_LONG_NAME);
		} else {
			$this->_weekLongNameArray = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		}

	}

	/**
	 * 時間の差を計算する
	 *
	 * @access	private
	 */
	function TimeDiff($from_time, $to_time)
	{
		if (strlen($from_time) == 6) {
			$dateTimeBegin = mktime(substr($from_time,0,2), substr($from_time,2,2), substr($from_time,4));
			$dateTimeEnd = mktime(substr($to_time,0,2), substr($to_time,2,2), substr($to_time,4));
		} else {
			$dateTimeBegin = mktime(substr($from_time,8,2), substr($from_time,10,2), substr($from_time,12,2),
									substr($from_time,4,2), substr($from_time,6,2), substr($from_time,0,4));
			$dateTimeEnd = mktime(substr($to_time,8,2), substr($to_time,10,2), substr($to_time,12,2),
									substr($to_time,4,2), substr($to_time,6,2), substr($to_time,0,4));
		}

		$diff = $dateTimeEnd - $dateTimeBegin;
		if ($diff < 0) {
			# error condition
			return false;
		}
		return round($diff / 3600, 2);
	}

	/**
	 * 週データ取得
	 *
	 * @access	public
	 */
	function getWeekArray()
	{
		foreach ($this->_wdayArrary as $i=>$key) {
			$weekArray[$i] = array("name" => $this->_weekNameArray[$i], "long_name" => $this->_weekLongNameArray[$i]);
		}
		return $weekArray;
	}

	/**
	 * タイムテーブル文字列に変換
	 *
	 * @access	public
	 */
	function convertTimeTableStr($time_table)
	{
		if ($time_table == RESERVATION_DEF_EVERYDAY) {
			$time_table_str = RESERVATION_EVERYDAY;
		} elseif ($time_table == RESERVATION_DEF_WEEKYDAY) {
			$time_table_str = RESERVATION_WEEKDAY;
		} else {
			$time_table_str = "";
			$time_table = explode(",", $time_table);
			foreach ($time_table as $i=>$val) {
				$index = array_search($val, $this->_wdayArrary);
				if ($time_table_str == "") {
					$time_table_str .= $this->_weekNameArray[$index];
				} else {
					$time_table_str .= ",". $this->_weekNameArray[$index];
				}
			}
		}
		return $time_table_str;
	}
	/**
	 * タイムテーブルの選択データ取得
	 *
	 * @access	public
	 */
	function getLocationWeekArray()
	{
		foreach ($this->_wdayArrary as $i=>$key) {
			$weekArray[$key] = array("name" => $this->_weekNameArray[$i], "long_name" => $this->_weekLongNameArray[$i]);
		}
		return $weekArray;
	}


	/**
	 * 日付フォーマットする
	 *
	 * @access	public
	 */
	function dateFormat($time=null, $timezone_offset=null, $insert_flag=false, $timeFormat="YmdHis", $to_flag=false)
	{
		if (!isset($time)) {
			$time = timezone_date(null, $insert_flag, "YmdHis");
		}
		if (isset($timezone_offset)) {
			$timezone_minute_offset = 0;
			$timezone_offset = floatval($timezone_offset);
			if(round($timezone_offset) != intval($timezone_offset)) {
				$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
				$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
			}

			if ($insert_flag) {
				$timezone_offset = -1 * $timezone_offset;
				$timezone_minute_offset = -1 * $timezone_minute_offset;
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
		$week = date("w", $timestamp);
		return date(sprintf($timeFormat, $this->_weekNameArray[$week]), $timestamp);
	}

	/**
	 * 予約区分の設定
	 *
	 * @access	public
	 */
	function setReserveFlag(&$row)
	{
		if ($row["space_type"] == _SPACE_TYPE_PUBLIC) {
			$row["reserve_flag"] = RESERVATION_PUBLIC;
		} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _ON) {
			$row["reserve_flag"] = RESERVATION_PRIVATE;
		} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _OFF) {
			$row["reserve_flag"] = RESERVATION_GROUP;
		} else {
			$row["reserve_flag"] = RESERVATION_MEMBERS;
		}
		return true;
	}

	/**
	 * カテゴリー件数を取得する
	 *
	 * @param   mixed   $category_id カテゴリーID
	 *
	 * @return  integer	カテゴリー件数
	 * @access  public
	 */
	function getCountCategory($category_id=null)
	{
		if (isset($category_id)) {
			$params = array("category_id"=>$category_id);
		} else {
			$params = null;
		}
		$count = $this->_db->countExecute("reservation_category", $params);
		return $count;
	}

	/**
	 * カテゴリーの存在チェックする
	 *
	 * @param   mixed   $category_id カテゴリーID
	 *
	 * @return  boolean
	 * @access  public
	 */
	function categoryExists($category_id=null)
	{
		$count = $this->getCountCategory($category_id);
		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * カテゴリー取得
	 *
	 * @access	public
	 */
	function getFirstCategory()
	{
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$location_count_list = $this->getCountLocationByCategory();
		$key_array = array_keys($location_count_list);
		if (!empty($key_array)) {
			return $key_array[0];
		}
		// 2013.02.12 bugfix modify
		if ($actionName == "reservation_action_edit_addblock" || $actionName == "reservation_action_edit_location_delete") {
			return "0";
		} else {
			return false;
		}
	}

	/**
	 * カテゴリー取得
	 *
	 * @access	public
	 */
	function &getCategories()
	{
		$sql = "SELECT category_id, category_name ";
		$sql .= "FROM {reservation_category} " .
				"ORDER BY display_sequence";
		$result = $this->_db->execute($sql, array(), null, null, true, array($this,"_getCategories"));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}
	/**
	 * カテゴリー取得
	 *
	 * @access	private
	 */
	function _getCategories(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[$row["category_id"]] = $row["category_name"];
		}
		return $result;
	}

	/**
	 * カテゴリー取得
	 *
	 * @param   mixed   $category_id カテゴリーID
	 *
	 * @return  array	カテゴリー
	 * @access  public
	 */
	function getCategory($category_id)
	{
		$category =& $this->_db->selectExecute("reservation_category", array("category_id"=>$category_id));
		if ($category === false) {
			return $category;
		}
		$category = $category[0];
		return $category;
	}
	/**
	 * カテゴリー取得
	 *
	 * @access	public
	 */
	function getCategorySequence()
	{
		$max_sequence = $this->_db->maxExecute("reservation_category", "display_sequence");
		if ($max_sequence === false) {
			return false;
		}
		if (empty($max_sequence)) {
			$max_sequence = 0;
		}
		return $max_sequence;
	}
	/**
	 * カテゴリー取得
	 *
	 * @param   mixed   $category_id カテゴリーID
	 *
	 * @return  array	カテゴリー
	 * @access  public
	 */
	function getNonCategory()
	{
		$categories =& $this->_db->selectExecute("reservation_category", array("category_name"=>""));
		if ($categories === false) {
			return $categories;
		}
		$category = $categories[0];
		return $category;
	}

	/**
	 * デフォルト値取得
	 *
	 * @access	public
	 */
	function getDefaultBlock()
	{
		$module_id = $this->_request->getParameter("module_id");
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
			return false;
		}

		if (defined($config['display_type']["conf_value"])) {
			$display_type = constant($config['display_type']["conf_value"]);
		} else {
			$display_type = $config['display_type']["conf_value"];
		}
		$room_id_arr = $this->_request->getParameter("room_id_arr");

		switch ($display_type) {
			case RESERVATION_DEF_MONTHLY:
			case RESERVATION_DEF_WEEKLY:
				$result = $this->getFirstLocation();
				if ($result === false) {
					return $result;
				}
				$category_id = $result["category_id"];
				$location_id = $result["location_id"];
				break;
			case RESERVATION_DEF_LOCATION:
				$category_id = $this->getFirstCategory();
				if ($category_id === false) {
					return $category_id;
				}
				$category_id = $category_id;
				$location_id = 0;
				break;
			default:
				$category_id = 0;
				$location_id = 0;
		}

		if (defined($config['display_interval']["conf_value"])) {
			$display_interval = constant($config['display_interval']["conf_value"]);
		} else {
			$display_interval = intval($config['display_interval']["conf_value"]);
		}

		if (defined($config['display_start_time']["conf_value"])) {
			$display_start_time = constant($config['display_start_time']["conf_value"]);
		} else {
			$display_start_time = $config['display_start_time']["conf_value"];
		}

		$default = array(
			"block_id" => 0,
			"display_type" => $display_type,
    		'display_timeframe'=>_OFF,
			"display_start_time" => $display_start_time,
			"start_time_hour" => $config['start_time_hour']["conf_value"],
			"display_interval" => $display_interval,
			"category_id" => $category_id,
			"location_id" => $location_id
		);
		return $default;
	}

	/**
	 * 施設取得
	 *
	 * @access	public
	 */
	function getFirstLocation($category_id = null)
	{
		$result = $this->getLocations($category_id, 1, 0);
		if ($result === false) {
			return $result;
		}
		if (!isset($result[0])) {
			return false;
		}
		return $result[0];
	}

	/**
	 * 施設取得
	 *
	 * @access	public
	 */
	function getLocationSequence($category_id)
	{
		$max_sequence = $this->_db->maxExecute("reservation_location", "display_sequence", array("category_id"=>$category_id));
		if ($max_sequence === false) {
			return false;
		}
		if (empty($max_sequence)) {
			$max_sequence = 0;
		}
		return $max_sequence;
	}

	/**
	 * 施設取得
	 *
	 * @access	public
	 */
	function getLocations($category_id = null, $limit = null, $offset = null, $func=null)
	{
		$_user_auth_id = $this->_session->getParameter("_user_auth_id");
		$_user_id = $this->_session->getParameter("_user_id");

		if (!isset($func)) {
			$func = array($this,"_getLocations");
		}
		if (!isset($options)) {
			$options = array("");
		}

		$room_id_arr = $this->_request->getParameter("room_id_arr");

		$sql = "SELECT DISTINCT location.* ";
		$sql .= "FROM {reservation_location} location ".
				"INNER JOIN {reservation_category} category ".
					"ON (location.category_id = category.category_id) ".
				"LEFT JOIN {reservation_location_rooms} l_rooms ".
					"ON (location.location_id = l_rooms.location_id) ";
		if ($_user_auth_id != _AUTH_ADMIN) {
			$sql .= "WHERE (";
			$sql .= 	"location.allroom_flag = ". _ON;
			if (!empty($room_id_arr)) {
				$sql .= " OR location.allroom_flag = ". _OFF ." AND l_rooms.room_id IN (".implode(",",$room_id_arr).")";
			}
			$sql .= ") ";
		} else {
			$sql .= "WHERE 1=1 ";
		}
		if (isset($category_id)) {
			$sql .= "AND location.category_id = ".$category_id." ";
		}
		$sql .= "ORDER BY category.display_sequence, location.display_sequence";

		$result = $this->_db->execute($sql, array(), $limit, $offset, true, $func, array(_OFF));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}
	/**
	 * 施設取得
	 *
	 * @access	private
	 */
	function _getLocations(&$recordSet, $params)
	{
		$_user_auth_id = $this->_session->getParameter("_user_auth_id");

		$result = array();
		$commonMain =& $this->_container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$reserve_block = $this->_request->getParameter("reserve_block");
		if (!empty($reserve_block)) {
			$display_type = $reserve_block["display_type"];
		}
		while ($row = $recordSet->fetchRow()) {
			if ($actionName == "reservation_view_admin_search") {
				$result[] = $row["location_id"];
				continue;
			}

			$time_diff = $this->TimeDiff($row["start_time"], $row["end_time"]);
			if ($time_diff == 24) {
				$row["allday_flag"] = _ON;
			} else {
				$row["allday_flag"] = _OFF;
			}
			$row["time_table_arr"] = explode(",", $row["time_table"]);
			$row["time_table_str"] = $this->convertTimeTableStr($row["time_table"]);

			$row["start_time_str"] = $this->dateFormat($row["start_time"], $row["timezone_offset"], false, _SHORT_TIME_FORMAT);
			$row["start_time"] = $this->dateFormat($row["start_time"], $row["timezone_offset"]);

			$row["end_time_str"] = $this->dateFormat($row["end_time"], $row["timezone_offset"], false, _SHORT_TIME_FORMAT, true);
			$row["end_time"] = $this->dateFormat($row["end_time"], $row["timezone_offset"], false, "YmdHis", true);

			if ($actionName == "reservation_view_main_location_details" || $actionName == "reservation_view_main_reserve_modify") {
				$row["timezone_string"] = $timezoneMain->getLangTimeZone($row["timezone_offset"]);
			} else {
				$row["timezone_string"] = $timezoneMain->getLangTimeZone($row["timezone_offset"], false);
			}

			if ($actionName == "reservation_view_main_init" && $params[0] == _OFF && $display_type == RESERVATION_DEF_LOCATION) {
				$result[$row["location_id"]] = $row;
			} elseif ($actionName == "reservation_view_edit_location_init" || $actionName == "reservation_view_main_movedate" ||
						$actionName == "reservation_view_main_init" && $params[0] == _OFF) {
				$result[$row["category_id"]][] = $row;
			} else {
				$result[] = $row;
			}

		}
		return $result;
	}
	/**
	 * 施設取得
	 *
	 * @access	public
	 */
	function getLocation($location_id, $details_flag=false)
	{
		if ($details_flag) {
			$sql = "SELECT location.*, category.category_name, details.contact, details.description ".
					"FROM {reservation_location} location ";
			$sql .= "INNER JOIN {reservation_category} category ".
						"ON (location.category_id = category.category_id) ";
			$sql .= "INNER JOIN {reservation_location_details} details ".
						"ON (location.location_id = details.location_id) ";
			$sql .= "WHERE location.location_id = ?";

		} else {
			$sql = "SELECT * " .
					"FROM {reservation_location} " .
					"WHERE location_id = ?";
		}

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "reservation_action_edit_location_sequence") {
			$result = $this->_db->execute($sql, array("location_id"=>$location_id));
		} else {
			$result = $this->_db->execute($sql, array("location_id"=>$location_id), null, null, true, array($this,"_getLocations"), array(_ON));
		}
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if (empty($result)) {
			return false;
		}
		$result = $result[0];
		return $result;
	}
	/**
	 * 施設取得
	 *
	 * @access	public
	 */
	function getDefaultLocation()
	{
		$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
			return false;
		}

		$commonMain =& $this->_container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");

		$timezone_offset = $this->_session->getParameter("_timezone_offset");
		$location = array(
			"location_id" => 0,
			"category_id" => 0,
			"location_name" => "",
			"active_flag" => _ON,
			"add_authority" => _AUTH_CHIEF,
			"time_table" => RESERVATION_DEF_WEEKYDAY,
			"time_table_arr" => explode(",", RESERVATION_DEF_WEEKYDAY),
			"start_time" => timezone_date_format(null, "Ymd090000"),
			"end_time" => timezone_date_format(null, "Ymd180000"),
			"timezone_offset" => $timezone_offset,
			"timezone_string" => $timezoneMain->getLangTimeZone($timezone_offset, false),
			"allday_flag" => _OFF,
			"contact" => "",
			"description" => "",
			"duplication_flag" => _OFF,
			"use_private_flag" => (defined($config['use_private_flag']["conf_value"]) ?
										constant($config['use_private_flag']["conf_value"]) : intval($config['use_private_flag']["conf_value"])),
			"allroom_flag" => (defined($config['allroom_flag']["conf_value"]) ?
										constant($config['allroom_flag']["conf_value"]) : intval($config['allroom_flag']["conf_value"])),
			"display_sequence" => 0
		);
		return $location;
	}

	/**
	 * 施設件数取得
	 *
	 * @access	public
	 */
	function getCountLocationByCategory()
	{
		$_user_auth_id = $this->_session->getParameter("_user_auth_id");
		$_user_id = $this->_session->getParameter("_user_id");

		$room_id_arr = $this->_request->getParameter("room_id_arr");

		if ($_user_auth_id != _AUTH_ADMIN) {
			$sql = "SELECT location.category_id, COUNT(*) AS location_count";
			$sql .= " FROM {reservation_location} location".
					" LEFT JOIN {reservation_location_rooms} l_rooms ".
						"ON (location.location_id = l_rooms.location_id)";

			$params = array();
			$sql .= " WHERE (";

			$pagesView =& $this->_container->getComponent("pagesView");
			$private_spase = $pagesView->getPrivateSpaceByUserId($_user_id);
			if ($private_spase === false) {
				return false;
			}
			if (!empty($private_spase)) {
				$private_room_id = $private_spase[0]["page_id"];
			} else {
				$private_room_id = 0;
			}

			if ($private_room_id > 0) {
				$sql .= "location.use_private_flag = ". _ON . " OR ";
			}
			$sql .= "location.allroom_flag = ". _ON;
			if (!empty($room_id_arr)) {
				$sql .= " OR location.allroom_flag = ". _OFF ." AND l_rooms.room_id IN (".implode(",",$room_id_arr).")";
			}
			$sql .= ") ";
			$sql .= " GROUP BY location.category_id";
			$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getCountLocationByCategory"));
		} else {
			$params = array();
			$sql = "SELECT category_id, COUNT(*) AS location_count";
			$sql .= " FROM {reservation_location}";
			$sql .= " GROUP BY category_id";
			$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getCountLocationByCategory"));
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
		}
		return $result;
	}

	/**
	 * 施設取得
	 *
	 * @access	private
	 */
	function _getCountLocationByCategory(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[$row["category_id"]] = intval($row["location_count"]);
		}
		return $result;
	}

	/**
	 * 施設件数取得
	 *
	 * @access	public
	 */
	function getCountLocation($location_id = null)
	{
		$_user_auth_id = $this->_session->getParameter("_user_auth_id");
		$_user_id = $this->_session->getParameter("_user_id");

		$room_id_arr = $this->_request->getParameter("room_id_arr");

		if (isset($location_id)) {
			$sql = "SELECT COUNT(*) ";
			$sql .= "FROM {reservation_location} location ".
					"LEFT JOIN {reservation_location_rooms} l_rooms ".
						"ON (location.location_id = l_rooms.location_id) ";

			$params = array("location_id"=>$location_id);
			$sql .= "WHERE location.location_id = ? ";
			if ($_user_auth_id != _AUTH_ADMIN) {
				$sql .= "AND (";

				$pagesView =& $this->_container->getComponent("pagesView");
				$private_spase = $pagesView->getPrivateSpaceByUserId($_user_id);
				if ($private_spase === false) {
					return false;
				}
				if (!empty($private_spase)) {
					$private_room_id = $private_spase[0]["page_id"];
				} else {
					$private_room_id = 0;
				}

				if ($private_room_id > 0) {
					$sql .= "location.use_private_flag = ". _ON . " OR ";
				}
				$sql .= "location.allroom_flag = ". _ON;
				if (!empty($room_id_arr)) {
					$sql .= " OR location.allroom_flag = ". _OFF ." AND l_rooms.room_id IN (".implode(",",$room_id_arr).")";
				}
				$sql .= ") ";
			}
			$result = $this->_db->execute($sql, $params, null, null, false);
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
			if (intval($result[0][0]) > 0) {
				return 1;
			} else {
				return 0;
			}
		} else {
			$params = array();
			$sql = "SELECT COUNT(*) ";
			$sql .= "FROM {reservation_location}";
			$result = $this->_db->execute($sql, $params, null, null, false);
			if ($result === false) {
				$this->_db->addError();
				return false;
			}
			return intval($result[0][0]);
		}
	}

	/**
	 * 時間枠取得
	 *
	 * @access	public
	 */
	function getTimeframes($divided_flag=false)
	{
		$func_params = array($divided_flag);

		$result = $this->_db->selectExecute('reservation_timeframe', null, null, 0, 0, array($this, '_getTimeframesCallback'), $func_params);

		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	function _getTimeframesCallback($recordSet, $params = array(false))
	{
		$ret = array();

		$divided_flag = $params[0];

 		$commonMain =& $this->_container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");

		while ($row = $recordSet->fetchRow()) {

			$start_time = timezone_date($row['start_time'], false, 'His');
			$end_time = timezone_date($row['end_time'], false, 'His');

			// 日跨ぎ 分割希望時
			if($divided_flag == true && $start_time>$end_time) {
				$ret_row = $this->__getTimeframesCallback($timezoneMain, $row, $start_time, '240000');
				$ret[$start_time] = $ret_row;
				$ret_row = $this->__getTimeframesCallback($timezoneMain, $row, '000000', $end_time);
				$ret['000000'] = $ret_row;
			}
			else {
				$ret_row = $this->__getTimeframesCallback($timezoneMain, $row, $start_time, $end_time);
				$ret[$start_time] = $ret_row;
			}
		}
		ksort($ret);
		return $ret;
	}
	function __getTimeframesCallback(&$timezoneMain, $row, $start_time, $end_time)
	{
		$ret = $row;

		$ret['start_time_view'] = $start_time;
		$ret['end_time_view'] = $end_time;

		$ret['left'] = $this->TimeDiff('000000', $start_time) * RESERVATION_DEF_V_INTERVAL;
		$ret['top'] = $this->TimeDiff('000000', $start_time) * RESERVATION_DEF_H_INTERVAL;

		if($end_time == '000000') {
			$ret['width'] = $this->TimeDiff($ret['start_time_view'], '240000') * RESERVATION_DEF_V_INTERVAL;
			$ret['height'] = $this->TimeDiff($ret['start_time_view'], '240000') * RESERVATION_DEF_H_INTERVAL;
		}
		else {
			$ret['width'] = $this->TimeDiff($ret['start_time_view'], $ret['end_time_view']) * RESERVATION_DEF_V_INTERVAL;
			$ret['height'] = $this->TimeDiff($ret['start_time_view'], $ret['end_time_view']) * RESERVATION_DEF_H_INTERVAL;
		}

		$ret['start_time_view_hour'] = substr($ret['start_time_view'], 0, 2);
		$ret['start_time_view_min'] =  substr($ret['start_time_view'], 2, 2);
		$ret['end_time_view_hour'] = substr($ret['end_time_view'], 0, 2);
		$ret['end_time_view_min'] = substr($ret['end_time_view'], 2, 2);


		$ret["start_time_original_str"] = $this->dateFormat(date('Ymd').$ret["start_time"], $ret["timezone_offset"], false, _SHORT_TIME_FORMAT);
		$ret["end_time_original_str"] = $this->dateFormat(date('Ymd').$ret["end_time"], $ret["timezone_offset"], false, _SHORT_TIME_FORMAT);
		$ret["start_time_original"] = $this->dateFormat(date('Ymd').$ret["start_time"], $ret["timezone_offset"], false, "His");
		$ret["end_time_original"] = $this->dateFormat(date('Ymd').$ret["end_time"], $ret["timezone_offset"], false, "His");

		$ret["start_time_original_hour"] = substr($ret['start_time_original'], 0, 2);
		$ret["start_time_original_min"] = substr($ret['start_time_original'], 2, 2);
		$ret["end_time_original_hour"] = substr($ret['end_time_original'], 0, 2);
		$ret["end_time_original_min"] = substr($ret['end_time_original'], 2, 2);

		$ret['timezone_constant_string'] = $timezoneMain->getLangTimeZone($ret['timezone_offset'], false);
		$ret['timezone_string'] = $timezoneMain->getLangTimeZone($ret['timezone_offset']);

		return $ret;
	}

	/**
	 * 時間枠数取得
	 *
	 * @access	public
	 */
	function getTimeframesCount()
	{
		$result = $this->_db->countExecute('reservation_timeframe');
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 時間枠取得
	 *
	 * @access	public
	 */
	function getTimeframe($timeframe_id)
	{
		$result = $this->_db->selectExecute('reservation_timeframe', array('timeframe_id'=>$timeframe_id), null, 0, 0, array($this, '_getTimeframesCallback'));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$timeframe = array_shift($result);
		return $timeframe;
	}

	/**
	 * 開始時間による時間枠取得(GMT)
	 *
	 * @access	public
	 */
	function getTimeframeByStartTime($start_time_str)
	{
		$result = $this->_db->selectExecute('reservation_timeframe', array('start_time'=>$start_time_str), null, 0, 0, array($this, '_getTimeframesCallback'));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$timeframe = array_shift($result);
		return $timeframe;
	}

	/**
	 * 終了時間による時間枠取得(GMT)
	 *
	 * @access	public
	 */
	function getTimeframeByEndTime($end_time_str)
	{
		$result = $this->_db->selectExecute('reservation_timeframe', array('end_time'=>$end_time_str), null, 0, 0, array($this, '_getTimeframesCallback'));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		$timeframe = array_shift($result);
		return $timeframe;
	}

	/**
	 * 時間枠重なりチェック(GMT)
	 *
	 * @access	public
	 */
	function getTimeframeDuplicate($timeframe_id, $start_time, $end_time)
	{
		$sql = 'SELECT count(*) AS timeframe_count FROM  {reservation_timeframe} WHERE '
				. '( ? < start_time AND ? > start_time '
				. ' OR '
				. ' ? >= start_time AND CASE WHEN start_time < end_time THEN ?+240000 < end_time+240000 ELSE ? <= end_time END )';
		if(!empty($timeframe_id)) {
			$sql .= ' AND timeframe_id != ?';
		}

		$where_param = array($start_time, $end_time,$start_time, $start_time, $start_time);
		if(!empty($timeframe_id)) {
			$where_param[] = $timeframe_id;
		}
		$result = $this->_db->execute($sql, $where_param);
		if($result && isset($result[0])) {
			if($result[0]['timeframe_count']>0) {
				return false;
			}
		}

		//
		return true;
	}

	/**
	 * 施設に紐付くルーム取得
	 *
	 * @access	public
	 */
	function getLocationRoom($location_id)
	{
		$params = array("location_id"=>$location_id);
		$room_id_arr = $this->_request->getParameter("room_id_arr");

		$sql = "SELECT location.location_id, l_rooms.room_id, page.thread_num, page.private_flag, page.space_type, page.page_name";
		$sql .= " FROM {reservation_location} location".
				" INNER JOIN {reservation_location_rooms} l_rooms".
					" ON (location.location_id = l_rooms.location_id)".
				" INNER JOIN {pages} page".
					" ON (l_rooms.room_id = page.page_id)";
		$sql .= " WHERE location.location_id = ?".
				" AND l_rooms.room_id IN (".implode(",",$room_id_arr).")";

		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getLocationRoom"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	/**
	 * 施設に紐付くルーム取得
	 *
	 * @access	private
	 */
	function _getLocationRoom(&$recordSet)
	{
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$result = array();
		while ($row = $recordSet->fetchRow()) {
			if ($actionName == "reservation_action_edit_location_modify" ||
				$actionName == "reservation_view_main_reserve_add" ||
				$actionName == "reservation_view_main_reserve_modify" ||
				$actionName == "reservation_action_main_reserve_add" ||
				$actionName == "reservation_action_main_reserve_modify" ||
				$actionName == "reservation_action_main_reserve_mail" ||
				$actionName == "reservation_view_edit_import_init" ||
				$actionName == "reservation_action_edit_import" ||
				$actionName == "reservation_view_main_reserve_switch_location") {

				$result[] = $row["room_id"];
				continue;
			}

			$this->setReserveFlag($row);
			if ($row["reserve_flag"] == RESERVATION_PUBLIC) {
				$result[RESERVATION_PUBLIC][$row["room_id"]] = $row["page_name"];
				continue;
			}
			if ($row["reserve_flag"] == RESERVATION_GROUP) {
				$result[RESERVATION_GROUP][$row["room_id"]] = $row["page_name"];
				continue;
			}
			if ($row["reserve_flag"] == RESERVATION_PRIVATE) {
				$result[RESERVATION_PRIVATE][$row["room_id"]] = $row["page_name"];
				continue;
			}
		}
		return $result;
	}

	/**
	 * 施設の存在チェック
	 *
	 * @access	public
	 */
	function locationExists($location_id)
	{
		$count = $this->getCountLocation($location_id);
		if ($count === false) {
			return false;
		}
		if ($count > 0) {
			return true;
		}
		return false;
	}

	/**
	 * デフォルト値取得
	 *
	 * @access	public
	 */
	function getBlock($display_type=null)
	{
		$params = array(
			"block_id" => $this->_request->getParameter("block_id")
		);

		$result =& $this->_db->selectExecute("reservation_block", $params);
		if (empty($result)) {
			$result[0] = $this->getDefaultBlock();
		}
		if (empty($result)) {
			return false;
		}

		$reserve_block = $result[0];
		$room_id_arr = $this->_request->getParameter("room_id_arr");

		if (isset($display_type) && $display_type > 0) {
			$reserve_block["display_type"] = $display_type;
		}

		switch ($reserve_block["display_type"]) {
			case RESERVATION_DEF_MONTHLY:
			case RESERVATION_DEF_WEEKLY:
				$location_id = $this->_request->getParameter("location_id");
				if (!isset($location_id)) {
					$location_id = $reserve_block["location_id"];
				} else {
					$location_id = intval($location_id);
				}
				$result = $this->locationExists($location_id);
				if (!$result) {
					$location = $this->getFirstLocation();
					if ($location === false) {
						return false;
					}
					$location_id = intval($location["location_id"]);
				}
				$location =& $this->getLocation($location_id, false);
				if ($location === false) {
					return false;
				}
				$reserve_block["category_id"] = $location["category_id"];
				$reserve_block["location_id"] = $location["location_id"];
				break;
			case RESERVATION_DEF_LOCATION:
				$category_id = $this->_request->getParameter("category_id");
				if (!isset($category_id)) {
					$category_id = $reserve_block["category_id"];
				} else {
					$category_id = intval($category_id);
				}
				if ($category_id > 0) {
					$result = $this->categoryExists($category_id);
					if (!$result) {
						$category_id = $this->getFirstCategory();
						if ($category_id === false) {
							return false;
						}
					}
				}
				$reserve_block["category_id"] = $category_id;
				$reserve_block["location_id"] = 0;
				break;
			default:
		}

		//表示タイプの設定
		$type = substr($reserve_block["display_start_time"], -1);
		$hour = substr($reserve_block["display_start_time"], 0, -1);
		if ($reserve_block["display_start_time"] >= "0000" && $reserve_block["display_start_time"] < "2400") {
			$reserve_block["start_time_type"] = RESERVATION_DEF_START_TIME_FIXATION;
			$reserve_block["start_time_hour"] = $reserve_block["display_start_time"];
		} else {
			$reserve_block["start_time_type"] = RESERVATION_DEF_START_TIME_DEFAULT;
			$module_id = $this->_request->getParameter("module_id");
			$configView =& $this->_container->getComponent("configView");
			$config = $configView->getConfigByConfname($module_id, "start_time_hour");
			if ($config === false) {
				return false;
			}
			$reserve_block["start_time_hour"] = $config["conf_value"];
		}
		return $reserve_block;
	}

	// 2013.02.12 bugfix insert
	/**
	 * デフォルト値取得 by location_id
	 *
	 * @access	public
	 */
	function getBlockByLocationId()
	{
		$params = array(
			"block_id" => $this->_request->getParameter("block_id"),
			"location_id" => $this->_request->getParameter("location_id")
		);

		$result =& $this->_db->selectExecute("reservation_block", $params);
		if (empty($result)) {
			return false;
		}

		$reserve_block = $result[0];

		return $reserve_block;
	}

	/**
	 * 予約取得
	 *
	 * @access	public
	 */
	function getReserve($reserve_id)
	{
		$room_id_arr = $this->_request->getParameter("room_id_arr");

		$sql = "SELECT reserve.*, details.contact, details.description, details.rrule, " .
					"location.category_id, location.location_name, location.start_time AS location_start_time, " .
					"location.end_time AS location_end_time, location.timezone_offset AS location_timezone_offset, " .
					"page.page_name, page.private_flag, page.space_type" .
				" FROM {reservation_reserve} reserve" .
				" INNER JOIN {reservation_location} location".
						" ON (reserve.location_id = location.location_id)".
				" LEFT JOIN {reservation_reserve_details} details".
						" ON (reserve.reserve_details_id = details.reserve_details_id)" .
				" LEFT JOIN {pages} page" .
						" ON (reserve.room_id=page.page_id)" .
				" WHERE reserve.reserve_id = ?" .
				" AND reserve.room_id IN (0,".implode(",",$room_id_arr).")";

		$params = array(
			"reserve_id" => $reserve_id
		);

		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getReserve"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	/**
	 * 予約データ取得
	 *
	 * @access	private
	 */
	function &_getReserve(&$recordSet)
	{
		$authCheck =& $this->_container->getComponent("authCheck");

		$_user_id = $this->_session->getParameter("_user_id");

		$row = $recordSet->fetchRow();

		if ($row["space_type"] == _SPACE_TYPE_PUBLIC) {
			$row["reserve_flag"] = RESERVATION_PUBLIC;
		} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _ON) {
			$row["reserve_flag"] = RESERVATION_PRIVATE;
		} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _OFF) {
			$row["reserve_flag"] = RESERVATION_GROUP;
		} else {
			$row["reserve_flag"] = RESERVATION_MEMBERS;
		}

		$start_time = $this->dateFormat($row["start_time_full"], null, false);

		$row["input_start_date"] = $this->dateFormat($row["start_time_full"], $row["timezone_offset"], false, _INPUT_DATE_FORMAT);
		$row["start_date_view"] = substr($start_time, 0, 8);
		$row["start_time_view"] = substr($start_time, 8);
		$row["start_date_str"] = $this->dateFormat($row["start_time_full"], null, false, RESERVATION_DATE_FORMAT);
		$row["start_time_str"] = $this->dateFormat($row["start_time_full"], null, false, RESERVATION_TIME_FORMAT);

		$end_time = $this->dateFormat($row["end_time_full"], null, false, "YmdHis", true);

		$row["input_end_time"] = $this->dateFormat($row["end_time_full"], $row["timezone_offset"], false, _INPUT_DATE_FORMAT);
		$row["end_date_view"] = substr($end_time, 0, 8);
		$row["end_time_view"] = substr($end_time, 8);
		$row["end_date_str"] = $this->dateFormat($row["end_time_full"], null, false, RESERVATION_DATE_FORMAT, true);
		$row["end_time_str"] = $this->dateFormat($row["end_time_full"], null, false, RESERVATION_TIME_FORMAT, true);

		$row["rrule_str"] = $this->stringRRule($row["rrule"]);
		$row["rrule_arr"] = $this->parseRRule($row["rrule"]);
		$row["rrule_set_arr"] = $this->parseRRule($row["rrule"], true);

		if ($row["reserve_flag"] == RESERVATION_MEMBERS) {
			$user_authority_id = $this->_session->getParameter("_user_auth_id");
			$user_hierarchy = _OFF;
			$reserve_hierarchy = _OFF;
		} else {
			$reserve_hierarchy = $authCheck->getPageHierarchy($row["insert_user_id"], $row["room_id"]);
			$user_hierarchy = $authCheck->getPageHierarchy($_user_id, $row["room_id"]);
			$user_authority_id = $authCheck->getPageAuthId($_user_id, $row["room_id"]);
			if ($user_authority_id == _AUTH_CHIEF) {
				$user_authority_id = _AUTH_ADMIN;
			}
		}
		if ($user_authority_id > _AUTH_CHIEF || $user_hierarchy > $reserve_hierarchy || $row["insert_user_id"] == $_user_id) {
			$row["hasModifyAuth"] = _ON;
		} else {
			$row["hasModifyAuth"] = _OFF;
		}

		return $row;
	}

	/**
	 * 予約件数取得
	 *
	 * @access	public
	 */
	function getCountReserve($reserve_id)
	{
		$room_id_arr = $this->_request->getParameter("room_id_arr");

		$sql = "SELECT COUNT(*)" .
				" FROM {reservation_reserve} reserve" .
				" INNER JOIN {reservation_location} location".
						" ON (reserve.location_id = location.location_id)".
				" WHERE reserve.reserve_id = ?" .
				" AND reserve.room_id IN (0,".implode(",",$room_id_arr).")";

		$params = array(
			"reserve_id" => $reserve_id
		);

		$result = $this->_db->execute($sql, $params, null, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if (intval($result[0][0]) > 0) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * 予約の存在チェック
	 *
	 * @access	public
	 */
	function reserveExists($reserve_id)
	{
		$count = $this->getCountReserve($reserve_id);
		if ($count === false) {
			return false;
		}
		if ($count > 0) {
			return true;
		}
		return false;
	}

	/**
	 * 予約ID取得
	 *
	 * @access	public
	 */
	function getReserveIdByFirstReserve($reserve_details_id)
	{
		$params = array("reserve_details_id"=>$reserve_details_id);
		$reserve_id = $this->_db->minExecute("reservation_reserve", "reserve_id", $params);
		if ($reserve_id === false) {
			return false;
		}
		return $reserve_id;
	}

	/**
	 * 予約データ取得
	 *
	 * @access	public
	 */
	function getReserveByDate($from_date, $to_date=null)
	{
		$reserve_block = $this->_request->getParameter("reserve_block");

		if (!isset($to_date)) {
			$to_date = $from_date;
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

		$sql = "SELECT reserve.*, " .
					"location.start_time AS location_start_time, location.end_time AS location_end_time, location.timezone_offset AS location_timezone_offset, " .
					"page.private_flag, page.space_type" .
				" FROM {reservation_reserve} reserve" .
				" INNER JOIN {reservation_location} location ON (reserve.location_id=location.location_id)" .
				" LEFT JOIN {pages} page ON (reserve.room_id=page.page_id)";
		$sql .= " WHERE ((reserve.start_date >= ? AND reserve.start_date <= ?)" .
					" OR (reserve.end_date >= ? AND reserve.end_date <= ?)" .
					" OR (reserve.start_date <= ? AND reserve.end_date >= ?))";

		switch ($reserve_block["display_type"]) {
			case RESERVATION_DEF_LOCATION:
				if ($reserve_block["category_id"] != 0) {
					$sql .= " AND location.category_id = ?";
					$params["category_id"] = $reserve_block["category_id"];
				}
				$sql .= " ORDER BY reserve.location_id, reserve.allday_flag DESC, reserve.start_date, reserve.start_time, reserve.end_date, reserve.end_time, reserve.reserve_id";
				break;
			case RESERVATION_DEF_WEEKLY:
			case RESERVATION_DEF_MONTHLY:
			default:
				$sql .= " AND reserve.location_id = ?";
				$params["location_id"] = $reserve_block["location_id"];
				$sql .= " ORDER BY reserve.allday_flag DESC, reserve.start_date, reserve.start_time, reserve.end_date, reserve.end_time, reserve.location_id, reserve.reserve_id";
		}

		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getReserveByDate"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	/**
	 * 予約データ取得
	 *
	 * @access	private
	 */
	function &_getReserveByDate(&$recordSet)
	{
		$result = array();
		$reserve_block = $this->_request->getParameter("reserve_block");

		while ($row = $recordSet->fetchRow()) {
			if ($row["allday_flag"] == _ON) {
				$start_time = $this->dateFormat($row["start_date"].$row["start_time"], $row["location_timezone_offset"], false, "Ymd");
				$start_time .= $this->dateFormat($row["location_start_time"], $row["location_timezone_offset"], false, "His");
				$start_time = $this->dateFormat($start_time, $row["location_timezone_offset"], true, "YmdHis");

				$end_time = $this->dateFormat($row["end_date"].$row["end_time"], $row["location_timezone_offset"], false, "Ymd", true);
				$end_time .= $this->dateFormat($row["location_end_time"], $row["location_timezone_offset"], false, "His");
				$end_time = $this->dateFormat($end_time, $row["location_timezone_offset"], true, "YmdHis", true);

				$row["start_date"] = substr($start_time, 0, 8);
				$row["start_time"] = substr($start_time, 8);
				$row["end_date"] = substr($end_time, 0, 8);
				$row["end_time"] = substr($end_time, 8);
				$row["timezone_offset"] = $row["location_timezone_offset"];
			}
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
				$row["reserve_flag"] = RESERVATION_PUBLIC;
			} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _ON) {
				$row["reserve_flag"] = RESERVATION_PRIVATE;
			} elseif ($row["space_type"] == _SPACE_TYPE_GROUP && $row["private_flag"] == _OFF) {
				$row["reserve_flag"] = RESERVATION_GROUP;
			} else {
				$row["reserve_flag"] = RESERVATION_MEMBERS;
			}
			for ($i=0; $i<=$num; $i++) {
				if ($i == 0) {
					$date = date("Ymd", $start_timestamp);
					$row["start_time"] = date("His", $start_timestamp);
					$row["start_time_str"] = date(_SHORT_TIME_FORMAT, $start_timestamp);
				} else {
					$date = date("Ymd", $start_timestamp + $i * 86400);
					$row["start_time"] = date("His", mktime(0,0,0));
					$row["start_time_str"] = date(_SHORT_TIME_FORMAT, mktime(0,0,0));
				}

				if ($i == $num) {
					if (date("Hi",$end_timestamp+1) == "0000") {
						$timeFormat = "240000";
						$timeStrFormat = str_replace("H", "24", _SHORT_TIME_FORMAT);
						$timeStrFormat = str_replace("i", "00", $timeStrFormat);
					} else {
						$timeFormat = "His";
						$timeStrFormat = _SHORT_TIME_FORMAT;
					}
					$row["end_time"] = date($timeFormat, $end_timestamp);
					$row["end_time_str"] = date($timeStrFormat, $end_timestamp);
				} else {
					$row["end_time"] = "240000";
					$timeStrFormat = str_replace("H", "24", _SHORT_TIME_FORMAT);
					$timeStrFormat = str_replace("i", "00", $timeStrFormat);
					$row["end_time_str"] = date($timeStrFormat, mktime(0,0,0));
				}
				$row["start_date"] = $date;
				$row["end_date"] = $date;

				$title = $row["title"];
				$row["short_title"] = mb_substr($title, 0, RESERVATION_SHORT_STRLEN, INTERNAL_CODE);
				if ($row["short_title"] != $title) {
					$row["short_title"] .= _SEARCH_MORE;
				}

				//値セット
				switch ($reserve_block["display_type"]) {
					case RESERVATION_DEF_WEEKLY:
						if (!isset($result[$date])) {
							$result[$date] = array();
							$height[$date] = 0;
						}
						$top = $this->TimeDiff("000000", $row["start_time"]) * RESERVATION_DEF_H_INTERVAL;

						//空白時間のセット(dummy)
						if (($top-$height[$date]) > 0) {
							$result[$date][] = array("height"=>($top-$height[$date]));
						}
						//予約時間のセット
						$row["height"] = $this->TimeDiff($row["start_time"], $row["end_time"]) * RESERVATION_DEF_H_INTERVAL;
						$height[$date] += ($top-$height[$date]) + $row["height"];
						$result[$date][] = $row;
						break;
					case RESERVATION_DEF_LOCATION:
						if (!isset($result[$row["location_id"]][$date])) {
							$result[$row["location_id"]][$date] = array();
							$width[$row["location_id"]][$date] = 0;
						}
						$left = $this->TimeDiff("000000", $row["start_time"]) * RESERVATION_DEF_V_INTERVAL;

						//空白時間のセット(dummy)
						if (($left-$width[$row["location_id"]][$date]) > 0) {
							$result[$row["location_id"]][$date][] = array("width"=>($left-$width[$row["location_id"]][$date]));
						}
						//予約時間のセット
						$row["width"] = $this->TimeDiff($row["start_time"], $row["end_time"]) * RESERVATION_DEF_V_INTERVAL;
						$width[$row["location_id"]][$date] += ($left - $width[$row["location_id"]][$date]) + $row["width"];
						$result[$row["location_id"]][$date][] = $row;
						break;
					default:
						if (!isset($result[$date])) {
							$result[$date] = array();
						}
						$result[$date][] = $row;
				}
			}
		}
		return $result;
	}

	/**
	 * 予約取得
	 *
	 * @access	public
	 */
	function getAddReserve()
	{
		$date = $this->_request->getParameter("reserve_date");
		$current_timestamp = mktime(0, 0, 0, substr($date,4,2), substr($date,6,2), substr($date,0,4));

		$start_time = $this->_request->getParameter("start_time");
		$end_time = $this->_request->getParameter("end_time");

		if (empty($start_time) && empty($end_time)) {
			$time = $this->_request->getParameter("time");
			if (!empty($time)) {
				$hour = sprintf("%02d", intval(substr($time,0,2)-1));
			} else {
				$hour = timezone_date(null, false, "H");
			}
			if ($hour == "23") {
				$start_time = $hour . "0000";
			} else {
				$start_time = sprintf("%02d", intval($hour) + 1) . "0000";
			}
			if (substr($start_time,0,2) == "23") {
				$end_time = "240000";
			} else {
				$end_time = sprintf("%02d", intval(substr($start_time,0,2))+1).substr($start_time,2,2) . "00";
			}
		}

		$rrule = $this->parseRRule("", true);

		$title_icon = $this->_request->getParameter("icon_name");

		$reserve = array(
			"room_id" => intval($this->_request->getParameter("reserve_room_id")),
			"location_id" => $this->_request->getParameter("location_id"),
			"category_id" => $this->_request->getParameter("category_id"),
			"title" => $this->_request->getParameter("title"),
			"title_icon" => $title_icon,
			"allday_flag" => intval($this->_request->getParameter("allday_flag")),
			"start_date" => date("Ymd", $current_timestamp),
			"input_start_date" => date(_INPUT_DATE_FORMAT, $current_timestamp),
			"start_time_view" => $start_time,
			"end_date" => date("Ymd", $current_timestamp),
			"input_end_date" => date(_INPUT_DATE_FORMAT, $current_timestamp),
			"end_time_view" => $end_time,
			"timezone_offset" => $this->_session->getParameter("_timezone_offset"),
			"contact" => "",
			"description" => "",
			"rrule" => "",
			"rrule_arr" => $this->parseRRule(""),
			"rrule_set_arr" => $this->parseRRule("", true),
		);

		return $reserve;
	}

	/**
	 * 施設に紐付く予約できる権限のルーム取得
	 *
	 * @param	&$location	施設情報
	 * @access	public
	 */
	function getAddLocationRoom(&$location)
	{
		$pagesView =& $this->_container->getComponent("pagesView");
		$_user_auth_id = $this->_session->getparameter('_user_auth_id');

		$room_id_arr = $pagesView->getRoomIdByUserId(null, $location["add_authority"]);
		if ($room_id_arr === false) {
			return false;
		}
		if ($location['add_authority'] == _AUTH_GENERAL) {
			$search_result = array_search(_SELF_TOPGROUP_ID, $room_id_arr);
			if ($search_result) {
				unset($room_id_arr[$search_result]);
			}
		}
		$private_space = $pagesView->getPrivateSpaceByUserId($this->_session->getParameter("_user_id"));
		if ($private_space === false) {
			return false;
		}
		if (!empty($private_space)) {
			$private_room_id = $private_space[0]["page_id"];
		} else {
			$private_room_id = 0;
		}

		$actionChain =& $this->_container->getComponent('ActionChain');
		$actionName = $actionChain->getCurActionName();

		$location['hasPrivateReserveAuthority'] = false;
		if ($location['use_private_flag'] == _ON && !empty($room_id_arr)
			&& $actionName != 'reservation_view_main_reserve_switch_location'
			&& $actionName != 'reservation_view_edit_import_init') {

			if ($location['use_auth_flag'] == RESERVATION_USE_AUTH_USER
				&& ($_user_auth_id == _AUTH_ADMIN || $location['add_authority'] <= $_user_auth_id)) {
				$location['hasPrivateReserveAuthority'] = true;
			} elseif ($location['use_auth_flag'] == RESERVATION_USE_AUTH_ROOM
				&& count($room_id_arr) > 1) {
				$location['hasPrivateReserveAuthority'] = true;
			}
		}
		if (!$location['hasPrivateReserveAuthority']) {
			$search_result = array_search($private_room_id, $room_id_arr);
			if ($search_result != 0 || $search_result === 0) {
				unset($room_id_arr[$search_result]);
			}
		}

		if (count($room_id_arr) == 0) {
			return false;
		}

		if ($location["allroom_flag"] == _ON) {
			return $room_id_arr;
		}
		$params = array("location_id" => $location["location_id"]);
		$sql = "SELECT location.location_id, l_rooms.room_id, page.thread_num, page.private_flag, page.space_type, page.page_name";
		$sql .= " FROM {reservation_location} location".
				" INNER JOIN {reservation_location_rooms} l_rooms".
					" ON (location.location_id = l_rooms.location_id)".
				" INNER JOIN {pages} page".
					" ON (l_rooms.room_id = page.page_id)";
		$sql .= " WHERE location.location_id = ?".
				" AND l_rooms.room_id IN (".implode(",",$room_id_arr).")";

		$allow_add_rooms = $this->_db->execute($sql, $params, null, null, true, array($this,"_getLocationRoom"));
		if ($allow_add_rooms === false) {
			$this->_db->addError();
			return false;
		}
		if ($location['hasPrivateReserveAuthority']) {
			$allow_add_rooms[] = $private_room_id;
		}

		if ($location['use_auth_flag'] == RESERVATION_USE_AUTH_ROOM
			&& count($allow_add_rooms) == 0) {
			$location['hasPrivateReserveAuthority'] = false;
		}

		return $allow_add_rooms;
	}

	/**
	 * 新着で表示するブロックIDを取得
	 *
	 * @access	public
	 */
	function getBlockIdByWhatsnew()
	{
		$reserve_room_id = $this->_request->getParameter("reserve_room_id");
		if (!is_null($reserve_room_id) || $reserve_room_id != 0) {
			$params = array("room_id"=>$reserve_room_id);
			$result = $this->_db->selectExecute("reservation_block", $params, null, 1);
			if (!empty($result)) {
				return $result[0]["block_id"];
			}
		}
		$params = array("room_id"=>_SPACE_TYPE_PUBLIC);
		$result = $this->_db->selectExecute("reservation_block", $params, null, 1);
		if (!empty($result)) {
			return $result[0]["block_id"];
		}
		return $this->_request->getParameter("block_id");
	}

	/**
	 * 予約日チェック
	 * 　($start_time_fullはrequestの値)
	 *
	 * @access	public
	 */
	function checkReserveTime($start_time_full, $end_time_full)
	{
		if (substr($start_time_full, 0, 8) != substr($end_time_full, 0, 8)) {
			return false;
		}
		if (substr($start_time_full, 0, 4) < RESERVATION_SELECT_MIN_YEAR || substr($start_time_full, 0, 4) > RESERVATION_SELECT_MAX_YEAR) {
			return false;
		}

		$location = $this->_request->getParameter("location");
		$timezone_offset = $this->_request->getParameter("timezone_offset");

		$start_time_full = $this->dateFormat($start_time_full, $timezone_offset, true);
		$start_time_full = $this->dateFormat($start_time_full, $location["timezone_offset"], false);

		$end_time_full = $this->dateFormat($end_time_full, $timezone_offset, true);
		$end_time_full = $this->dateFormat($end_time_full, $location["timezone_offset"], false, "YmdHis", true);

		$wday = date("w", mktime(substr($start_time_full,8,2), substr($start_time_full,10,2), substr($start_time_full,12,2),
								substr($start_time_full,4,2), substr($start_time_full,6,2), substr($start_time_full,0,4)));

		if (!in_array($this->_wdayArrary[$wday], $location["time_table_arr"])) {
			return false;
		}
		if (substr($location["start_time"],8) <= substr($start_time_full,8) && substr($start_time_full,8) < substr($location["end_time"],8) &&
			substr($location["start_time"],8) < substr($end_time_full,8) && substr($end_time_full,8) <= substr($location["end_time"],8)) {

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 予約チェック(重複)
	 *
	 * @access	public
	 */
	function checkReserveDuplication()
	{
		$location = $this->_request->getParameter("location");
		$reserve_id = $this->_request->getParameter("reserve_id");
		$reserve_id_arr = $this->_request->getParameter("reserve_id_arr");

		if ($location["duplication_flag"] == _ON) {
			return true;
		}

		$params = array();

		$sql = "SELECT start_time_full, COUNT(*) AS reserve_count" .
				" FROM {reservation_reserve}" .
				" WHERE location_id = ?";
		$params[] = $location["location_id"];

		if (isset($reserve_id_arr)) {
			$sql .= " AND reserve_id NOT IN (".implode(",",$reserve_id_arr).")";
		} elseif (isset($reserve_id)) {
			$sql .= " AND reserve_id <> ?";
			$params[] = $reserve_id;
		}
		$sql .= " AND (";

		$repeat_time = $this->_request->getParameter("repeat_time");
		if (empty($repeat_time)) {
			$i = 0;
		} else {
			$i = count($repeat_time);
		}
		$repeat_time[$i] = array(
			"start_time_full" => $this->_request->getParameter("start_time_full"),
			"end_time_full" => $this->_request->getParameter("end_time_full")
		);

		foreach ($repeat_time as $i=>$values) {
			$sql .= ($i != 0 ? " OR " : "")."(";
			$sql .= "start_time_full >= ? AND start_time_full < ?" .
					" OR " .
					"end_time_full > ? AND end_time_full <= ?" .
					" OR " .
					"start_time_full <= ? AND end_time_full > ?";
			$sql .= ")";

			$start_time_full = timezone_date($values["start_time_full"]);
			$end_time_full = timezone_date($values["end_time_full"]);

			$params[] = $start_time_full;
			$params[] = $end_time_full;
			$params[] = $start_time_full;
			$params[] = $end_time_full;
			$params[] = $start_time_full;
			$params[] = $start_time_full;
		}
		$sql .= ")";
		$sql .=" GROUP BY start_time_full";

		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_checkReserveDuplication"));
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if (!empty($result) && is_array($result)) {
			return $result;
		} else {
			return true;
		}
	}
	/**
	 * 予約チェック(重複)
	 *
	 * @access	private
	 */
	function _checkReserveDuplication(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$start_date = timezone_date($row["start_time_full"], false, "Ymd");
			if ($row["reserve_count"] > 0 && !isset($result[$start_date])) {
				$result[$start_date] = timezone_date($row["start_time_full"], false, _DATE_FORMAT);
			}
		}
		if (!empty($result) && count($result) > 0) {
			return $result;
		} else {
			return true;
		}
	}

	/**
	 * メール取得
	 *
	 * @access	public
	 */
	function getMailConfig()
	{
		$module_id = $this->_request->getParameter("module_id");

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
			return false;
		}

		$result = array();
		if (defined($config["mail_send"]["conf_value"])) {
			$result["mail_send"] = constant($config["mail_send"]["conf_value"]);
		} else {
			$result["mail_send"] = intval($config["mail_send"]["conf_value"]);
		}
		if (defined($config["mail_authority"]["conf_value"])) {
			$result["mail_authority"] = constant($config["mail_authority"]["conf_value"]);
		} else {
			$result["mail_authority"] = intval($config["mail_authority"]["conf_value"]);
		}
		if (defined($config["mail_subject"]["conf_value"])) {
			$result["mail_subject"] = constant($config["mail_subject"]["conf_value"]);
		} else {
			$result["mail_subject"] = $config["mail_subject"]["conf_value"];
		}
		if (defined($config["mail_body"]["conf_value"])) {
			$result["mail_body"] = preg_replace("/\\\\n/s", "\n", constant($config["mail_body"]["conf_value"]));
		} else {
			$result["mail_body"] = preg_replace("/\\\\n/s", "\n", $config["mail_body"]["conf_value"]);
		}

		return $result;
	}

	/**
	 * 予約の繰り返しのID取得
	 *
	 * @access	public
	 */
	function getRepeatReserveId($reserve_details_id)
	{
		$sql = "SELECT reserve_id" .
				" FROM {reservation_reserve}" .
				" WHERE reserve_details_id = ?";

		$params = array(
			"reserve_details_id" => $reserve_details_id
		);

		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_getRepeatReserveId"));
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	/**
	 * 予約の繰り返しのID取得
	 *
	 * @access	private
	 */
	function _getRepeatReserveId(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[] = $row["reserve_id"];
		}
		return $result;

	}

	/**
	 * パース処理
	 *
	 * @access	public
	 */
	function &defaultRRule()
	{
		$result_array = array(
			"FREQ" => "NONE",
			"NONE" => array(),
			"YEARLY" => array("FREQ"=>"YEARLY"),
			"MONTHLY" => array("FREQ"=>"MONTHLY"),
			"WEEKLY" => array("FREQ"=>"WEEKLY"),
			"DAILY" => array("FREQ"=>"DAILY"),
			"COUNT" => 3,
			"UNTIL" => timezone_date(null, true, "YmdHis"),
			"UNTIL_VIEW" => timezone_date(null, false, _DATE_FORMAT),
			"REPEAT_COUNT" => _ON,
			"REPEAT_UNTIL" => _OFF
		);
		$wday = timezone_date(null, true, "w");
		$month = timezone_date(null, true, "m");
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
			"BYDAY" => array($this->_wdayArrary[$wday])
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

		if ($rrule_str != "") {
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
						$result_array[$key] = substr($val,0,8).substr($val,-6);
						$result_array["UNTIL_VIEW"] = $this->dateFormat(substr($val,0,8).substr($val,-6), null, false, _DATE_FORMAT, true);
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
		$result_str = "";
		if (!is_array($rrule)) {
			$rrule = $this->parseRRule($rrule);
			if (empty($rrule)) { return ""; }
		}
		$freq = $rrule["FREQ"];
		if (!isset($rrule[$freq])) {
			$rrule[$freq] = $rrule;
		}

		$bymonth_str = "";
		if (isset($rrule[$freq]["BYMONTH"])) {
			foreach ($rrule[$freq]["BYMONTH"] as $i=>$val) {
				$bymonth_str .= RESERVATION_RRULE_PAUSE.sprintf(RESERVATION_RRULE_MONTH, $val);
			}
		}

		$byday_str = "";
		if (isset($rrule[$freq]["BYDAY"])) {
			foreach ($rrule[$freq]["BYDAY"] as $i=>$val) {
				$w = substr($val, -2);
				$n = intval(substr($val, 0, -2));
				$index = array_search($w, $this->_wdayArrary);
				if ($index !== false && $index !== null) {
					$w_name = $this->_weekLongNameArray[$index];
				} else {
					continue;
				}
				if ($freq == "WEEKLY") {
					$byday_str .= RESERVATION_RRULE_PAUSE;
				} else {
					switch ($n) {
						case 1:
							$byday_str .= ($freq == "MONTHLY" ? RESERVATION_RRULE_PAUSE : "<br />"). RESERVATION_RRULE_WEEK_FIRST;
							break;
						case 2:
							$byday_str .= ($freq == "MONTHLY" ? RESERVATION_RRULE_PAUSE : "<br />"). RESERVATION_RRULE_WEEK_SECOND;
							break;
						case 3:
							$byday_str .= ($freq == "MONTHLY" ? RESERVATION_RRULE_PAUSE : "<br />"). RESERVATION_RRULE_WEEK_THIRD;
							break;
						case 4:
							$byday_str .= ($freq == "MONTHLY" ? RESERVATION_RRULE_PAUSE : "<br />"). RESERVATION_RRULE_WEEK_FOURTH;
							break;
						default:
							$byday_str .= ($freq == "MONTHLY" ? RESERVATION_RRULE_PAUSE : "<br />"). RESERVATION_RRULE_WEEK_END;
					}
				}
				$byday_str .= $w_name;
			}
		}

		$bymonthday_str = "";
		if (isset($rrule[$freq]["BYMONTHDAY"])) {
			foreach ($rrule[$freq]["BYMONTHDAY"] as $i=>$val) {
				$bymonthday_str .= RESERVATION_RRULE_PAUSE. sprintf(RESERVATION_RRULE_DAY, $val);
			}
		}

		switch ($freq) {
			case "NONE":
				$result_str .= RESERVATION_RRULE_NONE;
				break;
			case "YEARLY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= RESERVATION_RRULE_EVERY_YEAR;
				} else {
					$result_str .= sprintf(RESERVATION_RRULE_INTERVAL_YEAR, $rrule[$freq]["INTERVAL"]);
				}
				$result_str .= $bymonth_str;
				if ($byday_str == "") {
					$byday_str = "<br />".RESERVATION_RRULE_STARTDATE;
				}
				$result_str .= $byday_str;
				break;
			case "MONTHLY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= RESERVATION_RRULE_EVERY_MONTH;
				} else {
					$result_str .= sprintf(RESERVATION_RRULE_INTERVAL_MONTH, $rrule[$freq]["INTERVAL"]);
				}
				$result_str .= $byday_str;
				$result_str .= $bymonthday_str;
				break;
			case "WEEKLY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= RESERVATION_RRULE_EVERY_WEEK;
				} else {
					$result_str .= sprintf(RESERVATION_RRULE_INTERVAL_WEEK, $rrule[$freq]["INTERVAL"]);
				}
				$result_str .= $byday_str;
				break;
			case "DAILY":
				if ($rrule[$freq]["INTERVAL"] == 1) {
					$result_str .= RESERVATION_RRULE_EVERY_DAY;
				} else {
					$result_str .= sprintf(RESERVATION_RRULE_INTERVAL_DAY, $rrule[$freq]["INTERVAL"]);
				}
				break;
			default:
		}

		if (isset($rrule["UNTIL"])) {
			$result_str .= "<br />";
			$result_str .= $this->dateFormat(substr($rrule["UNTIL"],0,8).substr($rrule["UNTIL"],-6), null, false, RESERVATION_RRULE_UNTIL, true);
		} elseif (isset($rrule["COUNT"])) {
			$result_str .= "<br />";
			$result_str .= sprintf(RESERVATION_RRULE_COUNT, $rrule["COUNT"]);
		}
		return $result_str;
	}


	/**
	 * 繰返しの日付を取得
	 *
	 * @access	public
	 */
	function getInputRRule()
	{
		$rrule = array();
		$error = array();

		$repeat_flag = intval($this->_request->getParameter("repeat_flag"));
		if ($repeat_flag == _OFF) {
			$rrule["FREQ"] = "NONE";
			return $rrule;
		}

		$rrule_term = $this->_request->getParameter("rrule_term");
		if ($rrule_term != "COUNT" && $rrule_term != "UNTIL") {
			$error["error_mess"] = _INVALID_INPUT;
			return $error;
		}

		if ($rrule_term == "COUNT") {
			$rrule_count = intval($this->_request->getParameter("rrule_count"));
			if (empty($rrule_count) && $rrule_count !== 0) {
				$error["error_mess"] = sprintf(_REQUIRED, RESERVATION_RRULE_LBL_COUNT);
				return $error;
			}
			if ($rrule_count <= 0) {
				$error["error_mess"] = sprintf(_NUMBER_ERROR, RESERVATION_RRULE_LBL_COUNT, 1, 999);
				return $error;
			}
			$rrule["COUNT"] = $rrule_count;
		}

		if ($rrule_term == "UNTIL") {
			$rrule_until = $this->_request->getParameter("rrule_until");

			if (empty($rrule_until)) {
				$error["error_mess"] = sprintf(_REQUIRED, RESERVATION_RRULE_LBL_UNTIL);
				return $error;
			}

			$reserve_date = $this->_request->getParameter("reserve_date");
			if ($reserve_date > $rrule_until) {
				$error["error_mess"] = RESERVATION_ERR_RESERVE_UNTIL_OVER;
				return $error;
			}

			$timezone_offset = $this->_request->getParameter("timezone_offset");
			$date = $this->dateFormat($rrule_until."240000", $timezone_offset, true);
			$rrule["UNTIL"] = substr($date, 0,8)."T".substr($date,8);

		}

		$repeat_freq = $this->_request->getParameter("repeat_freq");
		if (!isset($repeat_freq)) {
			$error["error_mess"] = _INVALID_INPUT;
			return $error;
		}

		$rrule_interval = $this->_request->getParameter("rrule_interval");
		if ($repeat_freq != "NONE" && !isset($rrule_interval) && !isset($rrule_interval[$repeat_freq])) {
			$error["error_mess"] = _INVALID_INPUT;
			return $error;
		}

		$rrule_byday = $this->_request->getParameter("rrule_byday");
		$rrule_bymonthday = $this->_request->getParameter("rrule_bymonthday");
		$rrule_bymonth = $this->_request->getParameter("rrule_bymonth");

		$rrule["FREQ"] = $repeat_freq;
		$rrule["INTERVAL"] = intval($rrule_interval[$repeat_freq]);

		switch ($repeat_freq) {
			case "DAILY":
				break;
			case "WEEKLY":
				if (!isset($rrule_byday) && !isset($rrule_byday[$repeat_freq])) {
					$error["error_mess"] = RESERVATION_RRULE_ERR_WDAY;
					return $error;
				}
				$byday = array();
				foreach ($rrule_byday[$repeat_freq] as $i=>$w) {
					if (!in_array($w, $this->_wdayArrary)) { continue; }
					$byday[] = $w;
				}
				if (empty($byday)) {
					$error["error_mess"] = RESERVATION_RRULE_ERR_WDAY;
					return $error;
				}
				$rrule["BYDAY"] = $byday;

				break;
			case "MONTHLY":
				if (!isset($rrule_byday) && !isset($rrule_byday[$repeat_freq]) && !isset($rrule_bymonthday) && !isset($rrule_bymonthday[$repeat_freq])) {
					$error["error_mess"] = RESERVATION_RRULE_ERR_WDAY_OR_DAY;
					return $error;
				}
				if (isset($rrule_byday) && isset($rrule_byday[$repeat_freq])) {
					$byday = array();
					foreach ($rrule_byday[$repeat_freq] as $i=>$val) {
						$w = substr($val, -2);
						$n = intval(substr($val, 0, -2));
						if ($n == 0) { $val = $w; }
						if (!in_array($w, $this->_wdayArrary)) { continue; }
						if (!($n >= -1 && $n <= 4)) { continue; }
						$byday[] = $val;
					}
					$rrule["BYDAY"] = $byday;
				}
				if (isset($rrule_bymonthday) && isset($rrule_bymonthday[$repeat_freq])) {
					$bymonthday = array();
					foreach ($rrule_bymonthday[$repeat_freq] as $i=>$val) {
						$val = intval($val);
						if ($val > 0 && $val <= 31) { $bymonthday[] = $val; }
					}
					$rrule["BYMONTHDAY"] = $bymonthday;
				}
				if (empty($byday) && empty($bymonthday)) {
					$error["error_mess"] = RESERVATION_RRULE_ERR_WDAY_OR_DAY;
					return $error;
				}

				break;
			case "YEARLY":
				if (!isset($rrule_bymonth) && !isset($rrule_bymonth[$repeat_freq])) {
					$error["error_mess"] = RESERVATION_RRULE_ERR_MONTH;
					return $error;
				}
				$bymonth = array();
				foreach ($rrule_bymonth[$repeat_freq] as $i=>$val) {
					$val = intval($val);
					if ($val > 0 && $val <= 12) {
						$bymonth[] = $val;
					}
				}
				if (empty($bymonth)) {
					$error["error_mess"] = RESERVATION_RRULE_ERR_MONTH;
					return $error;
				}
				$rrule["BYMONTH"] = $bymonth;
				if (isset($rrule_byday) && isset($rrule_byday[$repeat_freq])) {
					$byday = array();
					foreach ($rrule_byday[$repeat_freq] as $i=>$val) {
						$w = substr($val, -2);
						$n = intval(substr($val, 0, -2));
						if ($n == 0) { $val = $w; }
						if (!in_array($w, $this->_wdayArrary)) { continue; }
						if (!($n >= -1 && $n <= 4)) { continue; }
						$byday[] = $val;
					}
					$rrule["BYDAY"] = $byday;
				}

				break;
			default:
		}

		return $rrule;
	}

	/**
	 * 登録処理
	 *
	 * @access	public
	 */
	function getRepeatReserve($rrule)
	{
		$start_time_full = $this->_request->getParameter("start_time_full");
		$end_time_full = $this->_request->getParameter("end_time_full");

		$params = array(
			"start_date" => substr($start_time_full, 0, 8),
			"start_time" => substr($start_time_full, 8),
			"end_date" => substr($end_time_full, 0, 8),
			"end_time" => substr($end_time_full, 8),
			"timezone_offset" => $this->_request->getParameter("timezone_offset")
		);

		$repeat_time = array();

		$rrule["INDEX"] = 1;
		switch ($rrule["FREQ"]) {
			case "YEARLY":
				$repeat_time = $this->_repeatYearly($repeat_time, $params, $rrule, true);
				break;
			case "MONTHLY":
				if (isset($rrule["BYMONTHDAY"])) {
					$repeat_time = $this->_repeatMonthlyByMonthday($repeat_time, $params, $rrule, true);
				} else {
					$repeat_time = $this->_repeatMonthlyByDay($repeat_time, $params, $rrule, true);
				}
				break;
			case "WEEKLY":
				$repeat_time = $this->_repeatWeekly($repeat_time, $params, $rrule, true);
				break;
			case "DAILY":
				$repeat_time = $this->_repeatDaily($repeat_time, $params, $rrule);
				break;
			default:
				$repeat_time = null;
		}
		return $repeat_time;
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _repeatYearly(&$repeat_time, &$params, &$rrule, $first=false, $bymonthday=0)
	{
		$s_time = $params["start_date"].$params["start_time"];
		$e_time = $params["end_date"].$params["end_time"];

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
			return $repeat_time;
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
				$repeat_time = $this->__repeatYearlyByday($repeat_time, $params, $rrule, $first);
			} else {
				$repeat_time = $this->__repeatYearlyByMonthday($repeat_time, $params, $rrule, $bymonthday, $first);
			}
		}

		if (!empty($rrule["BYDAY"]) && count($rrule["BYDAY"]) > 0) {
			return $this->_repeatYearly($repeat_time, $params, $rrule);
		} else {
			return $this->_repeatYearly($repeat_time, $params, $rrule, false, $bymonthday);
		}
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function __repeatYearlyByMonthday(&$repeat_time, &$params, &$rrule, $bymonthday, $first)
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
			return $repeat_time;
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
			return $repeat_time;
		}
		return $this->_setRepeatTime($repeat_time, $params, $start_date.$start_time, $end_date.$end_time);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function __repeatYearlyByday(&$repeat_time, &$params, &$rrule, $first=false)
	{
		$rrule["INDEX"]++;

		$wday_num = array_search(substr($rrule["BYDAY"][0], -2), $this->_wdayArrary);
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
			return $repeat_time;
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
			return $repeat_time;
		}

		return $this->_setRepeatTime($repeat_time, $params, $start_date.$start_time, $end_date.$end_time);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _repeatMonthlyByMonthday(&$repeat_time, &$params, &$rrule, $first=false)
	{
		$rrule["INDEX"]++;

		$s_time = $params["start_date"].$params["start_time"];
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

		$e_time = $params["end_date"].$params["end_time"];
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
			return $repeat_time;
		}

		$repeat_time = $this->_setRepeatTime($repeat_time, $params, $start_date.$start_time, $end_date.$end_time);
		return $this->_repeatMonthlyByMonthday($repeat_time, $params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _repeatMonthlyByDay(&$repeat_time, &$params, &$rrule, $first=false)
	{
		$rrule["INDEX"]++;

		$wday_num = array_search(substr($rrule["BYDAY"][0], -2), $this->_wdayArrary);
		$week = intval(substr($rrule["BYDAY"][0], 0, -2));

		$s_time = $params["start_date"].$params["start_time"];
		$e_time = $params["end_date"].$params["end_time"];

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
			return $this->_repeatMonthlyByDay($repeat_time, $params, $rrule);
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
			return $repeat_time;
		}

		$repeat_time = $this->_setRepeatTime($repeat_time, $params, $start_date.$start_time, $end_date.$end_time);
		return $this->_repeatMonthlyByDay($repeat_time, $params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _repeatWeekly(&$repeat_time, &$params, &$rrule, $first=false)
	{
		$time = $params["start_date"].$params["start_time"];

		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + ($first ? 0 : (7 * $rrule["INTERVAL"])), substr($time,0,4));
		$current_week = date("w", $timestamp);
		$sunday_timestamp = $timestamp - $current_week * 86400;
		$start_date = date("Ymd", $sunday_timestamp);
		$start_time = date("His", $sunday_timestamp);

		$time = $params["end_date"].$params["end_time"];
		$end_timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + ($first ? 0 : (7 * $rrule["INTERVAL"])), substr($time,0,4));
		$end_date = date("Ymd", $end_timestamp - $current_week * 86400);
		$end_time = date("His", $end_timestamp - $current_week * 86400);

		if (!$this->_isRepeatable($rrule, $params["start_date"].$params["start_time"], $params["timezone_offset"])) {
			return $repeat_time;
		}
		foreach ($rrule["BYDAY"] as $i=>$val) {
			$params["start_date"] = $start_date;
			$params["start_time"] = $start_time;
			$params["end_date"] = $end_date;
			$params["end_time"] = $end_time;

			$index = array_search($val, $this->_wdayArrary);
			if ($first && $current_week >= $index) { continue; }
			$repeat_time = $this->__repeatWeekly($repeat_time, $params, $rrule, $index);
		}

		return $this->_repeatWeekly($repeat_time, $params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function __repeatWeekly(&$repeat_time, &$params, &$rrule, $interval)
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
			return $repeat_time;
		}

		return $this->_setRepeatTime($repeat_time, $params, $start_date.$start_time, $end_date.$end_time);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _repeatDaily(&$repeat_time, &$params, &$rrule)
	{
		$rrule["INDEX"]++;

		$time = $params["start_date"].$params["start_time"];
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + $rrule["INTERVAL"], substr($time,0,4));
		$start_date = date("Ymd", $timestamp);
		$start_time = date("His", $timestamp);

		$time = $params["end_date"].$params["end_time"];
		$timestamp = mktime(substr($time,8,2), substr($time,10,2), substr($time,12,2),
							substr($time,4,2), substr($time,6,2) + $rrule["INTERVAL"], substr($time,0,4));
		$end_date = date("Ymd", $timestamp);
		$end_time = date("His", $timestamp);
		if (!$this->_isRepeatable($rrule, $start_date.$start_time, $params["timezone_offset"])) {
			return $repeat_time;
		}

		$repeat_time = $this->_setRepeatTime($repeat_time, $params, $start_date.$start_time, $end_date.$end_time);
		return $this->_repeatDaily($repeat_time, $params, $rrule);
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _setRepeatTime(&$repeat_time, &$params, $start_time_full, $end_time_full)
	{
		$end_time_full = $this->dateFormat($end_time_full, $params["timezone_offset"], true, "YmdHis");
		$end_time_full = $this->dateFormat($end_time_full, $params["timezone_offset"], false, "YmdHis", true);

		$params["start_date"] = substr($start_time_full, 0, 8);
		$params["start_time"] = substr($start_time_full, 8);
		$params["end_date"] = substr($end_time_full, 0, 8);
		$params["end_time"] = substr($end_time_full, 8);

		$r_array = array(
			"start_time_full" => $start_time_full,
			"end_time_full" => $end_time_full
		);
		$repeat_time[] = $r_array;

		return $repeat_time;
	}

	/**
	 * 登録処理
	 *
	 * @access	private
	 */
	function _isRepeatable($rrule, $start_time, $timezone_offset)
	{
		if (isset($rrule["UNTIL"])) {
			$until = $this->dateFormat(substr($rrule["UNTIL"],0,8).substr($rrule["UNTIL"],-6), $timezone_offset, false);
			if ($start_time >= $until) {
				return false;
			}
		} else {
			$count = intval($rrule["COUNT"]);
			if ($rrule["INDEX"] > $count) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 検索を取得
	 *
	 * @access	public
	 */
	function getSearchCount($search_where, $search_params)
	{
		$sql = $this->_getSearchSQL("SELECT COUNT(*)", $search_where);

		$result = $this->_db->execute($sql, $search_params, null ,null, false);
		if ($result !== false) {
			$count = $result[0][0];
		} else {
			$count = 0;
		}
		return $count;
	}

	/**
	 * 検索を取得
	 *
	 * @access	public
	 */
	function getSearchResults($search_where, $search_params, $limit, $offset)
	{
		$sql = $this->_getSearchSQL("SELECT reserve.*, location.location_name, page.page_name, details.*", $search_where);

		$results = $this->_db->execute($sql, $search_params, $limit, $offset, true, array($this, "_getSearchResults"));

		return $results;
	}

	function _getSearchResults(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$ret_row = array();
			if ($row["room_id"] == 0) {
				$ret_row["room_name"] = RESERVATION_NO_RESERVE_FLAG ;
			} else {
				$ret_row["room_name"] = $row["page_name"];
			}

			$ret_row["pubDate"] = $row["start_time_full"];
			$ret_row["title_icon"] = $row["title_icon"];
			$ret_row["title"] = $row["title"];
			$ret_row["url"] = "?action=".DEFAULT_ACTION.
								"&page_id=".$this->_session->getParameter("_main_page_id").
								"&active_center=reservation_view_main_init" .
								"&reserve_id=".$row["reserve_id"];

			$ret_row["description"] = "";
			$ret_row["description"] .= sprintf(RESERVATION_WHATSNEW_LOCATION, $row["location_name"]);
			if (!empty($row["contact"])) {
				$ret_row["description"] .= sprintf(RESERVATION_WHATSNEW_CONTACT, $row["contact"]);
			}
			if (!empty($row["description"])) {
				$ret_row["description"] .= sprintf(RESERVATION_WHATSNEW_DESCRIPTION, $row["description"]);
			}
			$ret_row["user_id"] = $row["insert_user_id"];
			$ret_row["user_name"] = $row["insert_user_name"];

			$result[] = $ret_row;
		}
		return $result;
	}

	/**
	 * 検索を取得
	 *
	 * @access	public
	 */
	function _getSearchSQL($sql, $search_where)
	{
		$location_list = $this->_request->getParameter("location_list");

		$sql .= " FROM {reservation_reserve} reserve" .
				" INNER JOIN {reservation_location} location ON (reserve.location_id=location.location_id)".
				" LEFT JOIN {reservation_reserve_details} details ON (reserve.reserve_details_id=details.reserve_details_id)".
				" LEFT JOIN {pages} page ON (reserve.room_id=page.page_id)".
				" WHERE 1=1" .
				" ". $search_where .
				" AND reserve.location_id IN (".implode(",", $location_list).") ";

		return $sql;
	}
}
?>