<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 1.会員編集-ヘッダーメニューを削除する
 * 2.ルーム管理からの検索-ヘッダーメニューを削除する
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_ClearHeaderMenu extends Validator
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
    	if(!isset($params[0])) {
    		return $errStr;
    	}
    	// user_id or clear_flag
    	// 0以上ならばヘッダーメニュー削除
    	$clear_flag = $attributes;
    	if($clear_flag == null || $clear_flag === "0") {
    		// 新規
    		return;
    	}
    	$container =& DIContainerFactory::getContainer();
    	$filterChain =& $container->getComponent("FilterChain");
    	$headerMenu =& $filterChain->getFilterByName($params[0]);
		if(!$headerMenu) {
			//headerMenuフィルター指定なし
			return $errStr;
		}
		// クリア
		$headerMenu->clear();
    	return;
    }
}
?>
