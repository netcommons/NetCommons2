<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ヘッダーメニューをパラメータによりActiveにする
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Policy_Validator_ActiveHeaderMenu extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値		user_id
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数		headerMenuフィルタ名
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$filterName = "HeaderMenu";
    	if(isset($params[0])) {
    		$filterName = $params[0];
    	}
    	if(isset($attributes)) {
    		$user_auth_id = intval($attributes);
    	} else {
    		$user_auth_id = _AUTH_ADMIN;
    	}
    	switch($user_auth_id) {
    		case _AUTH_ADMIN:
    			// 管理者
    			$active_num = 1;
    			break;
    		case _AUTH_CHIEF:
    			// 主担 
    			$active_num = 2;
    			break;
    		case _AUTH_MODERATE:
    			// モデレータ
    			$active_num = 3;
    			break;
    		case _AUTH_GENERAL:
    			// 一般
    			$active_num = 4;
    			break;
    		case _AUTH_GUEST:
    			// ゲスト
    			$active_num = 5;
    			break;
    		default:
    			return $errStr;
    	}
    	$container =& DIContainerFactory::getContainer();
    	$filterChain =& $container->getComponent("FilterChain");
    	$headerMenu =& $filterChain->getFilterByName($filterName);
		if(!$headerMenu) {
			//headerMenuフィルター指定なし
			return $errStr;
		}
		// Active
		$headerMenu->setActive($active_num);
    	return;
    }
}
?>
