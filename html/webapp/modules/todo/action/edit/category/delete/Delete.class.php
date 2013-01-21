<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Edit_Category_Delete extends Action
{
	// パラメータを受け取るため
    var $todo_id = null;
    var $category_id = null;

	// 使用コンポーネントを受け取るため
    var $db = null;

    /**
     * カテゴリ削除アクションクラス
     *
     * @access  public
     */
    function execute()
    {
		$whereParams = array(
			"todo_id" => $this->todo_id,
			"category_id" => $this->category_id
		);
		$setParams = array(
			"category_id" => 0
		);

		$result = $this->db->updateExecute("todo_task", $setParams, $whereParams, false);
    	if (!$result) {
			return 'error';
    	}

    	$sql = "SELECT display_sequence ".
				" FROM {todo_category} ".
				" WHERE todo_id = ?".
				" AND category_id = ?";
		$sequences = $this->db->execute($sql, $whereParams, 1, null, false);
		if ($sequences === false) {
			$this->db->addError();
			return 'error';
		}
		$sequence = $sequences[0][0];

		$result = $this->db->deleteExecute("todo_category", $whereParams);
    	if (!$result) {
			return 'error';
    	}

		$whereParams = array(
			"todo_id" => $this->todo_id
		);
		$sequenceParam = array(
			"display_sequence" => $sequence
		);
		if (!$this->db->seqExecute("todo_category", $whereParams, $sequenceParam)) {
			return 'error';
		}

    	return "success";
    }
}
?>
