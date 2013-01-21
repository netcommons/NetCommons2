<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト参照画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Edit_Reference extends Action
{
    // 使用コンポーネントを受け取るため
    var $quizView = null;

    // validatorから受け取るため
	var $quiz = null;

    // 値をセットするため
	var $questions = null;
	var $reference = null;
	var $choiceDisplay = null;
	
    /**
     * 小テスト照画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->questions = $this->quizView->getQuestions();
		if (empty($this->questions)) {
			return "error";
		}

		$this->reference = true;
		$this->choiceDisplay = QUIZ_CHOICE_DISPLAY_REFERENCE;

		return "success";
    }
}
?>
