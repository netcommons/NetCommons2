<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Addmetadata extends Action
{
	// リクエストパラメータを受け取るため
	var $multidatabase_id = null;
	var $metadata_id = null;
	var $name = null;
	var $type = null;

	var $title_metadata_flag = null;
	var $require_flag = null;
	var $list_flag = null;
	var $detail_flag = null;
	var $search_flag = null;
	var $name_flag = null;
	var $sort_flag = null;
	var $file_password_flag = null;
	var $file_count_flag = null;

	var $options = null;

	// 使用コンポーネントを受け取るため
	var $db = null;

	// バリデートによりセットするため
	var $metadata = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$metadata_id = intval($this->metadata_id);
		$set_options = "";
		if(isset($this->options) 
			&& ($this->type == MULTIDATABASE_META_TYPE_SECTION 
				|| $this->type == MULTIDATABASE_META_TYPE_MULTIPLE)) {
			foreach($this->options as $key => $options) {
				$set_options .= $options."|";
			}
			$set_options = substr($set_options, 0, -1);
		}
		if($this->title_metadata_flag == _ON) {
			$this->require_flag = _ON;
		}
		if($this->list_flag != _ON) {
			$this->sort_flag = _OFF;
		}
		if($this->type != MULTIDATABASE_META_TYPE_FILE) {
			$this->file_password_flag = _OFF;
			$this->file_count_flag = _OFF;
		}
		if($this->type == MULTIDATABASE_META_TYPE_AUTONUM || $this->type == MULTIDATABASE_META_TYPE_INSERT_TIME || $this->type == MULTIDATABASE_META_TYPE_UPDATE_TIME) {
			$this->require_flag = _OFF;
		}
		if ($this->type == MULTIDATABASE_META_TYPE_IMAGE) {
			$this->name_flag = _OFF;
		}
		if($this->type == MULTIDATABASE_META_TYPE_FILE || $this->type == MULTIDATABASE_META_TYPE_IMAGE ||
				$this->type == MULTIDATABASE_META_TYPE_DATE || $this->type == MULTIDATABASE_META_TYPE_INSERT_TIME || $this->type == MULTIDATABASE_META_TYPE_UPDATE_TIME) {
			$this->search_flag = _OFF;
		}

		$beforeType = null;
		$afterType = $this->type;
		if (!empty($metadata_id)) {
			// 編集
			$where_params = array("metadata_id" => $metadata_id);
			$metadata_before_update = $this->db->selectExecute("multidatabase_metadata", $where_params);
			if($metadata_before_update === false || !isset($metadata_before_update[0])) {
				return 'error';
			}
			$beforeType = $metadata_before_update[0]['type'];

			if ($beforeType != MULTIDATABASE_META_TYPE_WYSIWYG
					&& $afterType == MULTIDATABASE_META_TYPE_WYSIWYG) {
				$sql = "SELECT metadata_content_id, "
							. "content "
						. "FROM {multidatabase_metadata_content} "
						. "WHERE metadata_id = ?";
				$result = $this->db->execute($sql, $where_params, null, null, true, array($this, '_escapeHtml'));
				if ($result === false) {
					return 'error';
				}
			}

			if ($beforeType != MULTIDATABASE_META_TYPE_DATE
					&& $afterType == MULTIDATABASE_META_TYPE_DATE) {
				$param = array("content" => "");
				$result = $this->db->updateExecute('multidatabase_metadata_content', $param, $where_params, true);
				if ($result === false) {
					return 'error';
				}
			}

			$param = array(
				"name" => $this->name,
				"type" => $this->type,
				"select_content" => $set_options,
				"require_flag" => intval($this->require_flag),
				"list_flag" => intval($this->list_flag),
				"detail_flag" => intval($this->detail_flag),
				"search_flag" => intval($this->search_flag),
				"name_flag" => intval($this->name_flag),
				"sort_flag" => intval($this->sort_flag),
				"file_password_flag" => intval($this->file_password_flag),
				"file_count_flag" => intval($this->file_count_flag)
			);
			//更新
			$result = $this->db->updateExecute("multidatabase_metadata", $param, $where_params, true);
			if ($result === false) {
				return 'error';
			}
		} else {
			$display_sequence = $this->db->maxExecute("multidatabase_metadata", "display_sequence", array("multidatabase_id"=>intval($this->multidatabase_id)));
			$param = array(
				"multidatabase_id" => $this->multidatabase_id,
				"name" => $this->name,
				"type" => $this->type,
				"display_pos" => MULTIDATABASE_DEFAULT_DISPLAY_POSITION,
				"select_content" => $set_options,
				"require_flag" => intval($this->require_flag),
				"list_flag" => intval($this->list_flag),
				"detail_flag" => intval($this->detail_flag),
				"search_flag" => intval($this->search_flag),
				"name_flag" => intval($this->name_flag),
				"sort_flag" => intval($this->sort_flag),
				"file_password_flag" => intval($this->file_password_flag),
				"file_count_flag" => intval($this->file_count_flag),
				"display_sequence" => $display_sequence + 1
			);
			// 追加
			$metadata_id = $this->db->insertExecute("multidatabase_metadata", $param, true, "metadata_id");
			if ($metadata_id === false) {
				return 'error';
			}
		}

		if ($beforeType != MULTIDATABASE_META_TYPE_AUTONUM
				&& $afterType == MULTIDATABASE_META_TYPE_AUTONUM) {
			$sql = "SELECT MC.content_id AS content_id, ".$metadata_id." AS metadata_id, MMC.metadata_content_id AS metadata_content_id, MC.temporary_flag, MC.agree_flag " .
					" FROM {multidatabase_content} MC" .
					" LEFT JOIN {multidatabase_metadata_content} MMC" .
						" ON (MC.content_id = MMC.content_id AND MMC.metadata_id = ?)" .
					" WHERE MC.multidatabase_id = ?".
					" ORDER BY MC.insert_time";

			$whereParams = array(
				'metadata_id' => $metadata_id,
				'multidatabase_id' => $this->multidatabase_id,
			);
			$callbackFunc = array($this, "_fetchCallback");
			$result = $this->db->execute($sql, $whereParams, null, null, true, $callbackFunc);
			if ($result === false) {
				return 'error';
			}
		}

		if($this->title_metadata_flag == _ON) {
			$update_params = array(
				"title_metadata_id" => $metadata_id
			);
			$result = $this->db->updateExecute("multidatabase", $update_params, array("multidatabase_id" => $this->multidatabase_id), true);
			if ($result === false) {
				return 'error';
			}
		}
		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function _fetchCallback(&$recordSet)
	{
		$i = 0;
		while ($row = $recordSet->fetchRow()) {
			if ($row["temporary_flag"] == MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE) {
				$param = array(
					"metadata_id" => $row["metadata_id"],
					"content_id" => $row["content_id"],
					"content" => ""
				);
			} else {
				$i++;
				$param = array(
					"metadata_id" => $row["metadata_id"],
					"content_id" => $row["content_id"],
					"content" => sprintf(MULTIDATABASE_META_AUTONUM_FORMAT, $i)
				);
			}

			if (!empty($row["metadata_content_id"])) {
				//更新
				$whereParams = array(
					'metadata_content_id' => $row["metadata_content_id"]
				);
				$result = $this->db->updateExecute("multidatabase_metadata_content", $param, $whereParams, true);
				if ($result === false) {
					return false;
				}
			} else {
				// 追加
				$result = $this->db->insertExecute("multidatabase_metadata_content", $param, true, "metadata_content_id");
				if ($result === false) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 既存コンテンツをHTMLエスケープする
	 *
	 * @param array $recordSet コンテンツデータADORecordSet
	 * @return boolean true or false
	 * @access public
	 */
	function _escapeHtml(&$recordSet)
	{
		$sql = "UPDATE {multidatabase_metadata_content} "
				. "SET "
				. "content = ? "
				. "WHERE metadata_content_id = ?";

		while ($row = $recordSet->fetchRow()) {
			$bindValues = array(
					htmlspecialchars($row['content']),
					$row['metadata_content_id']
				);
			$result = $this->db->execute($sql, $bindValues);
			if ($result === false) {
				return false;
			}
		}

		return true;
	}
}
?>
