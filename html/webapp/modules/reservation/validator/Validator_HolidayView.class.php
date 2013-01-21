<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設の存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_HolidayView extends Validator
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
    	$commonMain =& $container->getComponent("commonMain");
		$holidayView =& $commonMain->registerClass(WEBAPP_DIR.'/components/holiday/View.class.php', "Holiday_View", "holidayView");
        
		$holiday_list = $holidayView->get($attributes["start_date"], $attributes["end_date"]);
    	if ($holiday_list === false) {
    		return $errStr;
    	}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("holiday_list", $holiday_list);
    }
}
?>
