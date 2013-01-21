<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限設定チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_EditMessage extends Validator
{
    /**
     * 権限設定チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$receiver_id = null;
		$message_id = null;
		
		if(isset($attributes["receiver_id"])){
			$receiver_id = $attributes["receiver_id"];
		}
		if(isset($attributes["message_id"])){
			$message_id = $attributes["message_id"];
		}
		
		if($message_id != null){
			$container =& DIContainerFactory::getContainer();
			$pmView =& $container->getComponent("pmView");
			$receiver_id = $pmView->getMessageReceiverId($message_id);
			if($receiver_id == false){
				return;
			}
		}
		
		if($receiver_id == null){
			return;
		}
		
		if(!is_array($receiver_id)){		
			$receivers = explode(" ",$receiver_id);	
		} else {		
			$receivers = $receiver_id;	
		}
		
		$container =& DIContainerFactory::getContainer();
        $pmView =& $container->getComponent("pmView");

		foreach($receivers as $receiver) {
			if (!$pmView->checkMessageAuth($receiver)) {
				return $errStr;
			}
		}	

        return;
    }
}
?>
