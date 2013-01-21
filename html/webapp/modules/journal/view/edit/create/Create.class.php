<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Edit_Create extends Action
{
    // リクエストパラメータを受け取るため
    var $room_id = null;
    var $module_id = null;
    
    // バリデートによりセット
	var $journal_obj = null;

    // 使用コンポーネントを受け取るため
	var $db = null;

    // 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$count = $this->db->countExecute("journal", array("room_id"=>$this->room_id));
    	$journal_name = JOURNAL_NEW_TITLE.($count+1);
    	$this->journal_obj['journal_name'] = $journal_name;

    	return 'success';
    }
}
?>