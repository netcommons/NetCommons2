<?php
 /**
 * タイムゾーン関連クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Timezone_Main {
	var $_className = "Timezone_Main";
	
	/**
	 * タイムゾーン文字列取得(timezone.iniをSmartyAssignより読み込むこと)
	 * @param  string  timezone_str
	 * @return string  timezone offset
	 * @access	public
	 */
	function getFloatTimeZone($timezone_str) {
		if(!defined("_TZ_GMT0")) {
			// GMT[+|-]99:99のような記述でない場合、動作しない
			if(preg_match("/GMT(\+|-)([0-9]{1,2}):([0-9]{1,2})/i", $timezone_str, $matches)) {
				return floatval($matches[1].$matches[2].".".$matches[3]);
			} else {
				// matchしない場合、標準時間を返す
				return 0.0;	
			}
		}
		switch($timezone_str) {
			case _TZ_GMTM12:
				$ret = -12.0;
				break;
			case _TZ_GMTM11:
				$ret = -11.0;
				break;
			case _TZ_GMTM10:
				$ret = -10.0;
				break;
			case _TZ_GMTM9:
				$ret = -9.0;
				break;
			case _TZ_GMTM8:
				$ret = -8.0;
				break;
			case _TZ_GMTM7:
				$ret = -7.0;
				break;
			case _TZ_GMTM6:
				$ret = -6.0;
				break;
			case _TZ_GMTM5:
				$ret = -5.0;
				break;
			case _TZ_GMTM4:
				$ret = -4.0;
				break;
			case _TZ_GMTM35:
				$ret = -3.5;
				break;
			case _TZ_GMTM3:
				$ret = -3.0;
				break;
			case _TZ_GMTM2:
				$ret = -2.0;
				break;
			case _TZ_GMTM1:
				$ret = -1.0;
				break;
			case _TZ_GMT0:
				$ret = 0.0;
				break;
			case _TZ_GMTP1:
				$ret = 1.0;
				break;
			case _TZ_GMTP2:
				$ret = 2.0;
				break;
			case _TZ_GMTP3:
				$ret = 3.0;
				break;
			case _TZ_GMTP35:
				$ret = 3.5;
				break;
			case _TZ_GMTP4:
				$ret = 4.0;
				break;
			case _TZ_GMTP45:
				$ret = 4.5;
				break;
			case _TZ_GMTP5:
				$ret = 5.0;
				break;
			case _TZ_GMTP55:
				$ret = 5.5;
				break;
			case _TZ_GMTP6:
				$ret = 6.0;
				break;
			case _TZ_GMTP7:
				$ret = 7.0;
				break;
			case _TZ_GMTP8:
				$ret = 8.0;
				break;
			case _TZ_GMTP9:
				$ret = 9.0;
				break;
			case _TZ_GMTP95:
				$ret = 9.5;
				break;
			case _TZ_GMTP10:
				$ret = 10.0;
				break;
			case _TZ_GMTP11:
				$ret = 11.0;
				break;
			case _TZ_GMTP12:
				$ret = 12.0;
				break;
			default:
				// 言語切り替え直後$timezone_strから求める
				// GMT[+|-]99:99のような記述でない場合、動作しない
				if(preg_match("/GMT(\+|-)([0-9]{1,2}):([0-9]{1,2})/i", $timezone_str, $matches)) {
					$ret = floatval($matches[1].$matches[2].".".$matches[3]);
				} else {
					// matchしない場合、標準時間を返す
					$ret = 0.0;	
				}
		}
		return $ret;
	}
	/**
	 * タイムゾーン文字列取得(timezone.iniをSmartyAssignより読み込むこと)
	 * @param float timezone_offset
	 * @param int   constant_flag
	 * @return string timezone str
	 * @access	public
	 */
	function getLangTimeZone($timezone_offset, $constant_flag = true) {
		switch($timezone_offset) {
			case -12.0:
				$ret = "_TZ_GMTM12";
				break;
			case -11.0:
				$ret = "_TZ_GMTM11";
				break;
			case -10.0:
				$ret = "_TZ_GMTM10";
				break;
			case -9.0:
				$ret = "_TZ_GMTM9";
				break;
			case -8.0:
				$ret = "_TZ_GMTM8";
				break;
			case -7.0:
				$ret = "_TZ_GMTM7";
				break;
			case -6.0:
				$ret = "_TZ_GMTM6";
				break;
			case -5.0:
				$ret = "_TZ_GMTM5";
				break;
			case -4.0:
				$ret = "_TZ_GMTM4";
				break;
			case -3.5:
				$ret = "_TZ_GMTM35";
				break;
			case -3.0:
				$ret = "_TZ_GMTM3";
				break;
			case -2.0:
				$ret = "_TZ_GMTM2";
				break;
			case -1.0:
				$ret = "_TZ_GMTM1";
				break;
			case 0.0:
				$ret = "_TZ_GMT0";
				break;
			case 1.0:
				$ret = "_TZ_GMTP1";
				break;
			case 2.0:
				$ret = "_TZ_GMTP2";
				break;
			case 3.0:
				$ret = "_TZ_GMTP3";
				break;
			case 3.5:
				$ret = "_TZ_GMTP35";
				break;
			case 4.0:
				$ret = "_TZ_GMTP4";
				break;
			case 4.5:
				$ret = "_TZ_GMTP45";
				break;
			case 5.0:
				$ret = "_TZ_GMTP5";
				break;
			case 5.5:
				$ret = "_TZ_GMTP55";
				break;
			case 6.0:
				$ret = "_TZ_GMTP6";
				break;
			case 7.0:
				$ret = "_TZ_GMTP7";
				break;
			case 8.0:
				$ret = "_TZ_GMTP8";
				break;
			case 9.0:
				$ret = "_TZ_GMTP9";
				break;
			case 9.5:
				$ret = "_TZ_GMTP95";
				break;
			case 10.0:
				$ret = "_TZ_GMTP10";
				break;
			case 11.0:
				$ret = "_TZ_GMTP11";
				break;
			case 12.0:
				$ret = "_TZ_GMTP12";
				break;
		}
		if($constant_flag) $ret = constant($ret);
		return $ret;
	}
}
?>
