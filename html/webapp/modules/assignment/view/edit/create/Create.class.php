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
class Assignment_View_Edit_Create extends Action
{
	// 使用コンポーネントを受け取るため
	var $assignmentView = null;

    // 値をセットするため
    var $assignment = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$count = $this->assignmentView->getAssignmentCount();
		if ($count === false) {
			$count = 0;
		}
		$this->assignment = array(
			"assignment_id" => 0,
			"assignment_name" => sprintf(ASSIGNMENT_NEW_NAME, $count + 1),
			"grade_authority" => _AUTH_CHIEF
		);

		return "success";
    }
}
?>
