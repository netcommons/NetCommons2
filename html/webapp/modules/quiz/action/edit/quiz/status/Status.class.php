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
class Quiz_Action_Edit_Quiz_Status extends Action
{
	// リクエストパラメータを受け取るため
	var $quiz_id = null;

    // 使用コンポーネントを受け取るため
    var $quizAction = null;

    /**
     * 状態フラグ更新アクション
     *
     * @access  public
     */
    function execute()
    {
		$params = array(
			"quiz_id" => $this->quiz_id,
			"status" => QUIZ_STATUS_END_VALUE
		);
		if (!$this->quizAction->updateQuiz($params)) {
        	return "error";
        }

		return "success";
    }
}
?>
