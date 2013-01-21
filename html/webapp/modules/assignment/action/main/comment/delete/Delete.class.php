<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Main_Comment_Delete extends Action
{
    // 使用コンポーネントを受け取るため
	var $assignmentAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->assignmentAction->deleteComment()) {
    		return 'error';
    	}
        return 'success';
    }
}
?>
