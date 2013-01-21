<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_View_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	
	// 使用コンポーネントを受け取るため
	var $searchView = null;
	var $db = null;
	
	// 値をセットするため
	var $modules = null;
	var $search_blocks = null;
	
    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		$this->search_blocks = $this->searchView->getBlock($this->block_id);
        if ($this->search_blocks === false) {
        	return 'error';
        }
		
		$this->modules = $this->searchView->getModules($this->search_blocks);
        if ($this->modules === false) {
        	return 'error';
        }
        if (count($this->modules) == 0) {
        	return 'noexists';
        }
		return 'success';
	}
}
?>