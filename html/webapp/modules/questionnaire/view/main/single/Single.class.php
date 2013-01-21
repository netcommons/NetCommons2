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
class Questionnaire_View_Main_Single extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
 
	// 使用コンポーネントを受け取るため
	var $questionnaireView = null;
 	var $session = null;
	var $mobileView = null; // Mobile menu with page mod by AllCreator

	// validatorから受け取るため
	var $questionnaire = null;

	// 値をセットするため
	var $question = null;
	var $currentSequence = null;
	var $questionCount = null;
	var $single = null;
	var $choiceDisplay = null;

	var $block_num = null;  // Mobile menu with page mod by AllCreator

	/**
	 * アンケート初期画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$questionIDs = $this->session->getParameter("questionnaire_question_id_array". $this->block_id);
		if (empty($questionIDs)) {
			$questionIDs = $this->questionnaireView->getQuestionIDs();
			$this->session->setParameter("questionnaire_question_id_array". $this->block_id, $questionIDs);
		}
		if (empty($questionIDs)) {
			return "error";
		}
		
		$this->questionCount = count($questionIDs);

		$this->currentSequence = $this->session->getParameter("questionnaire_current_sequence". $this->block_id);
		if (empty($this->currentSequence)) {
			$this->currentSequence = 1;
			$this->session->setParameter("questionnaire_current_sequence". $this->block_id, $this->currentSequence);
		}

		$this->question = $this->questionnaireView->getQuestion($questionIDs[$this->currentSequence]);
		if (empty($this->question)) {
			return "error";
		}

		$this->single = true;
		$this->choiceDisplay = QUESTIONNAIRE_CHOICE_DISPLAY_NORMAL;

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		return "success";
	}
}
?>