<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレント・ステータス更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Action_Edit_Questionnaire_Current extends Action
{
	// リクエストパラメータを受け取るため
	var $questionnaire_id = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireAction = null;
    var $questionnaireView = null;

	// validatorから受け取るため
    var $questionnaire = null;



    /**
     * カレント・ステータス更新アクションクラス
     *
     * @access  public
     */
    function execute()
    {
		$questions = $this->questionnaireView->getQuestions();
		if ($this->questionnaire["status"] == QUESTIONNAIRE_STATUS_INACTIVE_VALUE
				&& !empty($questions)) {
	    	$params = array(
				"questionnaire_id" => $this->questionnaire_id,
				"status" => QUESTIONNAIRE_STATUS_ACTIVE_VALUE
			);
			if (!$this->questionnaireAction->updateQuestionnaire($params)) {
	        	return "error";
	        }
		}

		if (!$this->questionnaireAction->setBlock()) {
        	return "error";
        }

		return "success";
    }
}
?>
