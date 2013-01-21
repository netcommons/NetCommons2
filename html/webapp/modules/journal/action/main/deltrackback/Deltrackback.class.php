<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Deltrackback extends Action
{
    // リクエストパラメータを受け取るため
    var $post_id = null;
    var $trackback_id = null;

    // バリデートによりセット
    var $post = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
	var $whatsnewAction = null;

    // 値をセットするため

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		if(!$this->post['has_edit_auth']) {
			return 'error';
		}
    	// トラックバック削除
		$result = $this->db->deleteExecute("journal_post", array("post_id"=>$this->trackback_id));
		if($result === false) {
			return 'error';
		}

		//--新着情報関連 Start--
		$count = $this->db->countExecute("journal_post", array("parent_id"=>$this->post_id, "direction_flag != " . JOURNAL_TRACKBACK_TRANSMIT => null));
		if ($count === false) {
			return 'error';
		}
		if($this->post['agree_flag'] != JOURNAL_STATUS_AGREE_VALUE) {
			$whatsnew = array(
				"unique_id" => $this->post_id,
				"count_num" => $count,
				"child_flag" => _ON
			);
			$result = $this->whatsnewAction->auto($whatsnew);
			if($result === false) {
				return 'error';
			}
		}else {
			if($count == 0) {
				$result = $this->whatsnewAction->delete($this->post_id, _ON);
			}
		}
		
		//--新着情報関連 End--

		return 'success';
    }
}
?>
