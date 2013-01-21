<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_View_Main_Center extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $request = null;

	// Filterにより値をセット
	
	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
 		$this->request->setParameter("show_mode", SEARCH_SHOW_MODE_NORMAL);
 		$this->request->setParameter("center_flag", _OFF);
 		$this->request->setParameter("return_center", _ON);
		return 'success';
	}
}
?>