<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイル添付アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Main_Upload_Attachment extends Action
{
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
	
	/**
     * ファイル添付アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->uploadsAction->uploads();
    	
    	return true;
    }
}
?>
