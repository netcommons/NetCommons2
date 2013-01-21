<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タイムゾーンチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_TimezoneOffset extends Validator
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
	   	$session =& $container->getComponent("Session");
	   	$commonMain =& $container->getComponent("commonMain");
		if (isset($attributes["timezone_offset"])) {
			$array = explode("|", RESERVATION_DEF_TIMEZONE);
			if (!in_array($attributes["timezone_offset"], $array)) {
				return $errStr;
			}
			$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");
			$timezone_offset = $timezoneMain->getFloatTimeZone(constant($attributes["timezone_offset"]));
		} else {
        	$timezone_offset = $session->getParameter("_timezone_offset");
		}
	   	$request =& $container->getComponent("Request");
		$request->setParameter("timezone_offset", $timezone_offset);
    }
}
?>
