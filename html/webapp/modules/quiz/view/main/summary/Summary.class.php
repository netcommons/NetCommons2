<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答結果一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Main_Summary extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
    var $answer_user_id = null;
    var $scroll = null;
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $quizView = null;
    var $configView = null;
    var $request = null;
    var $filterChain = null;
    
    // validatorから受け取るため
	var $quiz = null;
	var $chiefItemShow = null;
	
	// 値をセットするため
	var $visibleRows = null;
	var $summaryCount = null;
	var $average = null;
	var $summaries = null;

    /**
     * 解答結果一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "quiz_summary_list_row_count");
			if ($config === false) {
	        	return "error";
	        }
	        
	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);
		}

		$this->summaryCount = $this->quizView->getSummaryCount($this->quiz["quiz_id"], $this->answer_user_id);
		if ($this->summaryCount === false) {
			return "error";
		}
		
		$statistics = $this->quizView->getStatistics();
		if (empty($statistics)) {
			return "error";
		}
		$this->average = $statistics[0];
		
		$this->summaries = $this->quizView->getSummaries($statistics);
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
