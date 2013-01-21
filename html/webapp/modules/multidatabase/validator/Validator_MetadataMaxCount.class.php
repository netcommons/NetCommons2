<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *  CSV入力チェック
 *  リクエストパラメータ
 *  var $multidatabase_id = null;
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_MetadataMaxCount extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     *                  
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
    	if($attributes['metadata_id'] != 0) {
    		return;
    	}
		
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$db =& $container->getComponent("DbObject");
    	$count = $db->countExecute("multidatabase_metadata", array("multidatabase_id" => intval($attributes['multidatabase_id'])));
        if($count >= MULTIDATABASE_MAX_METADATA_COUNT) {
        	return sprintf($smartyAssign->getLang("mdb_max_metadata_count"), MULTIDATABASE_MAX_METADATA_COUNT);
        }
		
    	return;
    }
}
?>