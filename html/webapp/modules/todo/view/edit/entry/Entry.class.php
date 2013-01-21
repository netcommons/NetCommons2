<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Todo入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_View_Edit_Entry extends Action
{
	// 使用コンポーネントを受け取るため
    var $todoView = null;

    // validatorから受け取るため
    var $todo = null;

    // 値をセットするため
    var $todoNumber = null;

    /**
     * Todo入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->todo["todo_id"])) {
			return "success";
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->todoNumber = $this->todoView->getTodoCount();
		if ($this->todoNumber === false) {
        	return "error";
        }
        $this->todoNumber++;
        
		return "success";
    }
}
?>
