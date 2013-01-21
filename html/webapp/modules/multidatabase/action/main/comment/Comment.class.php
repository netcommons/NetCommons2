<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Comment extends Action
{
    // リクエストパラメータを受け取るため
    var $content_id = null;
    var $comment_id = null;
    var $comment_content = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
	var $whatsnewAction = null;
	var $mdbAction = null;

    // 値をセットするため

    /**
     * コメント追加
     *
     * @access  public
     */
    function execute()
    {
    	if(empty($this->comment_id)) {
	    	$params = array(
				"content_id" => intval($this->content_id),
				"comment_content" => $this->comment_content
			);
			$comment_id = $this->db->insertExecute("multidatabase_comment", $params, true, "comment_id");
    	}else {
    		$params = array(
				"comment_content" => $this->comment_content
			);
    		$comment_id = $this->db->updateExecute("multidatabase_comment", $params,  array("comment_id"=>$this->comment_id), true);
    	}
		if($comment_id === false) {
			return 'error';
		}

		//--新着情報関連 Start--
		$content = $this->db->selectExecute("multidatabase_content", array("content_id"=>$this->content_id));
		if ($content === false && !isset($content[0])) {
			return 'error';
		}
		
		$count = $this->db->countExecute("multidatabase_comment", array("content_id"=>$this->content_id));
		if ($count === false) {
			return 'error';
		}
		
		$multidatabase = $this->db->selectExecute("multidatabase", array("multidatabase_id" => $content[0]['multidatabase_id']));
		if (empty($multidatabase)) {
			return 'error';
		}
		$result = $this->mdbAction->getWhatsnewTitle($this->content_id, $multidatabase[0]['title_metadata_id']);
		if ($result === false) {
			return 'error';
		}
		
		$whatsnew = array(
			"unique_id" => $this->content_id,
			"title" => $result['title'],
			"description" => $result['description'],
			"action_name" => "multidatabase_view_main_detail",
			"parameters" => "content_id=". $this->content_id . "multidatabase_id=" . $content[0]["multidatabase_id"],
			"count_num" => $count,
			"child_flag" => _ON,
			"insert_time" => timezone_date(),
			"insert_user_id" => $content[0]['insert_user_id'],
			"insert_user_name" => $content[0]['insert_user_name']
		);
		$result = $this->whatsnewAction->auto($whatsnew);
		if($result === false) {
			return 'error';
		}
		
		//--新着情報関連 End--

        return 'success';
    }
}
?>
