<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 質問毎の回答画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Edit_Answer extends Action
{
    // 使用コンポーネントを受け取るため
    var $questionnaireView = null;

    // validatorから受け取るため
	var $questionnaire = null;
	var $question = null;

    // 値をセットするため
	var $answers = null;
	var $notAnswerCount = null;

    /**
     * 質問毎の回答画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->answers = $this->questionnaireView->getQuestionaryAnswer();
		if ($this->answers === false) {
			return "error";
		}
		
		$this->notAnswerCount = $this->questionnaire["answer_count"] - count($this->answers);
		
		return "success";
    }
}
?>
