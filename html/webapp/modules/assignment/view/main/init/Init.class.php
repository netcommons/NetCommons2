<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メイン画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Main_Init extends Action
{
    // パラメータを受け取るため
	var $display_hide = null;
    var $module_id = null;
	var $block_id = null;
	var $yet_submitter = null;

    // 使用コンポーネントを受け取るため
	var $session = null;
    var $configView = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $submit_id = null;
	var $submit_user_id = null;
	var $reference = null;

	var $assignment = null;
	var $response = null;
	var $reports = null;
	var $report = null;
	var $submitterCount = null;
	var $submitters = null;
	var $commentCount = null;
	var $comments = null;

	var $hasAnswerAuthority = null;
	var $hasSummaryAuthority = null;
	var $hasSubmitListView = null;
	var $submitterExists = null;

    // 値をセットするため
    var $visibleRows = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->yet_submitter = intval($this->yet_submitter);
    	$this->display_hide = intval($this->display_hide);

    	$_id = $this->session->getParameter("_id");
     	$this->session->setParameter(array("assignment","id",$this->block_id), $_id);

		$config = $this->configView->getConfigByConfname($this->module_id, "visible_row");
		if ($config === false) {
        	return "error";
        }
		
		if ($this->reference == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme_name", "system");
			$view->setAttribute("define:min_width_size", ASSIGNMENT_EDIT_MIN_SIZE);
		}
		
        $this->visibleRows = $config["conf_value"];

        return "success";
    }
}
?>
