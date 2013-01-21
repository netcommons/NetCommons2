<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Edit_Import_Sample extends Action
{
    // リクエストパラメータを受け取るため
    var $multidatabase_id = null;
    
    // バリデートによりセット
	var $mdb_obj = null;
	
	// コンポーネントを受け取るため
	var $mdbView = null;
	var $csvMain = null;
	
	// 値をセットするため
	
    /**
     * 汎用データベース画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$metadatas = $this->mdbView->getMetadatas(array("multidatabase_id" => intval($this->multidatabase_id)));
		if($metadatas === false) {
    		return 'error';
    	}
    	
    	$data = array();
    	foreach ($metadatas as $metadata) {
			$data[] = $metadata['name'];
		}
		$this->csvMain->add($data);
		$this->csvMain->download($this->mdb_obj['multidatabase_name']);
		
    }
}
?>
