<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Edit_List extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
	var $scroll = null;
	var $photoalbum_id = null;

    // 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    var $configView = null;
    var $request = null;
    var $filterChain = null;

	// validatorから受け取るため
	var $photoalbumCount = null;
	
	// 値をセットするため
    var $visibleRows = null;
    var $photoalbums = null;
    var $currentPhotoalbumID = null;

    /**
     * フォトアルバム一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if ($this->scroll != _ON) {
			$config = $this->configView->getConfigByConfname($this->module_id, "photoalbum_list_row_count");
			if ($config === false) {
	        	return "error";
	        }
	        
	        $this->visibleRows = $config["conf_value"];
	        $this->request->setParameter("limit", $this->visibleRows);

			$this->currentPhotoalbumID = $this->photoalbumView->getCurrentPhotoalbumID();
	        if ($this->currentPhotoalbumID === false) {
	        	return "error";
	        }
		}
		
		$this->photoalbums = $this->photoalbumView->getPhotoalbums();
		if (empty($this->photoalbums)) {
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
