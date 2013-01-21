<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 掲示板入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Edit_Entry extends Action
{
	// 使用コンポーネントを受け取るため
    var $bbsView = null;

    // validatorから受け取るため
    var $bbs = null;

    // 値をセットするため
    var $bbsNumber = null;

    /**
     * 掲示板力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->bbs["bbs_id"])) {
			return "success";
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->bbsNumber = $this->bbsView->getBbsCount();
		if ($this->bbsNumber === false) {
        	return "error";
        }
        $this->bbsNumber++;
        
		return "success";
    }
}
?>
