<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着の既読
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Action_Main_Read extends Action
{
    // 使用コンポーネントを受け取るため
	var $whatsnewModAction = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->whatsnewModAction->setRead();
    	if (!$result) {
    		return 'error';
    	}
		return 'success';
    }
}
?>