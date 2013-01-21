<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ編集アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $visible_item = null;
    var $default_sort = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	
	// 値をセットするため
	
    /**
     * リスト編集アクション
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"block_id"=>intval($this->block_id)
		);
		//データがなければ、update
    	$mdb = $this->db->selectExecute("multidatabase_block", $params);
    	if($mdb === false) {
    		return 'error';
    	}
    	if(isset($mdb[0])) {
    		$update_params = array(
    			"visible_item" => $this->visible_item,
    			"default_sort" => $this->default_sort
    		);
    		
	    	$result = $this->db->updateExecute("multidatabase_block", $update_params, $params, true);
	    	if($result === false) {
	    		return 'error';
	    	}
    	}
    	
    	$sort_metadata_sesseion = $this->session->getParameter(array("multidatabase", $this->block_id, "sort_metadata"));
    	if(!empty($sort_metadata_sesseion)) {
    		$this->session->removeParameter(array("multidatabase", $this->block_id, "sort_metadata"));
    	}
    	
    	return 'success';
    }
}
?>
