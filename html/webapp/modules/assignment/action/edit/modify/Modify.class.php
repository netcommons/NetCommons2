<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 課題の編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Edit_Modify extends Action
{
    // パラメータを受け取るため
    var $activity = null;

    // 使用コンポーネントを受け取るため
	var $assignmentAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->assignmentAction->setAssignment(true)) {
    		return 'error';
    	}
    	if ($this->activity == _ON) {
	        if (!$this->assignmentAction->setBlock()) {
				return 'error';
			}
    	}
    	return 'success';
    }
}
?>