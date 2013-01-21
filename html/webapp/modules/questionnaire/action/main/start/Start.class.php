<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アンケート開始アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Action_Main_Start extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

    /**
     * アンケート開始アクション
     *
     * @access  public
     */
    function execute()
    {
		return "success";
    }
}
?>
