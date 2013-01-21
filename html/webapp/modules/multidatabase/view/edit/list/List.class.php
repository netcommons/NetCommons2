<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Edit_List extends Action
{
    // パラメータを受け取るため
    var $module_id = null;
	var $block_id = null;
	var $scroll = null;

    // 使用コンポーネントを受け取るため
    var $mdbView = null;
    var $configView = null;
    var $request = null;
    var $session = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $mdbCount = null;

    // 値をセットするため
    var $visibleRows = null;
    var $mdb_list = null;
    var $currentMdbId = null;

    /**
     * 汎用データベース一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
        if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "multidatabase_list_row_count");
			if ($config === false) {
	        	return 'error';
	        }
	        
	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);
	        
	        $this->currentMdbId = $this->mdbView->getCurrentMdbId();
	        if ($this->currentMdbId === false) {
	        	return 'error';
	        }
		}
		
		$this->mdb_list = $this->mdbView->getMdbs();
        if (empty($this->mdb_list)) {
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
