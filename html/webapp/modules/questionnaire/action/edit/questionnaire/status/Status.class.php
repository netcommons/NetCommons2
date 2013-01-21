<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 状態フラグ更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Action_Edit_Questionnaire_Status extends Action
{
	// リクエストパラメータを受け取るため
	var $questionnaire_id = null;

    // 使用コンポーネントを受け取るため
    var $questionnaireAction = null;

    /**
     * 状態フラグ更新アクション
     *
     * @access  public
     */
    function execute()
    {
		$params = array(
			"questionnaire_id" => $this->questionnaire_id,
			"status" => QUESTIONNAIRE_STATUS_END_VALUE
		);
		if (!$this->questionnaireAction->updateQuestionnaire($params)) {
        	return "error";
        }

		return "success";
    }
}
?>
