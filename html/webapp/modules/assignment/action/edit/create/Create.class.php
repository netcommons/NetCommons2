<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Edit_Create extends Action
{
    // パラメータを受け取るため
    var $activity = null;
	var $room_id = null;

    // 使用コンポーネントを受け取るため
	var $assignmentAction = null;
	var $assignmentView = null;
	var $db = null;
	var $request = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
    		"room_id" => $this->room_id
    	);
    	$gCount = $this->db->countExecute("assignment_grade_value", $params);
    	$aCount = $this->db->countExecute("assignment_assignment", $params);
    	if ($gCount == 0 && $aCount == 0) {
    		$default = array(
    			ASSIGNMENT_GRADE_A,
    			ASSIGNMENT_GRADE_B,
    			ASSIGNMENT_GRADE_C,
    			ASSIGNMENT_GRADE_D
    		);
    		$this->request->setParameter("grade_value", $default);
    		
    		if (!$this->assignmentAction->setGradeValue()) {
    			return 'error';
    		}
    	}
    	
    	if (!$this->assignmentAction->setAssignment()) {
    		return 'error';
    	}
    	if (intval($this->activity) == _ON) {
	        if (!$this->assignmentAction->setBlock()) {
				return false;
			}
    		return 'activity';
    	} else {
	        return 'list';
    	}
    }
}
?>