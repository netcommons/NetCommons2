<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員管理(モジュール別個人情報)-解答結果一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_View_Admin_Personalinf extends Action
{
    // validatorから受け取るため
	var $personalAssignments = null;
	
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
