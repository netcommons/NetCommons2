<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 採点アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Action_Edit_Mark extends Action
{
	// 使用コンポーネントを受け取るため
    var $quizAction = null;
    var $quizView = null;

    // validatorから受け取るため
	var $scores = null;
	var $answerFlags = null;
	var $summaryIDs = null;

    /**
     * 採点アクション
     *
     * @access  public
     */
    function execute()
    {
		$summaryScore = 0;
		foreach (array_keys($this->scores) as $answer_id) {
			$params["answer_id"] = $answer_id;
	        $params["answer_flag"] = $this->answerFlags[$answer_id];
	        $params["score"] = $this->scores[$answer_id];

	        if (!$this->quizAction->updateAnswer($params)) {
	        	return "error";
	        }
		}

        foreach ($this->summaryIDs as $summaryID) {
	        if (!$this->quizAction->updateSummary($summaryID)) {
	        	return "error";
	        }
        }

		if (!$this->quizAction->updateQuizAnswer(false)) {
        	return "error";
        }
		
		return "success";
    }
}
?>
