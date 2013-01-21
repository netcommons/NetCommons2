<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答結果画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Main_Answer extends Action
{
    // リクエストパラメータを受け取るため
 	var $block_id = null;
    var $summary_id = null;
    var $prefix_id_name = null;
 	var $target_id_name = null;

    // 使用コンポーネントを受け取るため
    var $quizView = null;
	var $session = null;

    // validatorから受け取るため
	var $quiz = null;
	var $summary = null;

    // 値をセットするため
	var $questions = null;
	var $answerSummaryShow = null;
	var $correctItemShow = null;
	var $questionaryAnswerLinkShow = null;
	var $scoreItemShow = null;

    /**
     * 解答結果画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->questions = $this->quizView->getAnswer($this->summary_id);

		if (empty($this->questions)) {
			return "error";
		}

		if (!empty($this->prefix_id_name)) {
			$this->answerSummaryShow = true;
		}

		if ($this->quiz["correct_flag"] == _ON) {
			$this->correctItemShow = true;
		}
		if ($this->session->getParameter("quiz_edit". $this->block_id) == _ON) {
			$this->correctItemShow = true;
			$this->questionaryAnswerLinkShow = true;
			$this->scoreItemShow = true;
		}

		return "success";
    }
}
?>
