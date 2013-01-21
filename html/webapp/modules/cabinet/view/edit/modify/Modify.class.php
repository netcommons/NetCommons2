<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネット編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Edit_Modify extends Action
{
    // パラメータを受け取るため
    var $page_id = null;

    // 使用コンポーネントを受け取るため
	var $cabinetView = null;
	var $getdata = null;

    // validatorから受け取るため
    var $cabinet = null;

	// 値をセットするため
	var $private_flag = null;
	var $maxsize_list = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$pages = $this->getdata->getParameter("pages");
		$this->private_flag = $pages[$this->page_id]["private_flag"];

		$this->maxsize_list = $this->cabinetView->getSizeList(); 

   		return 'success';
    }
}
?>