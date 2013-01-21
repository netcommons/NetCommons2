<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_View
{
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

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
	function Holiday_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_wday_array = explode("|", "SU|MO|TU|WE|TH|FR|SA");
	}

	/**
	 * 祝日取得
	 *
	 * @access	public
	 */
	function get($start_date, $end_date=null, $timezone_flag=true)
	{
		$session =& $this->_container->getComponent("Session");
		$_lang = $session->getParameter("_lang");
		if (!isset($end_date)) {
			$end_date = $start_date;
		}
		if ($timezone_flag) {
			$start_date = timezone_date($start_date."000000", true, "YmdHis");
			$end_date = timezone_date($end_date."595959", true, "YmdHis");
		}

		$sql = "SELECT * FROM {holiday} ";
		$sql .= "WHERE lang_dirname = ? ";
		$sql .= "AND holiday >= ? ";
		$sql .= "AND holiday <= ? ";
		$params = array(
			"lang_dirname" => $_lang,
			"start_date" => $start_date,
			"end_date" => $end_date
		);
        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callback"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * 祝日取得
	 *
	 * @access	private
	 */
	function &_callback(&$recordSet)
	{
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			$holiday = timezone_date($row["holiday"], false, "Ymd");
			$ret[$holiday] = $row["summary"];
		}
		return $ret;
	}

	/**
	 * 祝日取得
	 *
	 * @access	public
	 */
	function getYear($year=null, $lang=null, $sort_col="holiday", $sort_dir="ASC")
	{
    	if (!isset($year)) {
    		$year = timezone_date(null, false, "Y");
    	}
    	if (!isset($lang)) {
			$session =& $this->_container->getComponent("Session");
			$lang = $session->getParameter("_lang");
    	}
		$sql = "SELECT * FROM {holiday} ";
		$sql .= "WHERE lang_dirname = ? ";
		$sql .= "AND holiday >= ? ";
		$sql .= "AND holiday <= ? ";
		$sql .= "ORDER BY ".$sort_col." ".$sort_dir;

		$params = array(
			"lang_dirname" => $lang,
			"start_date" => timezone_date($year."0101000000", true, "YmdHis"),
			"end_date" => timezone_date($year."1231000000", true, "YmdHis")
		);
    	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackYear"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * 祝日取得
	 *
	 * @access	private
	 */
	function &_callbackYear(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$row["holiday"] = timezone_date($row["holiday"], false, "YmdHis");
			$row["holiday_str"] = date(_DATE_FORMAT, mktime(0,0,0,substr($row["holiday"],4,2),substr($row["holiday"],6,2),substr($row["holiday"],0,4)));
			$result[] = $row;
		}
		return $result;
	}

	/**
	 * 祝日(繰返しも含め)取得
	 *
	 * @access	public
	 */
	function &getRRule($holiday_id)
	{
		$sql = "SELECT * " .
				"FROM {holiday} holiday " .
				"INNER JOIN {holiday_rrule} rrule ON (holiday.rrule_id=rrule.rrule_id) ".
				"WHERE holiday_id = ?";
        $result = $this->_db->execute($sql, array("holiday_id"=>$holiday_id), null, null, true, array($this,"_callbackRRule"));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	/**
	 * 祝日(繰返しも含め)取得
	 *
	 * @access	private
	 */
	function &_callbackRRule(&$recordSet)
	{
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			$row["start_time"] = timezone_date($row["start_time"], false, "YmdHis");
			$row["end_time"] = timezone_date($row["end_time"], false, "YmdHis");
			$row["holiday"] = timezone_date($row["holiday"], false, "YmdHis");
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * パース処理
	 *
	 * @access	public
	 */
	function &parseRRule($rrule_str="")
	{
		$result_array = array();
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
	 * 文字列にする処理
	 *
	 * @access	public
	 */
	function concatRRule($rrule, &$result_str)
	{
		$result_str = "";
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
		$result_str = implode(";", $result);
        return true;
	}

	/**
	 * 祝日取得（複数日指定）
	 *
	 * @param array $dates 祝日の判断を行う対象年月日配列
	 * @return array 日時をkeyとした祝日名称配列
	 * @access	public
	 */
	function getHolidays($dates)
	{
		$holidays = array();

		$session =& $this->_container->getComponent('Session');
		$language = $session->getParameter('_lang');

		$datesString = implode("','", $dates);
		$sql = "SELECT holiday, summary "
				. "FROM {holiday} "
				. "WHERE lang_dirname = ? "
				. "AND holiday IN ('" . $datesString . "') ";
		$params = array(
			$language
		);
		$holidays = $this->_db->execute($sql, $params, null, null, true, array($this, '_callback'));
		if ($holidays === false) {
			$this->_db->addError();
		}

		return $holidays;
	}
}
?>