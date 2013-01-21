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
class Journal_Action_Edit_Category extends Action
{
    // リクエストパラメータを受け取るため
    var $category_id = null;
	var $category_name = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * リスト編集アクション
     *
     * @access  public
     */
    function execute()
    {	
    	//更新
    	$params = array(
			"category_name" => $this->category_name
		);
		$where_params = array(
			"category_id" => intval($this->category_id)
		);
    	$result = $this->db->updateExecute("journal_category", $params, $where_params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
}
?>
