<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 画像アップロードアクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Main_Upload_Image extends Action
{
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
	
	/**
     * 画像アップロードアクション
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
