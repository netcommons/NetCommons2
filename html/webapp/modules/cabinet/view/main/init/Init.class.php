<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネットの表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $folder_id = null;
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;
    var $session = null;

    // validatorから受け取るため
    var $cabinet = null;
    var $reference = null;

	// 値をセットするため
	var $folder_list = null;
	var $fileCount = null;
	var $current_parent = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$_id = $this->session->getParameter("_id");
    	$this->session->setParameter(array("cabinet", "_id", $this->block_id), $_id);
    	
    	$this->folder_list = $this->cabinetView->getFolders();
		if($this->folder_list === false) {
			return 'error';
		}

		$this->fileCount = $this->cabinetView->getFileCount();
		if($this->fileCount === false) {
			return 'error';
		}
		if ($this->folder_id != "0") {
			$this->fileCount++;
		}
		$this->current_parent = array("0");
        return 'success';
    }
}
?>
