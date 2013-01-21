<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト初期画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Quiz_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $quizView = null;
 	var $session = null;
	var $mobileView = null;

	// validatorから受け取るため
	var $quiz = null;

	// 値をセットするため
	var $answerLinkShow = null;
	var $totalLinkShow = null;
	var $questions = null;
	var $choiceDisplay = null;
	var $imageAuthenticationGenerator = null;
	var $block_num = null;

	/**
	 * 小テスト初期画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->answerLinkShow = $this->quizView->isAnswered();

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		$answerEndIDs = $this->session->getParameter("quiz_answer_end_ids". $this->block_id);
		$answerEnd = false;
		if (is_array($answerEndIDs) && in_array($this->quiz["quiz_id"], $answerEndIDs)) {
			$answerEnd = true;
		}

		if ($this->quiz["total_flag"] == _ON &&
				($this->answerLinkShow || $answerEnd)) {
			$this->totalLinkShow = true;
		}

		$confirmDatas = $this->session->getParameter('quiz_confirm' . $this->block_id);
		$this->session->removeParameter('quiz_confirm' . $this->block_id);

		$userID = $this->session->getParameter("_user_id");
		if (($this->quiz["nonmember_flag"] == _OFF && empty($userID)) ||
				$this->quiz["status"] == QUIZ_STATUS_END_VALUE ||
				$answerEnd ||
				($this->quiz["repeat_flag"] == _OFF && $this->answerLinkShow)) {

			$this->session->removeParameter("quiz_current_sequence". $this->block_id);
			$this->session->removeParameter("quiz_question_id_array". $this->block_id);
			$this->session->removeParameter("quiz_current_summary_id". $this->block_id);
			$this->session->removeParameter("quiz_nonmember_answers". $this->block_id);

			return "result";
		}

		$this->imageAuthenticationGenerator = time();

		if ($this->quiz["quiz_type"] != QUIZ_TYPE_LIST_VALUE) {
			$questionIDs = $this->session->getParameter("quiz_question_id_array". $this->block_id);
			if (empty($questionIDs)) {
				return "start";
			}

			return "continue";
		}

		$this->questions = $this->quizView->getQuestions();
		if (empty($this->questions)) {
			return "error";
		}

		$this->choiceDisplay = QUIZ_CHOICE_DISPLAY_NORMAL;

		if (!empty($confirmDatas)) {
			foreach (array_keys($this->questions) as $questionId) {
				$answerValue = $confirmDatas['answer_value'][$questionId];
				if ($this->questions[$questionId]['question_type'] != QUIZ_QUESTION_TYPE_TEXTAREA_VALUE &&
					$this->questions[$questionId]['question_type'] != QUIZ_QUESTION_TYPE_WORD_VALUE) {

					$choices = $this->questions[$questionId]['choices'];
					$answerValue = $this->quizView->getAnswerValues($answerValue, $choices);
				}
				$this->questions[$questionId]['answer']['answer_value'] = $answerValue;
			}
		}

		return "list";
	}
}
?>