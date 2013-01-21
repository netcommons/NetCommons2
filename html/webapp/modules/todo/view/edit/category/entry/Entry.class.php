<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_View_Edit_Category_Entry extends Action
{
	// パラメータを受け取るため
    var $todo_id = null;
    var $category_id = null;

	// 使用コンポーネントを受け取るため
    var $db = null;

    // 値をセットするため
    var $category = array();

	/**
     * Todo入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!empty($this->category_id)) {
			$params = array(
				$this->todo_id,
				$this->category_id
			);

			$sql = "SELECT category_id, category_name ".
					" FROM {todo_category} ".
					" WHERE todo_id = ? ".
					" AND category_id = ?";

			$result = $this->db->execute($sql, $params);
			if ($result === false) {
				$this->db->addError();
				return false;
			}
			$this->category_name = $result[0]["category_name"];
    	}

		return 'success';
    }
}
?>
