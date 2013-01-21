<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $visible_item = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
	// 値をセットするため
	var $journal = null;
	
    /**
     * 表示方法変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"block_id"=>intval($this->block_id)
		);
		//データがなければ、update
    	$this->journal = $this->db->selectExecute("journal_block", $params);
    	if($this->journal === false) {
    		return 'error';
    	}
    	if(isset($this->journal[0])) {
    		$update_params = array(
    			"visible_item" => $this->visible_item
    		);
    		
	    	$result = $this->db->updateExecute("journal_block", $update_params, $params, true);
	    	if($result === false) {
	    		return 'error';
	    	}
    	}
    	
    	return 'success';
    }
}
?>
