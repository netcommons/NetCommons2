<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネットの追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Action_Main_Modify extends Action
{
    // リクエストパラメータを受け取るため
	var $file_name = null;

	// 使用コンポーネントを受け取るため
 	var $cabinetAction = null;
 	var $uploadsAction = null;

    // validatorから受け取るため
    var $file = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->cabinetAction->setFile();
    	if ($result === false) {
    		return 'error';
    	}
		if ($this->file["file_type"] == CABINET_FILETYPE_FILE) {
	    	$params = array(
				"file_name" => $this->file_name.".".$this->file["extension"]
			);
	    	$whrer_params = array(
				"upload_id" => $this->file["upload_id"]
			);
			$result = $this->uploadsAction->updUploads($params, $whrer_params);
	    	if ($result === false) {
	    		return 'error';
	    	}
		}
		return 'success';
    }
}
?>