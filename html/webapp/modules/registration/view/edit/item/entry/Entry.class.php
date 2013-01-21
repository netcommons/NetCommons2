<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_View_Edit_Item_Entry extends Action
{
    // validatorから受け取るため
    var $registration = null;
    var $item = null;

	// 値をセットするため
    var $optionNumber = null;
        
    /**
     * 項目入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->optionNumber = count($this->item["option_values"]);
		$this->optionNumber++;
		
		return "success";
    }
}
?>
