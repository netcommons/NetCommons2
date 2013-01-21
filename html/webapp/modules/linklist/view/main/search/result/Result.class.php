<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索結果表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Main_Search_Result extends Action
{
    // 使用コンポーネントを受け取るため
    var $linklistView = null;

    // validatorから受け取るため
    var $linklist = null;

    // 値をセットするため
    var $categoryLinks = null;

    /**
     * 検索結果表示アクション
     *
     * @access  public
     */
    function execute()
    {
        $this->categoryLinks = $this->linklistView->getCategoryLinks();
        if ($this->categoryLinks === false) {
        	return "error";
        }

        return "success";
    }
}
?>
