<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_DisplayStartTime extends Validator
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
    	$container =& DIContainerFactory::getContainer();

		switch ($attributes["start_time_type"]) {
			case RESERVATION_DEF_START_TIME_FIXATION:
				$hour = intval(substr($attributes["start_time_fixation"], 0, 2));
				if ($hour < 0 || $hour >= 24) {
					return $errStr;
				}
				$display_start_time = sprintf("%02d00", $hour);
				break;
			case RESERVATION_DEF_START_TIME_DEFAULT:
				$display_start_time = "default";
				break;
			default:
				return $errStr;
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("display_start_time", $display_start_time);
    }
}
?>
