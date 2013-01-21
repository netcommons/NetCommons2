<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 宛先情報取得クラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_ReceiverEnter extends Validator
{
    /**
     * 宛先情報取得処理
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$handle = $attributes["handle"];
		$flag = $attributes["flag"];
		
		$container =& DIContainerFactory::getContainer();
		$usersView =& $container->getComponent("usersView");
		
		if($flag == "info"){
			if(trim($handle) == ''){
				// return "false|" . sprintf(PM_ERROR_USER_EMPTY, PM_USER_HANDLE);
			}else{
				$pmView =& $container->getComponent("pmView");
				$user_id = $pmView->getUserIdByHandle($handle);
				
				if(empty($user_id)) {
					return "false|" . sprintf(PM_ERROR_USER_NONEEXISTS, PM_USER_HANDLE, $handle);
				}else{
					return "true|" . $user_id;
				}
			}
		}elseif($flag == "avatar"){
			$user_id = $attributes["user_id"];
			if(empty($user_id)){
				$avatar = "";
			}else{
				$pmView =& $container->getComponent("pmView");
				$avatar = $pmView->getUserAvatar($user_id);
				if(!$avatar){
					$avatar = "";
				}
			}
			
			if($avatar == ""){
				$avatar = "false";
			}
			return $avatar;
		}
		
		/*
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("user_id" => $users[0]["user_id"]));
		*/
        return;
    }
}
?>
