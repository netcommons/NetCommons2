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
class Multidatabase_Validator_FileExist extends Validator
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
		
    	if(empty($attributes)) {
			return $errStr;
		}
		
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$db =& $container->getComponent("DbObject");
		$request =& $container->getComponent("Request");
		
		$params = array("upload_id" => intval($attributes));
		$file = $db->selectExecute("multidatabase_file", $params);
		if($file === false || !isset($file[0])) {
			return $errStr;
		}
		
		$request->setParameter("file", $file[0]);
		
    	return;
    }
}
?>