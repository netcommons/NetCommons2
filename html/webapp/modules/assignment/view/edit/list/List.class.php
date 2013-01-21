<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Edit_List extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
	var $block_id = null;
	var $scroll = null;

    // 使用コンポーネントを受け取るため
    var $assignmentView = null;
    var $configView = null;
    var $request = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $assignmentCount = null;
    var $assignments = null;

    // 値をセットするため
    var $visibleRows = null;
    var $assignment_id = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
        if ($this->scroll == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", 0);

        	return "scroll";

        } else {
			$config = $this->configView->getConfigByConfname($this->module_id, "list_row");
			if ($config === false) {
	        	return "error";
	        }
	        
	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);

	        $this->assignment_id = $this->assignmentView->getCurrentAssignmentID();
	        if ($this->assignment_id === false) {
	        	return "error";
	        }

	        return "screen";
        }
    }
}
?>
