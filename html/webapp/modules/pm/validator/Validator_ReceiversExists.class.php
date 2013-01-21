<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 宛先存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_ReceiversExists extends Validator
{
    /**
     * 宛先存在チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$receivers = $attributes["receivers"];
		$send_all_flag = $attributes["send_all_flag"];
		
		if($send_all_flag == _ON){
			return;
		}
		
		$container =& DIContainerFactory::getContainer();
        $pmView =& $container->getComponent("pmView");
		foreach($receivers as $handle) {
			if (!empty($handle)) {
				$userId = $pmView->getUserIdByHandle(trim($handle));
				if (empty($userId)) {
					return "[".$handle."]".$errStr;
				}
			}
		}
        return;
    }
}
?>
