<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_View_Edit_Create extends Action
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
     *execute実行
     *
     * @access  public
     */
    function execute()
    {
		$cabNumber = $this->cabinetView->getCabCount();
		$cabNumber++;

		$this->cabinet["cabinet_id"] = 0;
		$this->cabinet["cabinet_name"] = sprintf(CABINET_NEW_NAME, $cabNumber);

		$pages = $this->getdata->getParameter("pages");
		$this->private_flag = $pages[$this->page_id]["private_flag"];
		
		$this->maxsize_list = $this->cabinetView->getSizeList(); 
    	return 'success';
    }
}
?>