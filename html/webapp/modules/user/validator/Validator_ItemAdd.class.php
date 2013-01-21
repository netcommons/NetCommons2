<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目テーブルの入力チェック（項目追加-項目編集）maple.ini->key指定すること
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Validator_ItemAdd extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$usersView =& $container->getComponent("usersView");
		
    	// item_id取得
    	$item_id = intval($attributes["item_id"]);
    	if($item_id != 0) {
    		// 項目編集
    		$items =& $usersView->getItemById($item_id, array($usersView,"_getShowItemsFetchcallback"), array(true));
    		if($items === false) return $errStr;
 			
 			if($items['system_flag'] == _ON && $items['type'] != $attributes["type"]) {
 				//システムで使用するのものなのに、タイプが変更された
 				return $errStr;	
 			}
 			if(!isset($attributes["require_flag"])) {
				$attributes["require_flag"] = _OFF;
 			}
 			
 			if(($items['tag_name'] == "login_id" || $items['tag_name'] == "handle" || $items['tag_name'] == "password") &&
 				$attributes["require_flag"] == _OFF) {
 				//ログインID、パスワード、ハンドルは必ず必須
 				return $errStr;	
 			}
 			
 			if($items['tag_name'] != "" && $items['tag_name'] != "email" && $items['tag_name'] != "user_name" &&
 				isset($attributes["allow_public_flag"]) && 
 				$attributes["allow_public_flag"] == _ON) {
 				//公開非公開の設定ができない項目
 				return $errStr;	
 			}
			
 			if($items['type'] != "email" && $items['type'] != "mobile_email" && 
 				isset($attributes["allow_email_reception_flag"]) && 
 				$attributes["allow_email_reception_flag"] == _ON) {
 				//メールでないのに受け取りフラグがON
 				return $errStr.$attributes["allow_email_reception_flag"];	
 			}
 				
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
 		
    	}
    	if(($item_id==0 || $items['system_flag'] == _OFF) && ($attributes["type"] == USER_TYPE_RADIO || $attributes["type"] == USER_TYPE_SELECT || $attributes["type"] == USER_TYPE_CHECKBOX)) {
    		//
    		// 選択式
    		//
    		if(!isset($attributes["options"]) || count($attributes["options"]) == 0) {
    			//リストに1項目も指定していない
    			return USER_ERR_NONEEXISTS_OPTIONS;
    		}
    		$select_count = 0;
    		$option_arr = array();
    		foreach($attributes["options"] as $key => $options) {
    			if(in_array($options, $option_arr, true)) {
    				//同じリスト値が存在する
    				return USER_ERR_DUPLICATION_CHAR_OPTIONS;
    			}
    			array_push($option_arr, $options);
    			if(preg_match("/\|/", $options)) {
    				//禁止文字「|」
    				return USER_ERR_PROHIBITION_CHAR_OPTIONS;
    			}
    			$default_selected = isset($attributes["default_selected"][$key]) ? _ON : _OFF;
    			if($attributes["type"] == USER_TYPE_RADIO || $attributes["type"] == USER_TYPE_SELECT) {
    				if($default_selected) $select_count++;
    				//複数選択されている
    				if($select_count > 1) return USER_ERR_PROHIBITION_MULTIPLE_OPTIONS;
    			}
	    	}
    	}
    	
    	return;
    }
}
?>
