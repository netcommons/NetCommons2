<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示順変更チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_Chgseq extends Validator
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
    	$multidatabase_id = $attributes[0];
    	$content_id_arr = $attributes[1];
    	
    	$container =& DIContainerFactory::getContainer();
		$db =& $container->getComponent("DbObject");
		
		//データ取得
    	$params = array(
			"multidatabase_id"=>intval($multidatabase_id)
		);
		$contents = $db->selectExecute("multidatabase_content",$params, null, null, null, array($this, "_getContentFetchcallback") , $content_id_arr);
        if ($contents === false) {
        	return $errStr;
        }
        if(count($content_id_arr) != count($contents)) {
        	// コンテンツの数が合わない
        	return $errStr;
        }
    }
    
    
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_getContentFetchcallback($result, $content_id_arr) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(in_array($row['content_id'], $content_id_arr)) {
				$ret[$row['content_id']] = $row['content_id'];
			} else {
				// 存在しないコンテンツがあがってきている
				$ret = false;
				return $ret;
			}
		}
		return $ret;
	}
}
?>
