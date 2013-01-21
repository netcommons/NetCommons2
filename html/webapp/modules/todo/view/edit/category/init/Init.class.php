<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Todo_View_Edit_Category_Init extends Action
{
	// パラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
    var $todoView = null;
    var $session = null;

    // validatorから受け取るため
    var $todo = null;

    // 値をセットするため
    var $todo_id = 0;
    var $categories = array();

	/**
	 * カテゴリ画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->todo_id = $this->todo["todo_id"];

		$_id = $this->session->getParameter("_id");
	 	$this->session->setParameter(array("todo", "_id", $this->block_id), $_id);

		$this->categories = $this->todoView->getCategories($this->todo_id);
		if (empty($this->categories)) {
			return 'error';
		}
		return 'success';
	}
}
?>