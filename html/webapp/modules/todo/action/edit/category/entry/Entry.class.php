<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Edit_Category_Entry extends Action
{
	// パラメータを受け取るため
    var $todo_id = null;
    var $category_id = null;
    var $category_name = null;

	// 使用コンポーネントを受け取るため
    var $db = null;

	/**
     * カテゴリ登録アクション
     *
     * @access  public
     */
    function execute()
	{
		if (empty($this->category_id)) {
			$params = array(
				$this->todo_id
			);
			$sql = "SELECT MAX(category_id) AS max_category_id,  MAX(display_sequence) AS max_display_sequence".
					" FROM {todo_category}".
					" WHERE todo_id = ?";
			$sequences = $this->db->execute($sql, $params, null, null);
			if ($sequences === false) {
				$this->db->addError();
				return 'error';
			}

			$params = array(
				"category_id" => $sequences[0]["max_category_id"] + 1,
				"todo_id" => $this->todo_id,
				"category_name" => $this->category_name,
				"display_sequence" => $sequences[0]["max_display_sequence"] + 1
			);
			$result = $this->db->insertExecute("todo_category", $params, true);
			if (!$result) {
				return 'error';
			}
		} else {
			$params = array(
				"category_name" => $this->category_name
			);
			$whereParams = array(
				"todo_id" => $this->todo_id,
				"category_id" => $this->category_id
			);
			$result = $this->db->updateExecute("todo_category", $params, $whereParams, true);
			if (!$result) {
				return 'error';
			}
		}
		return 'success';
	}
}
?>