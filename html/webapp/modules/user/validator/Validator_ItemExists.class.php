<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_ItemExists extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数(items,item_id=0を許すかどうか)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$usersView =& $container->getComponent("usersView");
		
    	// item_id取得
    	$item_id = intval($attributes);
    	if(isset($params[1]) && $params[1] == _ON && $item_id == 0) {
    		return;
    	}
 		$items =& $usersView->getItemById($item_id);
 		if($items === false) return $errStr;
 		
 		//
 		// Actionにデータセット
 		//

		// actionChain取得
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		if(isset($params[0])) {
			BeanUtils::setAttributes($action, array($params[0]=>$items));
		} else {
			BeanUtils::setAttributes($action, array("items"=>$items));
		}
    	return;
    }
}
?>
