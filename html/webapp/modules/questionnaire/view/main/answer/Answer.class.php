<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 回答結果画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Main_Answer extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $summary_id = null;
 	var $prefix_id_name = null;
 	var $target_id_name = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireView = null;
    var $session = null;

    // validatorから受け取るため
	var $questionnaire = null;
	var $summary = null;

    // 値をセットするため
	var $questions = null;
	var $answerSummaryShow = null;
	var $questionaryAnswerLinkShow = null;
	var $answerItemShow = null;

    /**
     * 回答結果画面表示アクション
     *
     * @access  public
     */
    function execute()
    {

        if($this->questionnaire['answer_show_flag']==_ON && $this->session->getParameter('_auth_id')<_AUTH_CHIEF) {
            return 'error';
        }
    	$this->questions = $this->questionnaireView->getAnswer($this->summary_id);

		if (empty($this->questions)) {
			return "error";
		}

		if (!empty($this->prefix_id_name)) {
			$this->answerSummaryShow = true;
		}

		$this->answerItemShow = true;

		if ($this->session->getParameter("questionnaire_edit". $this->block_id) == _ON) {
			$this->questionaryAnswerLinkShow = true;
		}

		return "success";
    }
}
?>
