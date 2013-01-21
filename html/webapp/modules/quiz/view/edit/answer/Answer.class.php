<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 問題毎の解答画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Edit_Answer extends Action
{
    // 使用コンポーネントを受け取るため
    var $quizView = null;

    // validatorから受け取るため
	var $quiz = null;
	var $question = null;

    // 値をセットするため
	var $answers = null;
	var $notAnswerCount = null;
	var $correctPercent = null;
	var $scoreItemShow = null;

    /**
     * 問題毎の解答画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->answers = $this->quizView->getQuestionaryAnswer();
		if ($this->answers === false) {
			return "error";
		}

		$counts = $this->quizView->getAnswerFlagCount();
		if ($counts === false) {
			return "error";
		}
	
		if (empty($this->quiz["answer_count"]) || empty($counts[QUIZ_ANSWER_CORRECT_VALUE])) {
			$this->correctPercent = 0;
		} else {
			$this->correctPercent = $counts[QUIZ_ANSWER_CORRECT_VALUE] / $this->quiz["answer_count"] * 100;
		}
		
		$this->notAnswerCount = $this->quiz["answer_count"] - count($this->answers);
		
		$this->scoreItemShow = true;
		
		return "success";
    }
}
?>
