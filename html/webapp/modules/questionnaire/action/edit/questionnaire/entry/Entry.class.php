<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Action_Edit_Questionnaire_Entry extends Action
{
    // リクエストパラメータを受け取るため
	var $questionnaire_id = null;
	
	// 使用コンポーネントを受け取るため
    var $questionnaireAction = null;

    /**
     * アンケート登録アクション
     *
     * @access  public
     */
    function execute()
    {
        if (!$this->questionnaireAction->setQuestionnaire()) {
        	return "error";
        }

		if (empty($this->questionnaire_id)) {
			return "create";
		}
		
		return "modify";
    }
}
?>
