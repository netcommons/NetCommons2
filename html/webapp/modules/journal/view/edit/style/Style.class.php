<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌表示方法変更画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $room_id = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	
	// 値をセットするため
	var $journal_obj = null;
	
    /**
     * Journal編集画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"block_id"=>intval($this->block_id)
		);
		//データがなければ
		$journal = $this->db->selectExecute("journal_block", $params);
    	if($journal === false) {
    		return 'error';
    	}
    	$this->journal_obj = $journal[0];
		return 'success';
    }
}
?>
