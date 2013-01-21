<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート期限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Validator_Period extends Validator
{
    /**
     * アンケート期限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		// TODO:期限の時間設定
		$attributes["period_hour"] = "24";
		$attributes["period_minute"] = "00";

		$attributes["period_hour"] = intval($attributes["period_hour"]);
		$attributes["period_minute"] = intval($attributes["period_minute"]);
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		if (empty($attributes["period_checkbox"])) {
			$request->setParameter("period", "");
			return;
		}
		
		$period = $attributes["period"]. 
					sprintf("%02d", $attributes["period_hour"]).
					sprintf("%02d", $attributes["period_minute"]).
					"00";
		$period = timezone_date($period, true);
		
		$gmt = timezone_date();
		if ($period < $gmt) {
			return $errStr;
		}

		$request->setParameter("period", $period);
		
        return;
    }
}
?>