<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 移動ポップアップを表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Move extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $file_id = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;

    // validatorから受け取るため
    var $cabinet = null;
    var $file = null;

	// 値をセットするため
	var $folder_list = null;
	var $err_address = null;
	var $current_parent = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->folder_list = $this->cabinetView->getFolders();
		if($this->folder_list === false) {
			return 'error';
		}
    	
    	$this->err_address = $this->cabinetView->getMoveErrFolder($this->file_id);
		if($this->err_address === false) {
			return 'error';
		}
		$this->err_address[] = $this->file["parent_id"];
		$this->err_address[] = $this->file["file_id"];

		$this->current_parent = array("0");
		return 'success';
    }
}
?>
