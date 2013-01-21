<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌の削除から呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Delete extends Action
{
    // パラメータを受け取るため
    var $journal_id = null;

    // 使用コンポーネントを受け取るため
    var $journalAction = null;

    /**
     * 日誌の削除から呼ばれるアクション
     * @return boolean
     * @access  public
     */
    function execute()
    {
    	//TODO:ここにblock_idに対応した日誌のデータを削除する処理を入れる
        $result = $this->journalAction->delJournal($this->journal_id);
    	if($result === false) {
    		return 'error';
    	}
    	return 'success';
    }
}
?>
