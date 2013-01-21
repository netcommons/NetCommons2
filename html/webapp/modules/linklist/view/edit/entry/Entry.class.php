<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Edit_Entry extends Action
{
	// 使用コンポーネントを受け取るため
    var $linklistView = null;

    // validatorから受け取るため
    var $linklist = null;

    // 値をセットするため
    var $linklistNumber = null;

    /**
     * リンクリスト入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->linklist["linklist_id"])) {
			return "success";
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->linklistNumber = $this->linklistView->getLinklistCount();
		if ($this->linklistNumber === false) {
        	return "error";
        }
        $this->linklistNumber++;
        
		return "success";
    }
}
?>
