<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Edit_Modify extends Action
{
    // validatorから受け取るため
    var $assignment = null;

	// 使用コンポーネントを受け取るため
    var $assignmentView = null;

    // 値をセットするため
	var $current_assignment_id = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->current_assignment_id = $this->assignmentView->getCurrentAssignmentID();
		return "success";
    }
}
?>
