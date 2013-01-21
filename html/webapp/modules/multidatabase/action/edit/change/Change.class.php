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
class Multidatabase_Action_Edit_Change extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $multidatabase_id = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    function execute()
    {
    	$params = array(
			"block_id"=>intval($this->block_id)
		);

    	$block_obj = $this->db->selectExecute("multidatabase_block", $params);
    	if($block_obj === false) {
    		return 'error';
    	}

    	if(isset($block_obj[0])) {
    		$update_params = array(
    			"multidatabase_id"=>intval($this->multidatabase_id)
    		);
	    	$result = $this->db->updateExecute("multidatabase_block", $update_params, $params, true);
	    	if($result === false) {
	    		return 'error';
	    	}
    	} else {
    		$params = array(
    			"block_id" => $this->block_id,
				"multidatabase_id" => $this->multidatabase_id
			);
	    	$result = $this->db->insertExecute("multidatabase_block", $params, true);
	    	if ($result === false) {
    			return 'error';
    		}
    	}
    	return 'success';
    }
}
?>
