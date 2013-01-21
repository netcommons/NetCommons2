<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タスク順序変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_Action_Edit_Category_Sequence extends Action
{
	// パラメータを受け取るため
    var $todo_id = null;
    var $drag_category_id = null;
    var $drop_category_id = null;
    var $position = null;

    // 使用コンポーネントを受け取るため
	var $db = null;

	/**
     * タスク順序変更アクション
     *
     * @access  public
     */
    function execute()
    {
		$params = array(
			$this->todo_id,
			$this->drag_category_id,
			$this->drop_category_id
		);

		$sql = "SELECT category_id, display_sequence ".
				"FROM {todo_category} ".
				"WHERE todo_id = ? ".
				"AND (category_id = ? OR category_id = ?) ";
		$result = $this->db->execute($sql, $params);
		if ($result === false || count($result) != 2) {
			$this->db->addError();
			return 'error';
		}

		$sequences[$result[0]["category_id"]] = $result[0]["display_sequence"];
		$sequences[$result[1]["category_id"]] = $result[1]["display_sequence"];

        //移動元デクリメント(前詰め処理)
    	$params = array(
			"todo_id" => $this->todo_id
    	);
		$sequence_param = array(
			"display_sequence" => $sequences[$this->drag_category_id]
		);
    	$result = $this->db->seqExecute("todo_category", $params, $sequence_param);
    	if ($result === false) {
			return 'error';
       	}

    	if ($sequences[$this->drag_category_id] > $sequences[$this->drop_category_id]) {
	        if ($this->position == "top") {
	        	$drop_sequence = $sequences[$this->drop_category_id];
	        } else {
	        	$drop_sequence = $sequences[$this->drop_category_id] + 1;
	        }
	    } else {
	    	if ($this->position == "top") {
	        	$drop_sequence = $sequences[$this->drop_category_id] - 1;
	        } else {
	        	$drop_sequence = $sequences[$this->drop_category_id];
	        }
	    }

	    //移動先インクリメント
    	$params = array(
			"todo_id" => $this->todo_id
    	);
		$sequence_param = array(
			"display_sequence" => $drop_sequence
		);
    	$result = $this->db->seqExecute("todo_category", $params, $sequence_param, 1);
    	if ($result === false) {
			return 'error';
    	}

        //更新
    	$params = array(
			"display_sequence" => $drop_sequence
		);
		$where_params = array(
			"todo_id" => $this->todo_id,
			"category_id" => $this->drag_category_id
		);
    	$result = $this->db->updateExecute("todo_category", $params, $where_params, false);
    	if ($result === false) {
			return 'error';
    	}

    	return 'success';
    }
}
?>