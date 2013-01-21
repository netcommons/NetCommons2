<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Main_Comment extends Action
{
	// validatorから受け取るため
	var $assignment = null;
	var $report = null;
	var $submit_id = null;
	var $submit_user_id = null;
	var $commentCount = null;
	var $comments = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
        return "success";
    }
}
?>
