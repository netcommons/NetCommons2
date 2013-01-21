<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_View_Edit_Questionnaire_Entry extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;

	// 使用コンポーネントを受け取るため
    var $questionnaireView = null;
    var $session = null;
    
    // validatorから受け取るため
    var $questionnaire = null;

	// 値をセットするため
    var $oldQuestionnaires = array();
    var $questionnaireNumber = null;

    /**
     * アンケート入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->questionnaire["questionnaire_id"])) {
			return "success";
		}

		$this->session->setParameter("questionnaire_edit". $this->block_id, _ON);
		
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->oldQuestionnaires = $this->questionnaireView->getOldQuestionnaires();
		if ($this->oldQuestionnaires === false) {
        	return "error";
        }
		
		$this->questionnaireNumber = $this->questionnaireView->getQuestionnaireCount();
		if ($this->questionnaireNumber === false) {
        	return "error";
        }
        $this->questionnaireNumber++;
        
		return "success";
    }
}
?>
