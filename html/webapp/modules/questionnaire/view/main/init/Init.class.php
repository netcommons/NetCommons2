<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート初期画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Questionnaire_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $questionnaireView = null;
	var $session = null;
	var $mobileView = null;

	// validatorから受け取るため
	var $questionnaire = null;

	// 値をセットするため
	var $answerLinkShow = null;
	var $totalLinkShow = null;
	var $questions = null;
	var $choiceDisplay = null;
	var $imageAuthenticationGenerator = null;
	var $block_num = null;

	/**
	 * アンケート初期画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->answerLinkShow = $this->questionnaireView->isAnswered();

		$answerEndIDs = $this->session->getParameter("questionnaire_answer_end_ids". $this->block_id);
		$answerEnd = false;
		if (is_array($answerEndIDs) && in_array($this->questionnaire["questionnaire_id"], $answerEndIDs)) {
			$answerEnd = true;
		}

		if ($this->questionnaire["total_flag"] == _ON &&
				($this->answerLinkShow || $answerEnd)) {
			$this->totalLinkShow = true;
		}

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		$confirmDatas = $this->session->getParameter('questionnaire_confirm' . $this->block_id);
		$this->session->removeParameter('questionnaire_confirm' . $this->block_id);

		$userID = $this->session->getParameter("_user_id");
		if (($this->questionnaire["nonmember_flag"] == _OFF && empty($userID)) ||
				$this->questionnaire["status"] == QUESTIONNAIRE_STATUS_END_VALUE ||
				$answerEnd ||
				($this->questionnaire["repeat_flag"] == _OFF && $this->answerLinkShow)) {

			$this->session->removeParameter("questionnaire_current_sequence". $this->block_id);
			$this->session->removeParameter("questionnaire_question_id_array". $this->block_id);
			$this->session->removeParameter("questionnaire_current_summary_id". $this->block_id);
			$this->session->removeParameter("questionnaire_nonmember_answers". $this->block_id);

			return "result";
		}

		$this->imageAuthenticationGenerator = time();

		if ($this->questionnaire["questionnaire_type"] != QUESTIONNAIRE_TYPE_LIST_VALUE) {
			$questionIDs = $this->session->getParameter("questionnaire_question_id_array". $this->block_id);
			if (empty($questionIDs)) {
				$this->_setKeyPassFlag();
				return "start";
			}

			return "continue";
		}

		$this->questions = $this->questionnaireView->getQuestions();
		if (empty($this->questions)) {
			return "error";
		}

		$this->choiceDisplay = QUESTIONNAIRE_CHOICE_DISPLAY_NORMAL;

		if (!empty($confirmDatas)) {
			foreach (array_keys($this->questions) as $questionId) {
				$answerValue = $confirmDatas['answer_value'][$questionId];
				if ($this->questions[$questionId]['question_type'] != QUESTIONNAIRE_QUESTION_TYPE_TEXTAREA_VALUE) {
					$choices = $this->questions[$questionId]['choices'];
					$answerValue = $this->questionnaireView->getAnswerValues($answerValue, $choices);
				}
				$this->questions[$questionId]['answer']['answer_value'] = $answerValue;
			}
		}

		$this->_setKeyPassFlag();
		return "list";
	}
	function _setKeyPassFlag() {
		if ($this->questionnaire['keypass_use_flag'] == _ON) { 
		    $this->session->setParameter('questionnaire_keypass_check_flag'. $this->block_id, _ON);
        }
        else {
		    $this->session->removeParameter('questionnaire_keypass_check_flag'. $this->block_id);
        }
	}
}
?>