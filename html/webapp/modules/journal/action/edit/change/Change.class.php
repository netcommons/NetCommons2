<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブッロク編集アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Change extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $journal_id = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    function execute()
    {
    	$params = array(
			"block_id"=>intval($this->block_id)
		);

    	$blocks_journal = $this->db->selectExecute("journal_block", $params);
    	if($blocks_journal === false) {
    		return 'error';
    	}

    	if(isset($blocks_journal[0])) {
    		$update_params = array(
    			"journal_id"=>intval($this->journal_id)
    		);
	    	$result = $this->db->updateExecute("journal_block", $update_params, $params, true);
	    	if($result === false) {
	    		return 'error';
	    	}
    	} else {
    		$params = array(
    			"block_id" => $this->block_id,
				"journal_id" => $this->journal_id
			);
	    	$result = $this->db->insertExecute("journal_block", $params, true);
	    	if ($result === false) {
    			return 'error';
    		}
    	}
    	return 'success';
    }
}
?>
