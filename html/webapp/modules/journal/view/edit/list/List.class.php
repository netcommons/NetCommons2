<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Edit_List extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
	var $block_id = null;
	var $scroll = null;

    // 使用コンポーネントを受け取るため
    var $journalView = null;
    var $configView = null;
    var $request = null;
    var $session = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $journal_count = null;

    // 値をセットするため
    var $visible_rows = null;
    var $journal_list = null;
	var $current_journal_id = null;
	
    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "journal_list_row_count");
			if ($config === false) {
	        	return 'error';
	        }
	        
	        $this->visible_rows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visible_rows);
	        
	        $this->current_journal_id = $this->journalView->getCurrentJournalId();
	        if ($this->current_journal_id === false) {
	        	return 'error';
	        }
		}
		
		$this->journal_list = $this->journalView->getJournals();
        if (empty($this->journal_list)) {
        	return 'error';
        }
        
        if ($this->scroll == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", 0);
        	
        	return 'scroll';
        }
        
        return 'screen';
    }
}
?>
