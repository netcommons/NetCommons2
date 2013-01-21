<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_Date extends Validator
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
    	if (empty($attributes)) {
			$attributes = timezone_date(null, false, "Ymd");
    	}
		$pattern = "/^([0-9]{4})/";
		if (!preg_match($pattern, $attributes, $matches)) {
			return $errStr;
		} else {
			$year = $matches[1];
		}
		$pattern = "/^([0-9]{4})([0-9]{2})/";
		if (!preg_match($pattern, $attributes, $matches)) {
			$month = timezone_date(null, false, "m");;
		} else {
			$month = $matches[2];
		}
		$pattern = "/^([0-9]{4})([0-9]{2})([0-9]{2})$/";
		if (!preg_match($pattern, $attributes, $matches)) {
			$day = "01";
		} else {
			$day = $matches[3];
		}
		if (!checkdate($month, $day, $year)) {
			$attributes = timezone_date(null, false, "Ymd");
		} else {
			$attributes = $year.$month.$day;
		}
		if ($attributes < CALENDAR_MIN_DATE) {
			$attributes = CALENDAR_MIN_DATE;
		}
		if ($attributes > CALENDAR_MAX_DATE) {
			$attributes = CALENDAR_MAX_DATE;
		}

    	$container =& DIContainerFactory::getContainer();
 		$request =& $container->getComponent("Request");
 		$key = $this->getKeys(0);
		$request->setParameter($key, $attributes);
    }
}
?>
