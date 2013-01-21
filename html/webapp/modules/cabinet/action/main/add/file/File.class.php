<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルアップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Action_Main_Add_File extends Action
{
	// 使用コンポーネントを受け取るため
 	var $cabinetAction = null;
 	var $uploadsAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->cabinetAction->setFile();
    	if ($result === false) {
    		$this->uploadsAction->delUploadsById($this->filelist[0]["upload_id"]);
    		return 'error';
    	}
		return 'success';
    }
}
?>