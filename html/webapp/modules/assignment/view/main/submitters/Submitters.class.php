<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 提出者リスト画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Main_Submitters extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
    var $assignment_id = null;
	var $scroll = null;
	var $yet_submitter = null;
	var $prefix_id_name = null;

    // 使用コンポーネントを受け取るため
    var $configView = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $assignment = null;
	var $submit_id = null;
	var $submit_user_id = null;
	var $submitters = null;
	var $submitterCount = null;

    // 値をセットするため
    var $visibleRows = null;
	var $popup_flag = _OFF;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		if ($this->prefix_id_name == ASSIGNMENT_SUBMITTERS_PREFIX_NAME.$this->assignment_id) {
			$this->popup_flag = _ON;
		}

    	$this->yet_submitter = intval($this->yet_submitter);
    	if ($this->scroll == _ON) {
	        return "scroll";
    	}

		if ($this->prefix_id_name == ASSIGNMENT_SUBMITTERS_PREFIX_NAME.$this->assignment_id) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", _ON);
			$view->setAttribute("define:theme_name", "system");
			$view->setAttribute("define:print", _ON);
		}

		$config = $this->configView->getConfigByConfname($this->module_id, "visible_row");
		if ($config === false) {
        	return "error";
        }
        $this->visibleRows = $config["conf_value"];

        return "screen";
    }
}
?>
