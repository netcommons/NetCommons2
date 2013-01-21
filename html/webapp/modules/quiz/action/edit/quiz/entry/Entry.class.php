<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Action_Edit_Quiz_Entry extends Action
{
    // リクエストパラメータを受け取るため
	var $quiz_id = null;
	
	// 使用コンポーネントを受け取るため
    var $quizAction = null;

    /**
     * 小テスト登録アクション
     *
     * @access  public
     */
    function execute()
    {
        if (!$this->quizAction->setQuiz()) {
        	return "error";
        }

		if (empty($this->quiz_id)) {
			return "create";
		}
		
		return "modify";
    }
}
?>
