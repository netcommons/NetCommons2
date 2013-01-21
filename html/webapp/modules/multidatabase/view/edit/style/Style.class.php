<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース表示方法変更画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    
    // バリデートによりセット
	var $mdb_obj = null;
	
	// コンポーネントを受け取るため
	var $mdbView = null;
	
	// 値をセットするため
	var $sort_metadatas = null;
	
    /**
     * 汎用データベース表示変更
     *
     * @access  public
     */
    function execute()
    {
		$sort_params = array(
    		"multidatabase_id" => intval($this->mdb_obj['multidatabase_id']),
    		"sort_flag" => _ON,
    		"list_flag" => _ON
    	);
    	
    	$this->sort_metadatas = $this->mdbView->getMetadatas($sort_params);
    	if($this->sort_metadatas === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
}
?>
