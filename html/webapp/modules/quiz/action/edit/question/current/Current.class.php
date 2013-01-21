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
class Quiz_Action_Edit_Question_Current extends Action
{
	// リクエストパラメータを受け取るため
	var $quiz_id = null;

    // 使用コンポーネントを受け取るため
    var $quizAction = null;
    var $quizView = null;

	// validatorから受け取るため
    var $quiz = null;



    /**
     * カレント・ステータス更新アクションクラス
     *
     * @access  public
     */
    function execute()
    {
		$questions = $this->quizView->getQuestions();
		if ($this->quiz["status"] == QUIZ_STATUS_INACTIVE_VALUE
				&& !empty($questions)) {
	    	$params = array(
				"quiz_id" => $this->quiz_id,
				"status" => QUIZ_STATUS_ACTIVE_VALUE
			);
			if (!$this->quizAction->updateQuiz($params)) {
	        	return "error";
	        }
		}

		if (!$this->quizAction->setBlock()) {
        	return "error";
        }

        return "success";
    }
}
?>
