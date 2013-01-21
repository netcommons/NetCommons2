<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Edit_Category extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $room_id = null;
    var $journal_id = null;
    
    // バリデートによりセット
	var $journal_obj = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	
	// 値をセットするため
	var $category_count = null;
	var $categories = null;
	
    /**
     * Journal編集画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"journal_id"=>intval($this->journal_id)
		);
		$this->category_count = $this->db->countExecute("journal_category", $params);
        if ($this->category_count === false) {
        	return 'error';
        }
        
        $order_params = array(
        	"display_sequence" => "ASC"
        );
		$this->categories = $this->db->selectExecute("journal_category", $params, $order_params);
    	if($this->categories === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>
