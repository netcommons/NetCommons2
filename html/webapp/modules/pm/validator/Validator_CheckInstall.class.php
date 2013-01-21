<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * プライベートルームチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_CheckInstall extends Validator
{
    /**
     * プライベートルームチェックバリデータクラス
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
 		$request =& $container->getComponent("Request");
 		$session =& $container->getComponent("Session");
 		$actionChain =& $container->getComponent("ActionChain");
		$action_name =& $actionChain->getCurActionName();
		
		$block_id = $request->getParameter("block_id");
		$room_id = $request->getParameter("room_id");
		$room_arr = $request->getParameter("room_arr");
		if($session->getParameter("_user_id") != "0" && ($block_id == "0" || $action_name == "pm_view_main_message_entry")) {
			return;
		}
		$check = false;
		if(is_array($room_arr[0][0])){
			foreach($room_arr[0][0] as $room){
				if($room["room_id"] == $room_id){
					$private_flag = $room["private_flag"];
					if($private_flag == _ON){
						$check = true;
					}
				}
			}
		}
		if(!$check && ($session->getParameter("_user_id") != "0")){
			return $errStr;
		}
		
        return;
    }
}
?>
