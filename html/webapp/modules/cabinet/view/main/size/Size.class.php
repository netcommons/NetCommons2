<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * プロパティのサイズを表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Size extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $file_id = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;
	var $fileView = null;

    // validatorから受け取るため
    var $file = null;

    // 値をセット
	var $base_size = null;
	var $prefix_size = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->file["file_type"] == CABINET_FILETYPE_FOLDER) {
			$this->base_size = $this->cabinetView->getSize($this->file_id);
    	} else {
    		$this->base_size = $this->file["size"];
    	}
		$this->prefix_size = $this->fileView->formatSize($this->base_size);
		return 'success';
    }
}
?>
