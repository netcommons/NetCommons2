<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 質問一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Edit_Question_List extends Action
{
	// 使用コンポーネントを受け取るため
    var $questionnaireView = null;

    // validatorから受け取るため
    var $questionnaire = null;

    // 値をセットするため
    var $questions = null;

    /**
     * 質問一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->questions = $this->questionnaireView->getQuestions();
		if ($this->questions === false) {
			return "error";
		}
		
		return "success";
    }
}
?>
