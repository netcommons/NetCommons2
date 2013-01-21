<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_ContentExists extends Validator
{
    /**
     * コンテンツ存在チェックバリデータ
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
		$mdbView =& $container->getComponent("mdbView");
		
		$params = array(
    		"multidatabase_id" => intval($attributes['multidatabase_id'])
    	);
    	$metadatas = $mdbView->getMetadatas($params);
    	if(empty($metadatas)) {
    		return $errStr;
    	}
    	
    	$request =& $container->getComponent("Request");
    	$request->setParameter("metadatas", $metadatas);
		
		$result = $mdbView->getMDBDetail($attributes['content_id'], $metadatas);
        if (empty($result)) {
	       	return $errStr;
        }
        
		$request->setParameter("detail", $result);
        
        return;
    }
}
?>