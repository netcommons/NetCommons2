<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリストメイン画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $entry = null;
    var $prefix_id_name = null;

    // 使用コンポーネントを受け取るため
    var $linklistView = null;

    // validatorから受け取るため
    var $linklist = null;

    // 値をセットするため
    var $categoryLinks = null;
    var $linkExists = null;
    var $headerShow = null;

    /**
     * リンクリストメイン画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->categoryLinks = $this->linklistView->getCategoryLinks();
		if ($this->categoryLinks === false) {
			return "error";
		}
    	$this->linkExists = !empty($this->categoryLinks);

		if (strpos($this->prefix_id_name, LINKLIST_PREFIX_REFERENCE) === 0) {
			$this->headerShow = false;
		} else {
			$this->headerShow = true;
		}

        if ($this->entry == _ON) {
        	return "entry";
        }

        return "view";
    }
}
?>
