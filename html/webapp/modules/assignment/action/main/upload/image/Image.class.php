<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 画像アップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Main_Upload_Image extends Action
{
	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;

	/**
     * アップロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	$filelist = $this->uploadsAction->uploads();
    	
    	return true;
    }
}
?>
