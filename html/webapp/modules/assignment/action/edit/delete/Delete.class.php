<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 課題削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Edit_Delete extends Action
{
	// コンポーネントを受け取るため
	var $assignmentAction = null;
	
    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->assignmentAction->deleteAssignment()) {
    		return 'error';
    	}
    	return 'success';
    }
}
?>
