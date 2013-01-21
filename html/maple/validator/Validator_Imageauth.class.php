<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *　画像認証の入力チェック
 * @param   mixed   $attributes チェックする値　0:画像認証の入力値 1:module_id 2:id
 * @param   string  $errStr     エラー文字列
　* @param   array   $params
 * @return  string  エラー文字列(エラーの場合)
 * @access  public
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Validator_Imageauth extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値(user_id, items, items_public, items_reception)
     *                  
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
    	$session =& $container->getComponent("Session");
    	
    	$count = count($attributes);
    	if($count != 2) {
    		return $errStr;
    	}
    	
    	$image_auth_session = $session->getParameter(array(_SESSION_IMAGE_AUTH.$attributes[1]));
    	if(empty($image_auth_session)) {
    		return;
    	}else {
    		if($attributes[0] == "" || $attributes[0] != $image_auth_session) {
				return $errStr;
			}
    	}
    	return;
    }
}
?>
