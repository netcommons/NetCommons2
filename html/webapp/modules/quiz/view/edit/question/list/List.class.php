<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 問題一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Edit_Question_List extends Action
{
	// 使用コンポーネントを受け取るため
    var $quizView = null;

    // validatorから受け取るため
    var $quiz = null;

    // 値をセットするため
    var $questions = null;

    /**
     * 問題一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->questions = $this->quizView->getQuestions();
		if ($this->questions === false) {
			return "error";
		}
		
		return "success";
    }
}
?>
