<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_View_Edit_Quiz_Entry extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;

	// 使用コンポーネントを受け取るため
    var $quizView = null;
    var $session = null;
    
    // validatorから受け取るため
    var $quiz = null;

	// 値をセットするため
    var $oldQuizzes = array();
    var $quizNumber = null;

    /**
     * 小テスト入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->quiz["quiz_id"])) {
			return "success";
		}

		$this->session->setParameter("quiz_edit". $this->block_id, _ON);
		
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->oldQuizzes = $this->quizView->getOldQuizzes();
		if ($this->oldQuizzes === false) {
        	return "error";
        }
		
		$this->quizNumber = $this->quizView->getQuizCount();
		if ($this->quizNumber === false) {
        	return "error";
        }
        $this->quizNumber++;
        
		return "success";
    }
}
?>