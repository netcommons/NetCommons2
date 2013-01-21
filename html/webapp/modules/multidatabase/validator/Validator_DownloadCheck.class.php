<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルのダウンロードチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_DownloadCheck extends Validator
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
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		if(empty($attributes['upload_id']) 
			|| empty($attributes['metadata_id'])) {
			return $smartyAssign->getLang("_invalid_input");
		}
		
		$db =& $container->getComponent("DbObject");
		$request =& $container->getComponent("Request");
		$mdbView =& $container->getComponent("mdbView");
		
		$params = array("upload_id" => intval($attributes['upload_id']));
		$file = $db->selectExecute("multidatabase_file", $params);
		
		if($file === false || !isset($file[0])) {
			return $smartyAssign->getLang("mdb_err_no_file");
		}
		
		$metadata = $mdbView->getMetadataById($attributes['metadata_id']);
    	if($metadata === false || !isset($metadata)) {
			return $smartyAssign->getLang("_invalid_input");
		}
		
		if($file[0]['file_password'] != "" && $metadata['file_password_flag'] == _ON) {
			if($attributes['password'] == "") {
				return $smartyAssign->getLang("mdb_err_required_password");
			}
			
			if($attributes['password'] != $file[0]['file_password']) {
				return $smartyAssign->getLang("mdb_err_incorrectpassword");
			}
		}
		
		$request->setParameter("file", $file[0]);
		
    	return;
    }
}
?>