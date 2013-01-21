<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 右列の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Explorer_Right extends Action
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
	var $fileCount = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->fileCount = $this->cabinetView->getFileCount();
		if($this->fileCount === false) {
			return 'error';
		}
		if ($this->folder_id != "0") {
			$this->fileCount++;
		}
        return 'success';
    }
}
?>
