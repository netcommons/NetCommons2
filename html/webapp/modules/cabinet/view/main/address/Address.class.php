<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アドレスバーを表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_Address extends Action
{
    // リクエストパラメータを受け取るため
    var $cabinet_id = null;
    var $folder_id = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;

    // validatorから受け取るため
    var $cabinet = null;

	// 値をセットするため
	var $folderPath = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$pathArr = $this->cabinetView->getFolderPathName($this->folder_id);
	    $this->folderPath = $this->cabinet["cabinet_name"]."/".implode("/", $pathArr);
		return 'success';
    }
}
?>
