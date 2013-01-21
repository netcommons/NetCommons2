<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アドレスバーの変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Main_SwitchAddress extends Action
{
	// リクエストパラメータを受け取るため
	var $address = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;

    // 値をセット
	var $folder_id = null;

/*
	// リクエストパラメータを受け取るため
	var $cabinet_id = null;
	var $address = null;
	var $cabinet_obj = null;

    // 使用コンポーネントを受け取るため
    var $cabinetView = null;

    // 値をセット
	var $folder_ids = null;
 */

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->folder_id = $this->cabinetView->switchFolder();
		return 'success';
    }
}
?>
