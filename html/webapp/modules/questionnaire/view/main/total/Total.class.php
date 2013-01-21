<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 集計結果画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Main_Total extends Action
{
    // リクエストパラメータを受け取るため
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireView = null;
    var $session = null;

    // validatorから受け取るため
	var $questionnaire = null;

    // 値をセットするため
	var $questions = null;
	var $questionaryAnswerLinkShow = null;

    /**
     * 集計結果画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->questions = $this->questionnaireView->getTotal();
		if (empty($this->questions)) {
			return "error";
		}

		if ($this->session->getParameter("questionnaire_edit". $this->block_id) == _ON) {
			$this->questionaryAnswerLinkShow = true;
		}


		return "success";
    }
}
?>
