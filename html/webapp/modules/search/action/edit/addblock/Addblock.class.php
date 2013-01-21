<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール追加時に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Action_Edit_Addblock extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $page_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $session = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		$headercolumn_page_id = $this->session->getParameter("_headercolumn_page_id");
		$leftcolumn_page_id = $this->session->getParameter("_leftcolumn_page_id");
		$rightcolumn_page_id = $this->session->getParameter("_rightcolumn_page_id");

		// 左右、ヘッダーカラムの場合、詳細リンクを非表示
 		// それ以外は、詳細リンクを表示する
 		// (簡易検索で検索したとき、無条件で詳細フォームを表示しているため、未使用)
/*
		if ($headercolumn_page_id == $this->page_id || 
			$leftcolumn_page_id == $this->page_id || 
			$rightcolumn_page_id == $this->page_id) {
			$detail_flag = _OFF;
		} else {
			$detail_flag = _ON;
		}
*/
		$params = array(
    		"block_id" => intval($this->block_id),
			"show_mode" => SEARCH_SHOW_MODE_NORMAL,
			"default_target_module" => SEARCH_DEFAULT_MODULES,
			"detail_flag" => _OFF
		);
    	$result = $this->db->insertExecute("search_blocks", $params, true);
    	if ($result === false) {
    		return 'error';
    	}
		return 'success';
	}
}
?>