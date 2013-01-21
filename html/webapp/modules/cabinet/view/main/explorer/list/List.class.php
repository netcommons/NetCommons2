<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイル一覧(Grid)を表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Explorer_List extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $folder_id = null;
    var $limit = null;
    var $offset = null;
    var $success = null;
    var $sort_col = null;
    var $sort_dir = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;

    // validatorから受け取るため
    var $cabinet = null;
    var $reference = null;
	var $folder_parent_id = null;

	// 値をセットするため
	var $file_list = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->limit = intval($this->limit);
    	$this->offset = intval($this->offset);
    	
    	$this->file_list = $this->cabinetView->getFileList($this->offset, $this->limit);
		if($this->file_list === false) {
			return 'error';
		}
		if ($this->success == "html") {
			return 'success_html';
		} else {
			return 'success_xml';
		}
    }
}
?>
