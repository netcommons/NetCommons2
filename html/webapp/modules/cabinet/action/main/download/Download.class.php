<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ダウンロード
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Action_Main_Download extends Action
{
    // リクエストパラメータを受け取るため
	var $file_id = null;

    // 使用コンポーネントを受け取るため
	var $cabinetAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->cabinetAction->setDownload($this->file_id);
    	return 'success';
    }
}
?>