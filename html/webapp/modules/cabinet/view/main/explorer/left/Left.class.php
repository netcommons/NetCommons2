<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 左列の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Explorer_Left extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $folder_id = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;

    // validatorから受け取るため
    var $cabinet = null;
    var $reference = null;

	// 値をセットするため
	var $folder_list = null;
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
		$this->current_parent = $this->cabinetView->getCurrentParentFolder($this->folder_id);
		if($this->current_parent === false) {
			return 'error';
		}
        return 'success';
    }
}
?>
