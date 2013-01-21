<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メタデータテーブルの存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_MetadataExists extends Validator
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
		$mdbView =& $container->getComponent("mdbView");
		
    	// metadata_id取得
    	$metadata_id = intval($attributes);
    	if(isset($params[1]) && $params[1] == _ON && $metadata_id == 0) {
    		return;
    	}
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
			BeanUtils::setAttributes($action, array("metadata"=>$metadata));
		}
    	return;
    }
}
?>
