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
class Calendar_Validator_MobileMoveDate extends Validator
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
    	if (!isset($attributes["month"])) {
    		$attributes["month"] = "01";
    		$attributes["day"] = "01";
    	}
    	if (!isset($attributes["day"])) {
    		$attributes["day"] = "01";
    	}
    	if (checkdate(intval($attributes["month"]),intval($attributes["day"]),intval($attributes["year"]))) {
			$container =& DIContainerFactory::getContainer();
			$request =& $container->getComponent("Request");
			$request->setParameter("date", date("Ymd", mktime(0,0,0,$attributes["month"],$attributes["day"],$attributes["year"])));
    	} else {
    		return $errStr;
    	}
    }
}
?>
