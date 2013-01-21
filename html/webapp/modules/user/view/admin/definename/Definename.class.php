<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定>項目追加(項目編集)>PHP定義名称を取得する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Definename extends Action
{
    // リクエストパラメータを受け取るため
    var $item_name = null;

    // 値をセットするため
    var $def_item_name = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$this->def_item_name = "";
    	if(defined($this->item_name)) $this->def_item_name = constant($this->item_name);
    	return 'success';
    }
}
?>
