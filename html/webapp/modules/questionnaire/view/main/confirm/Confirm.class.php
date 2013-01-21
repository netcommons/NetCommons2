<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 回答確認画面アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Main_Confirm extends Action
{
	// 使用コンポーネントを受け取るため
	var $questionnaireView = null;

	// validatorから受け取るため
	var $questionnaire = null;

	// 値をセットするため
	var $questions = null;
	var $isConfirm = null;

	/**
	 * 回答画面確認アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->questions = $this->questionnaireView->getAnswer();
		if (empty($this->questions)) {
			return 'error';
		}
		$this->isConfirm = true;

		return 'success';
	}
}
?>