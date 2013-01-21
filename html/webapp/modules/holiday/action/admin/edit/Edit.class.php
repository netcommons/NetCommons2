<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_Action_Admin_Edit extends Action
{
    // リクエストパラメータを受け取るため
	var $varidable_flag = null;
	var $month = null;
	var $day = null;
	var $week = null;
	var $wday = null;
	var $start_year = null;
	var $end_year = null;
	var $summary = null;
	var $substitute_flag = null;
	var $rrule_id = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $holidayView = null;
	var $session = null;
	var $request = null;

    // 値をセットするため
    var $holiday_list = null;
    var $lang_dirname = null;
    var $wday_num = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->varidable_flag = intval($this->varidable_flag);
		$this->substitute_flag = intval($this->substitute_flag);

    	$count = $this->db->countExecute("holiday_rrule", array("rrule_id"=>$this->rrule_id));
    	if($count === false) {
    		return 'error';
    	}

		$this->lang_dirname = $this->session->getParameter("holiday_lang");

		if ($this->varidable_flag == _ON) {
			$this->week = intval($this->week);
			$wday_array = explode("|", HOLIDAY_REPEAT_WDAY);
			$this->wday_num = array_search($this->wday, $wday_array);

	    	$start_time = timezone_date(date("Ymd",$this->_weekday($this->start_year))."000000", true, "YmdHis");
	    	$end_time = timezone_date(date("Ymd",$this->_weekday($this->end_year))."000000", true, "YmdHis");
			$rrule =  array(
				"FREQ"=>"YEARLY", 
				"INTERVAL"=>1, 
				"UNTIL"=>substr($end_time,0,8)."T".substr($end_time,8),
				"BYMONTH"=>array(intval(intval($this->month))),
				"BYDAY"=>array($this->week.$this->wday)
			);
		} else {
	    	$start_time = timezone_date($this->start_year.$this->month.$this->day."000000", true, "YmdHis");
	    	$end_time = timezone_date($this->end_year.$this->month.$this->day."000000", true, "YmdHis");
			$rrule =  array(
				"FREQ"=>"YEARLY", 
				"INTERVAL"=>1, 
				"UNTIL"=>substr($end_time,0,8)."T".substr($end_time,8),
				"BYMONTH"=>array(intval(intval($this->month)))
			);
		}
		$rrule_str = "";
		$result = $this->holidayView->concatRRule($rrule, $rrule_str);
		if ($result === false) {
			return 'error';
		}
		if ($this->start_year == $this->end_year) {
			$rrule_str = "";
		}
		$params = array(
			"varidable_flag" => $this->varidable_flag,
			"substitute_flag" => $this->substitute_flag,
			"start_time" => $start_time,
			"end_time" => $end_time,
			"rrule" => $rrule_str
		);

		if ($count > 0) {
		   	$result = $this->db->deleteExecute("holiday", array("rrule_id"=>$this->rrule_id));
			if ($result === false) {
				return 'error';
			}
		}

		$this->holiday_list = $this->holidayView->get($this->start_year."0101000000", $this->end_year."1231595959", false);
		if ($this->holiday_list === false) {
			return 'error';
		}

		if ($count > 0) {
	    	$result = $this->db->updateExecute("holiday_rrule", $params, array("rrule_id"=>$this->rrule_id), true);
		} else {
	    	$result = $this->db->insertExecute("holiday_rrule", $params, true, "rrule_id");
	    	$this->rrule_id = $result;
		}
		if ($result === false) {
			return 'error';
		}

		if ($this->varidable_flag == _ON) {
			$result = $this->_varidable($this->start_year);
		} else {
			$result = $this->_invaridable($this->start_year);
		}

		if ($result === false) {
			return 'error';
		}

		$lang = $this->session->getParameter("holiday_lang");
		$year = $this->session->getParameter("holiday_year");
		$this->request->setParameter("lang", $lang);
		$this->request->setParameter("year", $year);

		return 'success';
    }

    /**
     * 休日登録
     *
     * @access  private
     */
    function _regist($date, $params=array())
    {
		$base_params = array(
			"rrule_id" => $this->rrule_id,
			"lang_dirname" => $this->lang_dirname,
			"holiday" => timezone_date($date."000000", true, "YmdHis"),
			"summary" => $this->summary,
			"holiday_type" => _OFF,
		);
		$params = array_merge($base_params, $params);
    	$result = $this->db->insertExecute("holiday", $params, false, "holiday_id");
		if ($result === false) {
			return false;
		} else {
			return true;
		}
    }

    /**
     * 振替休日
     *
     * @access  private
     */
    function _substitute($month, $day, $year)
    {
		$timestamp = mktime(0, 0, 0, $month, $day, $year);
		$wday = date("w", $timestamp);
		$date = date("Ymd", $timestamp);
		if ($wday == 0 || isset($this->holiday_list[$date])) {
			return $this->_substitute($month, $day+1, $year);
		}
		return $this->_regist($date, array("summary"=>HOLIDAY_SUBSTITUTE, "holiday_type"=>_ON));
    }

    /**
     * 固定休日
     *
     * @access  private
     */
    function _invaridable($year)
    {
		$timestamp = mktime(0, 0, 0, $this->month, $this->day, $year);
		$wday = date("w", $timestamp);
		$date = date("Ymd", $timestamp);
		$result = $this->_regist($date);
		if ($result === false) {
			return false;
		}
		if ($this->substitute_flag == _ON && ($wday == 0 || isset($this->holiday_list[$date]))) {
			$result = $this->_substitute($this->month, $this->day, $year);
			if ($result === false) {
				return false;
			}
		}
		if ($year < $this->end_year) {
			return $this->_invaridable($year + 1);
		} else {
			return true;
		}
    }

    /**
     * 可変休日
     *
     * @access  private
     */
    function _varidable($year)
    {
		$date = date("Ymd", $this->_weekday($year));
		$result = $this->_regist($date);
		if ($result === false) {
			return false;
		}
		if ($year < $this->end_year) {
			return $this->_varidable($year + 1);
		} else {
			return true;
		}
    }
    /**
     * ○週の曜日
     *
     * @access  private
     */
    function _weekday($year)
    {
		$timestamp = mktime(0, 0, 0, $this->month, 1, $year);
    	if ($this->week == -1) {
			$last_day = date("t", $timestamp);
			$timestamp = mktime(0, 0, 0, $this->month, $last_day, $year);
			$w_last_day = date("w", $timestamp);
    		$timestamp = mktime(0, 0, 0, $this->month, $last_day - $w_last_day + $this->wday_num, $year);
    	} else {
			$w_1day = date("w", $timestamp);
			$w_1day = ($w_1day <= $this->wday_num ? 7 + $w_1day : $w_1day);
			$day = $this->week * 7 + $this->wday_num + 1;
			$timestamp = mktime(0, 0, 0, $this->month, $day - $w_1day, $year);
    	}
    	return $timestamp;
    }
}
?>