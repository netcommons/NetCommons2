<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 評価項目更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Edit_SetGrade extends Action
{
    // 使用コンポーネントを受け取るため
    var $assignmentAction = null;

    /**
     * 動作フラグ更新アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->assignmentAction->setGradeValue()) {
        	return "error";
        }

		return "success";
    }
}
?>
