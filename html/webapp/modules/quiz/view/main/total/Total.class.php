<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 集計結果画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Main_Total extends Action
{
    // リクエストパラメータを受け取るため
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $quizView = null;
    var $session = null;

    // validatorから受け取るため
	var $quiz = null;

    // 値をセットするため
    var $average = null;
	var $questions = null;
	var $questionaryAnswerLinkShow = null;

    /**
     * 集計結果画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (empty($this->quiz["answer_count"])) {
			$this->average = 0;
		} else {
			$this->average = $this->quiz["quiz_score"] / $this->quiz["answer_count"];
		}

		$this->questions = $this->quizView->getTotal();
		if (empty($this->questions)) {
			return "error";
		}

		if ($this->session->getParameter("quiz_edit". $this->block_id) == _ON) {
			$this->questionaryAnswerLinkShow = true;
		}

		foreach (array_keys($this->questions) as $question_id) {
			foreach (array_keys($this->questions[$question_id]["choices"]) as $choice_label) {
				if (empty($this->questions[$question_id]["choices"][$choice_label]["choice_count"])) {
					$this->questions[$question_id]["choices"][$choice_label]["choice_count"] = 0;
				}
			}
		}

		return "success";
    }
}
?>
