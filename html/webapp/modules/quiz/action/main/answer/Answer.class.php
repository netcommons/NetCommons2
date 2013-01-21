<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Action_Main_Answer extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $quizView = null;
    var $quizAction = null;
    var $session = null;

    // validatorから受け取るため
	var $quiz = null;
	var $questions = null;
	var $answer_value = null;
	var $answerChoiceIDs = null;
	var $summary_id = null;

	// 値をセットするため
	var $currentSequence = null;
	var $questionCount = null;
	var $summary = null;
	var $nextQuestion = null;
	var $questionEnd = null;
	var $answerHide = null;
	var $correctItemShow = null;
	var $answerSummaryShow = null;

    /**
     * 解答アクション
     *
     * @access  public
     */
    function execute()
    {
		$summaryScore = 0;
		foreach (array_keys($this->questions) as $question_id) {
			$params["quiz_id"] = $this->quiz["quiz_id"];
			$params["question_id"] = $question_id;
			$params["summary_id"] = $this->summary_id;
			$params["answer_value"] = $this->answer_value[$question_id];
	        if ($this->questions[$question_id]["question_type"] == QUIZ_QUESTION_TYPE_TEXTAREA_VALUE) {
	        	$params["answer_flag"] = QUIZ_ANSWER_NOT_MARK_VALUE;
	        	$params["score"] = 0;
	        } elseif ($this->questions[$question_id]["question_type"] == QUIZ_QUESTION_TYPE_WORD_VALUE) {
	        	$params["answer_flag"] = QUIZ_ANSWER_WRONG_VALUE;
		        $params["score"] = 0;

		        $answer_value = $this->quizView->getSynonym($this->answer_value[$question_id]);
		        foreach ($this->questions[$question_id]["choice_words"] as $choice) {
	        		if ($answer_value == $choice["choice_value"]) {
			        	$params["answer_flag"] = QUIZ_ANSWER_CORRECT_VALUE;
			        	$params["score"] = $this->questions[$question_id]["allotment"];
	        			break;
	        		}
	        	}
	        } elseif ($this->answer_value[$question_id] == $this->questions[$question_id]["correct"]) {
	        	$params["answer_flag"] = QUIZ_ANSWER_CORRECT_VALUE;
	        	$params["score"] = $this->questions[$question_id]["allotment"];
	        } else {
	        	$params["answer_flag"] = QUIZ_ANSWER_WRONG_VALUE;
		        $params["score"] = 0;
	        }

	        $summaryScore += $params["score"];

			if (!$this->quizAction->insertAnswer($params)) {
	        	return "error";
	        }

	        if ($this->quiz["correct_flag"] != _ON) {
	        	continue;
	        }

        	$this->questions[$question_id]["answer"] = $params;
        	if ($this->questions[$question_id]["question_type"] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
        		$this->questions[$question_id]["question_type"] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

        		$answerValues = $this->quizView->getAnswerValues($params["answer_value"], $this->questions[$question_id]["choices"]);
	        	$this->questions[$question_id]["answer"]["answer_value"] = $answerValues;
        	} else {
        		$this->questions[$question_id]["answer"]["answer_value"] = $params["answer_value"];
        	}
		}

		if (!$this->quizAction->incrementChoice($this->answerChoiceIDs)) {
			return "error";
        }

		if (!$this->quizAction->updateSummary($this->summary_id)) {
			return "error";
		}

		$this->currentSequence = $this->session->getParameter("quiz_current_sequence". $this->block_id);

		$result = $this->quizAction->updateQuizAnswer(true);

		if (!$result) {
        	return "error";
        }

		if ($this->quiz["quiz_type"] != QUIZ_TYPE_LIST_VALUE) {
			$questionIDs = $this->session->getParameter("quiz_question_id_array". $this->block_id);
			$this->questionCount = count($questionIDs);
		}

		if ($this->quiz["quiz_type"] == QUIZ_TYPE_LIST_VALUE ||
				$this->currentSequence == $this->questionCount) {
			$answerEndIDs = $this->session->getParameter("quiz_answer_end_ids". $this->block_id);
			$answerEndIDs[] = $this->quiz["quiz_id"];
			$answerEndIDs = array_unique($answerEndIDs);
			$this->session->setParameter("quiz_answer_end_ids". $this->block_id, $answerEndIDs);
		} elseif ($this->currentSequence < $this->questionCount) {
			$this->session->setParameter("quiz_current_sequence". $this->block_id, $this->currentSequence + 1);
			$this->nextQuestion = true;
		}

		if ($this->quiz["quiz_type"] != QUIZ_TYPE_LIST_VALUE &&
				$this->currentSequence == $this->questionCount) {
			$this->questionEnd = true;
			$this->summary["summary_id"] = $this->summary_id;
		}

		$this->answerHide = ($this->quiz["quiz_type"] != QUIZ_TYPE_LIST_VALUE);
		$this->correctItemShow = true;
		$this->answerSummaryShow = false;

		$this->session->removeParameter('quiz_confirm' . $this->block_id);

		if ($this->quiz["mail_send"] == _ON &&
				($this->quiz["quiz_type"] == QUIZ_TYPE_LIST_VALUE || $this->questionEnd)) {
			$this->session->setParameter("quiz_mail_summary_id", $this->summary_id);
			$mobile_flag = $this->session->getParameter("_mobile_flag");
			if ($mobile_flag == _ON) {
				return "mail";
			}
		}

		if ($this->quiz["correct_flag"] == _ON) {
			return "answer";
		} else {
			return "init";
		}
    }
}
?>
