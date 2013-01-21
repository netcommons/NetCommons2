<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 回答確認アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Action_Main_Confirm extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $choice_id = null;
	var $answer_value = null;

	// 使用コンポーネントを受け取るため
	var $session = null;

	// validatorから受け取るため
	var $answerChoiceIDs = null;

	/**
	 * 回答確認アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$confirmDatas = array(
			'choice_id' => $this->choice_id,
			'answer_value' => $this->answer_value,
			'answerChoiceIDs' => $this->answerChoiceIDs
		);
		$this->session->setParameter('questionnaire_confirm' . $this->block_id, $confirmDatas);

		return 'success';
	}
}
?>