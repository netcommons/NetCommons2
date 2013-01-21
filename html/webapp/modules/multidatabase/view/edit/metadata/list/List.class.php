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
class Multidatabase_View_Edit_Metadata_List extends Action
{
    // リクエストパラメータを受け取るため
    var $multidatabase_id = null;
    
    // バリデートによりセット
	var $mdb_obj = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	var $mdbView = null;
	
	// 値をセットするため
	var $count = null;
	var $metadatas_layout = null;
	
    /**
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"multidatabase_id" => intval($this->multidatabase_id)
		);
		$this->count = $this->db->countExecute("multidatabase_metadata", $params);
        if ($this->count === false) {
        	return 'error';
        }
		
		$this->metadatas_layout = $this->mdbView->getLayout($params);
    	if($this->metadatas_layout === false) {
    		return 'error';
    	}

		return 'success';
    }
}
?>
