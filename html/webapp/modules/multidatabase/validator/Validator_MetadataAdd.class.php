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
class Multidatabase_Validator_MetadataAdd extends Validator
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
		$mdbView =& $container->getComponent("mdbView");
		
    	// metadata_id取得
    	$metadata_id = intval($attributes["metadata_id"]);
    	if($metadata_id != 0) {
    		// 項目編集
    		$metadata =& $mdbView->getMetadataById($metadata_id);
 			if($metadata === false) return $errStr;
 				
 			//
	 		// Actionにデータセット
	 		//
	
			// actionChain取得
			$actionChain =& $container->getComponent("ActionChain");
			$action =& $actionChain->getCurAction();
			if(isset($params[0])) {
				BeanUtils::setAttributes($action, array($params[0]=>$metadata));
			} else {
				BeanUtils::setAttributes($action, array("items"=>$metadata));
			}
 		
    	}
    	if($attributes["type"] == MULTIDATABASE_META_TYPE_SECTION 
    		|| $attributes["type"] == MULTIDATABASE_META_TYPE_MULTIPLE) {
    		//
    		// 選択式
    		//
    		if(!isset($attributes["options"]) || count($attributes["options"]) == 0) {
    			//選択肢に1項目も指定していない
    			return MULTIDATABASE_ERR_NONEEXISTS_OPTIONS;
    		}
    		$select_count = 0;
    		$option_arr = array();
    		foreach($attributes["options"] as $key => $options) {
    			if (!strlen(trim($options))) {
    				return MULTIDATABASE_ERR_OPTION_EMPTY_NAME;
    			}
    			if(in_array($options, $option_arr, true)) {
    				//同じ選択肢値が存在する
    				return MULTIDATABASE_ERR_DUPLICATION_CHAR_OPTIONS;
    			}
    			array_push($option_arr, $options);
    			if(preg_match("/\|/", $options)) {
    				//禁止文字「|」
    				return MULTIDATABASE_ERR_PROHIBITION_CHAR_OPTIONS;
    			}
	    	}
    	}
    	
    	return;
    }
}
?>
