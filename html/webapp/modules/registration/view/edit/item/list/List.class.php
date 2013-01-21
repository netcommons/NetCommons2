<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_View_Edit_Item_List extends Action
{
	// 使用コンポーネントを受け取るため
    var $registrationView = null;

    // validatorから受け取るため
    var $registration = null;
    var $items = null;

    /**
     * 項目一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		return "success";
    }
}
?>