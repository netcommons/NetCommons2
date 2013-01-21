<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Action_Edit_Addblock extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
	var $module_id = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $whatsnewView = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$default = $this->whatsnewView->getDefaultBlock($this->module_id);
    	$result = $this->db->insertExecute("whatsnew_block", array_merge($default, array("block_id"=>$this->block_id)), true);
    	if ($result === false) {
    		return 'error';
    	}
        return 'success';
    }
}
?>