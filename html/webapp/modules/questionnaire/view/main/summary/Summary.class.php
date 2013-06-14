<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 回答結果一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Main_Summary extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
    var $answer_user_id = null;
    var $scroll = null;
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireView = null;
    var $configView = null;
    var $request = null;
    var $filterChain = null;
    var $session = null;

    // validatorから受け取るため
	var $questionnaire = null;
	var $chiefItemShow = null;

	// 値をセットするため
	var $visibleRows = null;
	var $summaryCount = null;
	var $summaries = null;

    /**
     * 回答結果一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
        if($this->questionnaire['answer_show_flag']==_ON && $this->session->getParameter('_auth_id')<_AUTH_CHIEF) {
            return 'error';
        }
		if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "questionnaire_summary_list_row_count");
			if ($config === false) {
	        	return "error";
	        }

	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);
		}

		$this->summaryCount = $this->questionnaireView->getSummaryCount($this->questionnaire["questionnaire_id"], $this->answer_user_id);
		if ($this->summaryCount === false) {
			return "error";
		}

		$this->summaries = $this->questionnaireView->getSummaries();
		if ($this->summaries === false) {
			return "error";
		}

        if ($this->scroll == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", 0);

        	return "scroll";
        }

		return "screen";
    }
}
?>
