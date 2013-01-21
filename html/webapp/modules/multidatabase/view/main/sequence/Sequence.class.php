<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Main_Sequence extends Action
{
    // リクエストパラメータを受け取るため
    var $multidatabase_id = null;
    var $sort = null;
	
	// バリデートによりセット
	var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
    var $mdbView = null;
 
    // 値をセットするため
    var $data_count = null;
	var $title_list = null;
	var $title_type = null;
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {   
    	if($this->sort == null || $this->sort == "") {
    		$order_params = array(
				"display_sequence" => "ASC"
			);
    	} else {
    		if($this->sort != "") {
    			$sort_arr = explode(":", $this->sort);
    			$sort_col = $sort_arr[0];
	    		$sort_dir = $sort_arr[1];
	    		$sort_dir = ($sort_dir == null || $sort_dir == "ASC") ? "ASC" : "DESC";
	    		switch($sort_col) {
	    			case "title":
	    			case "vote_count":
	    			case "insert_time":
	    				break;
	    			default:
	    				$sort_col = "display_sequence";
	    				break;
	    		}
    		} else {
    			$sort_col = "display_sequence";
    			$sort_dir = "ASC";
    		}
    		
    		$order_params = array(
	 			$sort_col  => $sort_dir,
	 			"display_sequence"  => "ASC"
	 		);
    	}
	
    	$this->title_list = $this->mdbView->getMDBTitleList($this->multidatabase_id, $this->mdb_obj['title_metadata_id'], $order_params);
    	if($this->title_list === false) {
    		return 'error';
    	}
    	$this->data_count = count($this->title_list);

    	$metadata = $this->mdbView->getMetadataById($this->mdb_obj['title_metadata_id']);
    	$this->title_type = $metadata["type"];

		return 'success';
    }
}
?>
