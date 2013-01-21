<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Edit_List extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
	var $block_id = null;
	var $scroll = null;

    // 使用コンポーネントを受け取るため
    var $linklistView = null;
    var $configView = null;
    var $request = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $linklistCount = null;

    // 値をセットするため
    var $visibleRows = null;
    var $linklists = null;
    var $currentLinklistID = null;

    /**
     * リンクリスト一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
        if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "linklist_list_row_count");
			if ($config === false) {
	        	return "error";
	        }
	        
	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);
	        
	        $this->currentLinklistID = $this->linklistView->getCurrentLinklistID();
	        if ($this->currentLinklistID === false) {
	        	return "error";
	        }
		}
		
		$this->linklists = $this->linklistView->getLinklists();
        if (empty($this->linklists)) {
        	return "error";
        }
        
        if ($this->scroll == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", 0);
        	
        	return "scroll";
        }
        
        return "screen";
    }
}
?>
