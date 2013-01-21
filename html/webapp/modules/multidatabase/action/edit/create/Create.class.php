<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Create extends Action
{
    // リクエストパラメータを受け取るため
    var $room_id = null;
	var $module_id = null;
    var $block_id = null;
    var $multidatabase_name = null;
	var $contents_authority = null;
	var $vote_flag = null;
	var $comment_flag = null;
	var $mail_flag = null;
	var $mail_authority = null;
	var $mail_subject = null;
	var $mail_body = null;
	var $new_period = null;
	var $agree_flag = null;
	var $agree_mail_flag = null;
	var $agree_mail_subject = null;
	var $agree_mail_body = null;
	var $old_use = null;
	var $old_multidatabase_id = null;

	// バリデートによりセット
	var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $request = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"multidatabase_name" => $this->multidatabase_name,
			"active_flag" => _ON,
			"contents_authority" => intval($this->contents_authority),
			"vote_flag" => intval($this->vote_flag),
			"comment_flag" => intval($this->comment_flag),
			"mail_flag" => intval($this->mail_flag),
			"mail_authority" => intval($this->mail_authority),
			"mail_subject" => $this->mail_subject,
			"mail_body" => $this->mail_body,
			"new_period" => intval($this->new_period),
			"agree_flag" => intval($this->agree_flag),
			"agree_mail_flag" => intval($this->agree_mail_flag),
			"agree_mail_subject" => $this->agree_mail_subject,
			"agree_mail_body" => $this->agree_mail_body
		);
		$multidatabase_id = $this->db->insertExecute("multidatabase", $params, true, "multidatabase_id");
    	if ($multidatabase_id === false) {
    		return 'error';
    	}

    	if ($this->old_use == _ON) {
			$params = array($this->room_id, $this->old_multidatabase_id);
			$sql = "SELECT title_metadata_id ".
					" FROM {multidatabase} ".
					" WHERE room_id = ? ".
					" AND multidatabase_id = ? ";
			$result = $this->db->execute($sql, $params);
			if ($result == false) {
				$this->db->addError();
				return 'error';
			}
			$title_metadata_id = $result[0]["title_metadata_id"];

			$metadatas =& $this->db->selectExecute("multidatabase_metadata", array("multidatabase_id"=>intval($this->old_multidatabase_id)));
			if($metadatas === false) {
				$this->db->addError();
				return 'error';
			}
    	} else {
			$metadatas = split("/", MULTIDATABASE_METADATA_PATTERN);
			$title_metadata_id = null;
    	}

		foreach ($metadatas as $key => $metadata) {
			if ($this->old_use == _ON) {
				$params = array(
					"multidatabase_id" => $multidatabase_id,
					"name" => $metadata["name"],
					"type" =>$metadata["type"],
					"require_flag" => $metadata["require_flag"],
					"list_flag" => $metadata["list_flag"],
					"detail_flag" => $metadata["detail_flag"],
					"name_flag" => $metadata["name_flag"],
					"search_flag" => $metadata["search_flag"],
					"sort_flag" => $metadata["sort_flag"],
					"file_password_flag" => $metadata["file_password_flag"],
					"file_count_flag" => $metadata["file_count_flag"],
					"display_pos" => $metadata["display_pos"],
					"display_sequence" => $metadata["display_sequence"],
					"select_content" => $metadata["select_content"]
				);
				$old_metadata_id = $metadata["metadata_id"];
			} else {
				$items = split(",", $metadata);
				$params = array(
					"multidatabase_id" => $multidatabase_id,
					"name" => $items[0],
					"type" => $items[1],
					"require_flag" => $items[2],
					"list_flag" => $items[2],
					"detail_flag" => $items[3],
					"name_flag" => $items[4],
					"search_flag" => $items[5],
					"sort_flag" => $items[6],
					"file_password_flag" => $items[1]==MULTIDATABASE_META_TYPE_FILE?$items[9]:0,
					"file_count_flag" => $items[1]==MULTIDATABASE_META_TYPE_FILE?1:0,
					"display_pos" => $items[7],
					"display_sequence" => $items[8],
					"select_content" => $items[1]==MULTIDATABASE_META_TYPE_SECTION?$items[9]:""
				);
				$old_metadata_id = -1;
			}

			$metadata_id = $this->db->insertExecute("multidatabase_metadata", $params, true, "metadata_id");
			if ($metadata_id === false) {
	    		return 'error';
	    	}
	    	if(!isset($title_metadata_id) && $key == 0 || $title_metadata_id == $old_metadata_id) {
	    		$update_params = array(
	    			"title_metadata_id" => $metadata_id
	    		);
	    		$result = $this->db->updateExecute("multidatabase", $update_params, array("multidatabase_id" => $multidatabase_id));
	    		if ($result === false) {
		    		return 'error';
		    	}
	    	}
		}


		$count = $this->db->countExecute("multidatabase_block", array("block_id"=>$this->block_id));
    	if($count === false) {
    		return 'error';
    	}

    	$params = array(
			"multidatabase_id" => $multidatabase_id,
			"visible_item" => $this->mdb_obj['visible_item'],
    		"default_sort" => $this->mdb_obj['default_sort']
		);
		if ($count == 0) {
	    	$result = $this->db->insertExecute("multidatabase_block", array_merge(array("block_id" => $this->block_id), $params), true);
		}else {
	    	$result = $this->db->updateExecute("multidatabase_block", $params,  array("block_id"=>$this->block_id), true);
    	}
    	if ($result === false) {
    		return 'error';
    	}
    	$this->request->setParameter("multidatabase_id", $multidatabase_id);
        return 'success';
    }
}
?>