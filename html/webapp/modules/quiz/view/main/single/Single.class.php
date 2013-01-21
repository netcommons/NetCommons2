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
class Quiz_View_Main_Single extends Action
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
	var $question = null;
	var $currentSequence = null;
	var $questionCount = null;
	var $single = null;
	var $choiceDisplay = null;
	var $block_num = null;

	/**
	 * 小テスト初期画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$questionIDs = $this->session->getParameter("quiz_question_id_array". $this->block_id);
		if (empty($questionIDs)) {
			$questionIDs = $this->quizView->getQuestionIDs();
			$this->session->setParameter("quiz_question_id_array". $this->block_id, $questionIDs);
		}
		if (empty($questionIDs)) {
			return "error";
		}

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		$this->questionCount = count($questionIDs);

		$this->currentSequence = $this->session->getParameter("quiz_current_sequence". $this->block_id);
		if (empty($this->currentSequence)) {
			$this->currentSequence = 1;
			$this->session->setParameter("quiz_current_sequence". $this->block_id, $this->currentSequence);
		}

		$this->question = $this->quizView->getQuestion($questionIDs[$this->currentSequence]);
		if (empty($this->question)) {
			return "error";
		}

		$this->single = true;
		$this->choiceDisplay = QUIZ_CHOICE_DISPLAY_NORMAL;

		return "success";
	}
}
?>