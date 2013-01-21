<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロックに配置してあるかどうか
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Chat_Validator_ExistBlock extends Validator
{
    /**
     * ブロックに配置してあるかどうか
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
		$chatView =& $container->getComponent("chatView");
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$block_id = $attributes;
		if(empty($block_id) || $actionName == "chat_action_edit_create") {
			$chat_obj = $chatView->getDefaultChat();
		}else {
			$chat_obj = $chatView->getChatById($block_id);
		}

		if($chat_obj === false || !isset($chat_obj)) {
			return $errStr;
		}

		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("chat_obj"=>$chat_obj));
        return;
    }
}
?>
