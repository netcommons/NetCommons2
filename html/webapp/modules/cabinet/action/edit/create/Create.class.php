<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Action_Edit_Create extends Action
{
    // 使用コンポーネントを受け取るため
	var $cabinetAction = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->cabinetAction->setCabinet();
    	if ($result === false) {
    		return 'error';
    	}
        return 'success';
    }
}
?>