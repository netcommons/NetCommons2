<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Delcomment extends Action
{
	// リクエストパラメータを受け取るため
	var $comment_id = null;
	var $content_id = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $whatsnewAction = null;
	var $mdbAction = null;

	// バリデートによりセットするため

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$result = $this->db->deleteExecute("multidatabase_comment", array("comment_id" => intval($this->comment_id)));
		if ($result === false) {
			return 'error';
		}

		//--新着情報関連 Start--
		
		$count = $this->db->countExecute("multidatabase_comment", array("content_id"=>$this->content_id));
		if ($count === false) {
			return 'error';
		}
		if($count == 0) {
			$result = $this->whatsnewAction->delete($this->content_id, _ON);
		} else {
			$params = array();
			$whereParams = array('content_id' => $this->content_id);
			$sql = "SELECT M.multidatabase_id, M.title_metadata_id "
					. "FROM {multidatabase_content} MC "
					. "INNER JOIN {multidatabase} M "
					. "ON MC.multidatabase_id = M.multidatabase_id"
					. $this->db->getWhereSQL($params, $whereParams);
			$multidatabase = $this->db->execute($sql, $params);
			if (empty($multidatabase)) {
				return 'error';
			}

			$whatsnewTitle = $this->mdbAction->getWhatsnewTitle($this->content_id, $multidatabase[0]['title_metadata_id']);
			if ($whatsnewTitle === false) {
				return 'error';
			}
			$whatsnew = array(
				"unique_id" => $this->content_id,
				"title" => $whatsnewTitle["title"],
				"description" => $whatsnewTitle["description"],
				"action_name" => "multidatabase_view_main_detail",
				"parameters" => "content_id=". $this->content_id . "&multidatabase_id=" . $multidatabase[0]["multidatabase_id"],
				"count_num" => $count,
				"child_flag" => _ON
			);
			$result = $this->whatsnewAction->auto($whatsnew);
		}
		if($result === false) {
			return 'error';
		}
		
		//--新着情報関連 End--

    	return 'success';
	}
}
?>
