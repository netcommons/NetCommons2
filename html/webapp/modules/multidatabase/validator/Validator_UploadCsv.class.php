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
class Multidatabase_Validator_UploadCsv extends Validator
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
		$mdbView =& $container->getComponent("mdbView");
		$mdbAction =& $container->getComponent("mdbAction");
		$db =& $container->getComponent("DbObject");
		
		$errStr = $smartyAssign->getLang("_invalid_input");
		
		if(!isset($attributes)) {
			return $errStr;
		}
		
		$uploadsAction =& $container->getComponent("uploadsAction");
		$file = $uploadsAction->uploads();
		
		if(empty($file) || strtolower($file[0]['extension']) != "csv") {
			return MULTIDATABASE_IMPORT_FILE_TYPE_EORROR;
		}
		
		if(isset($file[0]['error_mes']) && $file[0]['error_mes'] != "") {
			return $file[0]['error_mes'];
		}
		
		$csv_file = FILEUPLOADS_DIR."multidatabase/".$file[0]['physical_file_name'];
    	$handle = fopen($csv_file, 'r');
    	if($handle === false) {
    		return MULTIDATABASE_IMPORT_FILE_OPEN_EORROR;
    	}
    	
    	$row_data = array();
    	while (($data = $mdbAction->fgetcsv_reg($handle)) !== FALSE) {
		    $num = count($data);
		    for ($c=0; $c < $num; $c++) {
		    	$data[$c] = mb_convert_encoding($data[$c], "UTF-8", "SJIS");
		    }
		    $row_data[] = $data;
		}

		fclose($handle); 
		//データが件数を取得（ヘッダ部を除く）
		$row_num = (count($row_data) - 1);
		if($row_num <= 0) {
    		return MULTIDATABASE_IMPORT_FILE_NO_DATA;
		}
		
		$metadatas = $mdbView->getMetadatas(array("multidatabase_id" => intval($attributes)));
		if($metadatas === false) {
    		return $errStr;
    	}

    	if(count($metadatas) != count($row_data[0])) {
	    	return MULTIDATABASE_IMPORT_FILE_METADATA_NUM_ERROR;
    	}else {
    		$count = 0;
	    	foreach(array_keys($metadatas) as $i) {
    			if($metadatas[$i]['name'] != $row_data[0][$count]) {
    				return $row_data[0][$count].":".MULTIDATABASE_IMPORT_FILE_METADATA_ERROR;
    			}
    			$count++;	
	    	}
    	}
    	array_shift($row_data);
    	
    	$session =& $container->getComponent("Session");
    	$session->setParameter(array("multidatabase_csv_data", $attributes), $row_data);

    	return;
    }
}
?>