<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネット編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Edit_Modify extends Action
{
    // リクエストパラメータを受け取るため
    var $journal_id = null;

    // 使用コンポーネントを受け取るため
	var $db = null;

    // 値をセットするため
	var $journal_obj = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$result = $this->db->selectExecute("journal", array("journal_id"=>$this->journal_id));
        if ($result === false) {
        	return 'error';
        }
        
        $this->journal_obj = $result[0];
   		return 'success';
    }
}
?>