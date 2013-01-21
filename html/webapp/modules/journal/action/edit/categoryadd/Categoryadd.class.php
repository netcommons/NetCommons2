<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ追加処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Categoryadd extends Action
{
    // パラメータを受け取るため
    var $block_id = null;
	var $journal_id = null;
	var $category_name = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * Todo追加処理
     *
     * @access  public
     */
    function execute()
    {
    	$display_sequence = $this->db->maxExecute("journal_category", "display_sequence", array("journal_id"=>intval($this->journal_id)));
    	if($display_sequence === false) {
    		return 'error';
    	}
    	$params = array(
			"journal_id" => intval($this->journal_id),
			"category_name" => $this->category_name,
			"display_sequence" => $display_sequence+1
			
		);
    	$category_id = $this->db->insertExecute("journal_category", $params, true, "category_id");
    	if($category_id === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
    
}
?>
