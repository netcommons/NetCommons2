<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 差出人存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_SenderExists extends Validator
{
    /**
     * 差出人存在チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$senders = $attributes["senders"];
		
		if (empty($senders)) {
			return;
		}
				
		$container =& DIContainerFactory::getContainer();
        $pmView =& $container->getComponent("pmView");
		$userId = $pmView->getUserIdByHandle(trim($senders));
		if (empty($userId)) {
			return "[".$senders."]".$errStr;
		}
			
        return;
    }
}
?>
