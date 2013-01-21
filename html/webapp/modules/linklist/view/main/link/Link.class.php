<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク追加画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Main_Link extends Action
{
    // 使用コンポーネントを受け取るため
    var $linklistView = null;

    // validatorから受け取るため
    var $linklist = null;

    // 値をセットするため
    var $categories = null;

    /**
     * リンク追加画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
        $this->categories = $this->linklistView->getCategories();
        if ($this->categories === false) {
        	return "error";
        }

        return "success";
    }
}
?>
