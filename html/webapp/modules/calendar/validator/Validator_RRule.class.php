<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 繰返しチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_RRule extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$details_flag = intval($attributes["details_flag"]);
		if ($details_flag == _OFF) {
			return;
		}
    	$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
  		$action =& $actionChain->getCurAction();
		
		$rrule = array();

		$repeat_flag = intval($attributes["repeat_flag"]);
		if ($repeat_flag == _OFF) {
			$rrule["FREQ"] = "NONE";
			BeanUtils::setAttributes($action, array("rrule"=>$rrule));
			return;
		}

		$rrule_term = $attributes["rrule_term"];
		if ($rrule_term != "COUNT" && $rrule_term != "UNTIL") {
			return _INVALID_INPUT;
		}
		if ($rrule_term == "COUNT") {
			$rrule_count = intval($attributes["rrule_count"]);
			if (empty($attributes["rrule_count"]) && $rrule_count !== 0) {
				return sprintf(_REQUIRED, CALENDAR_RRULE_LBL_COUNT);
			}
			if ($rrule_count <= 0) {
				return sprintf(_NUMBER_ERROR, CALENDAR_RRULE_LBL_COUNT, 1, 999);
			}
			$rrule["COUNT"] = $rrule_count;
		}

		if ($rrule_term == "UNTIL") {
			$rrule_until = $attributes["rrule_until"];
			if (empty($rrule_until)) {
				return sprintf(_REQUIRED, CALENDAR_RRULE_LBL_UNTIL);
			}
			if (!preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})$/", $rrule_until, $matches)) {
				return sprintf(_INVALID_DATE, CALENDAR_RRULE_LBL_UNTIL);
			}
			if (!checkdate($matches[2], $matches[3], $matches[1])) {
				return sprintf(_INVALID_DATE, CALENDAR_RRULE_LBL_UNTIL);
			}
			$date = timezone_date($matches[1].$matches[2].$matches[3]."240000", true, "YmdHis");
			if ($attributes["start_time_full"] > $date) {
				return CALENDAR_RRULE_ERR_UNTIL_OVER;
			}
			$rrule["UNTIL"] = substr($date, 0,8)."T".substr($date,8);
		}

		$repeat_freq = $attributes["repeat_freq"];
		if (!isset($repeat_freq)) {
			return _INVALID_INPUT;
		}

		$rrule_interval = $attributes["rrule_interval"];
		if ($repeat_freq != "NONE" && !isset($rrule_interval) && !isset($rrule_interval[$repeat_freq])) {
			return _INVALID_INPUT;
		}

		$wday_array = explode("|", CALENDAR_REPEAT_WDAY);

		$rrule_byday = $attributes["rrule_byday"];
		$rrule_bymonthday = $attributes["rrule_bymonthday"];
		$rrule_bymonth = $attributes["rrule_bymonth"];

		switch ($repeat_freq) {
			case "DAILY":
				$rrule["FREQ"] = $repeat_freq;
				$rrule["INTERVAL"] = intval($rrule_interval[$repeat_freq]);
				
				break;
			case "WEEKLY":
				$rrule["FREQ"] = $repeat_freq;
				$rrule["INTERVAL"] = intval($rrule_interval[$repeat_freq]);
				if (!isset($rrule_byday) && !isset($rrule_byday[$repeat_freq])) {
					return CALENDAR_RRULE_ERR_WDAY;
				}
				$byday = array();
				foreach ($rrule_byday[$repeat_freq] as $i=>$w) {
					if (!in_array($w, $wday_array)) { continue; }
					$byday[] = $w;
				}
				if (empty($byday)) {
					return CALENDAR_RRULE_ERR_WDAY;
				}
				$rrule["BYDAY"] = $byday;

				break;
			case "MONTHLY":
				$rrule["FREQ"] = $repeat_freq;
				$rrule["INTERVAL"] = intval($rrule_interval[$repeat_freq]);
				if (!isset($rrule_byday) && !isset($rrule_byday[$repeat_freq]) && !isset($rrule_bymonthday) && !isset($rrule_bymonthday[$repeat_freq])) {
					return CALENDAR_RRULE_ERR_WDAY_OR_DAY;
				}
				if (isset($rrule_byday) && isset($rrule_byday[$repeat_freq])) {
					$byday = array();
					foreach ($rrule_byday[$repeat_freq] as $i=>$val) {
						$w = substr($val, -2);
						$n = intval(substr($val, 0, -2));
						if ($n == 0) { $val = $w; }
						if (!in_array($w, $wday_array)) { continue; }
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
					return CALENDAR_RRULE_ERR_WDAY_OR_DAY;
				}
				
				break;
			case "YEARLY":
				$rrule["FREQ"] = $repeat_freq;
				$rrule["INTERVAL"] = intval($rrule_interval[$repeat_freq]);
				if (!isset($rrule_bymonth) && !isset($rrule_bymonth[$repeat_freq])) {
					return CALENDAR_RRULE_ERR_MONTH;
				}
				$bymonth = array();
				foreach ($rrule_bymonth[$repeat_freq] as $i=>$val) {
					$val = intval($val);
					if ($val > 0 && $val <= 12) {
						$bymonth[] = $val;
					}
				}
				if (empty($bymonth)) {
					return CALENDAR_RRULE_ERR_MONTH;
				}
				$rrule["BYMONTH"] = $bymonth;
				if (isset($rrule_byday) && isset($rrule_byday[$repeat_freq])) {
					$byday = array();
					foreach ($rrule_byday[$repeat_freq] as $i=>$val) {
						$w = substr($val, -2);
						$n = intval(substr($val, 0, -2));
						if ($n == 0) { $val = $w; }
						if (!in_array($w, $wday_array)) { continue; }
						if (!($n >= -1 && $n <= 4)) { continue; }
						$byday[] = $val;
					}
					$rrule["BYDAY"] = $byday;
				}
				
				break;
			default:
				$rrule["FREQ"] = "NONE";
		}

	   	$request =& $container->getComponent("Request");
		$request->setParameter("rrule", $rrule);
    }
}
?>
