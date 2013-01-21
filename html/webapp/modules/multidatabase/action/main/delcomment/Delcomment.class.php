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
			$whatsnew = array(
				"unique_id" => $this->content_id,
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
