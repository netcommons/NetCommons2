<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 編集画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Main_Grade extends Action
{
	// validatorから受け取るため
	var $assignment = null;
	var $submitter = null;
	var $submit_id = null;
	var $submit_user_id = null;
    var $gradeValues = null;

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
