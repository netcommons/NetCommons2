<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリの削除アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Categorydel extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $category_id = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * リスト編集アクション
     *
     * @access  public
     */
    function execute()
    {
    	//データ取得
    	$params = array(
			"category_id"=>intval($this->category_id)
		);
    	
    	$result = $this->db->deleteExecute("journal_category", $params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
    	$update_params = array("category_id" => 0);
    	$result = $this->db->updateExecute("journal_post", $update_params, $params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
}
?>
