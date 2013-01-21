<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 回答アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Action_Main_Answer extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireView = null;
    var $questionnaireAction = null;
    var $session = null;

    // validatorから受け取るため
	var $questionnaire = null;
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
	var $answerSummaryShow = null;
	var $answerItemShow = null;

    /**
     * 回答アクション
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");

		foreach (array_keys($this->questions) as $question_id) {
			$params["questionnaire_id"] = $this->questionnaire["questionnaire_id"];
			$params["question_id"] = $question_id;
			$params["summary_id"] = $this->summary_id;
			$params["answer_value"] = $this->answer_value[$question_id];

			if (!$this->questionnaireAction->insertAnswer($params)) {
				return "error";
			}

	       	$this->questions[$question_id]["answer"] = $params;
        	if ($this->questions[$question_id]["question_type"] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
	        	$answerValues = $this->questionnaireView->getAnswerValues($params["answer_value"], $this->questions[$question_id]["choices"]);
	        	$this->questions[$question_id]["answer"]["answer_value"] = $answerValues;
        	} else {
        		$this->questions[$question_id]["answer"]["answer_value"] = $params["answer_value"];
        	}
		}

		if (!$this->questionnaireAction->incrementChoice($this->answerChoiceIDs)) {
			return "error";
        }

        if (!$this->questionnaireAction->updateSummary($this->summary_id)) {
        	return "error";
        }

		$this->currentSequence = $this->session->getParameter("questionnaire_current_sequence". $this->block_id);

		$result = $this->questionnaireAction->updateQuestionnaireAnswer(true);
		if (!$result) {
        	return "error";
        }

		if ($this->questionnaire["questionnaire_type"] != QUESTIONNAIRE_TYPE_LIST_VALUE) {
			$questionIDs = $this->session->getParameter("questionnaire_question_id_array". $this->block_id);
			$this->questionCount = count($questionIDs);
		}

		$answerEndIDs = array();
		if ($this->questionnaire["questionnaire_type"] == QUESTIONNAIRE_TYPE_LIST_VALUE ||
				$this->currentSequence == $this->questionCount) {
			$answerEndIDs = $this->session->getParameter("questionnaire_answer_end_ids". $this->block_id);
			$answerEndIDs[] = $this->questionnaire["questionnaire_id"];
			$answerEndIDs = array_unique($answerEndIDs);
			$this->session->setParameter("questionnaire_answer_end_ids". $this->block_id, $answerEndIDs);
		} elseif ($this->currentSequence < $this->questionCount) {
			$this->session->setParameter("questionnaire_current_sequence". $this->block_id, $this->currentSequence + 1);
			$this->nextQuestion = true;
		}

		if ($this->questionnaire["questionnaire_type"] != QUESTIONNAIRE_TYPE_LIST_VALUE &&
				$this->currentSequence == $this->questionCount) {
			$this->questionEnd = true;
			$this->summary["summary_id"] = $this->summary_id;
		}

		$this->answerHide = ($this->questionnaire["questionnaire_type"] != QUESTIONNAIRE_TYPE_LIST_VALUE);
		$this->answerSummaryShow = false;
		$this->answerItemShow = true;

		$this->session->removeParameter('questionnaire_confirm' . $this->block_id);

		if ($this->questionnaire["mail_send"] == _ON &&
				($this->questionnaire["questionnaire_type"] == QUESTIONNAIRE_TYPE_LIST_VALUE || $this->questionEnd)) {
			$this->session->setParameter("questionnaire_mail_summary_id", $this->summary_id);
			if ($mobile_flag == _ON) {
				return "mail";
			}
		}

		if ($mobile_flag == _ON
			&& $this->questionnaire["questionnaire_type"] == QUESTIONNAIRE_TYPE_LIST_VALUE) {
			return 'answer';
		}

		if ($this->questionnaire["questionnaire_type"] == QUESTIONNAIRE_TYPE_LIST_VALUE
			|| !is_array($answerEndIDs)
			|| !in_array($this->questionnaire["questionnaire_id"], $answerEndIDs)) {
			return "init";
		} else {
			return "answer";
		}
    }
}
?>
