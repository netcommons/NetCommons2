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
class Multidatabase_Action_Main_Confirm extends Action
{
    // リクエストパラメータを受け取るため
    var $content_id = null;
    
    // バリデートによりセット
	var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $session = null;
	var $mdbAction = null;

    // 値をセットするため
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {	
    	$content_before_update = $this->db->selectExecute("multidatabase_content", array("content_id" => $this->content_id));
		if (empty($content_before_update)) {
			return 'error';
		}
		
		$params = array(
			"agree_flag" => MULTIDATABASE_STATUS_AGREE_VALUE
		);
		$result = $this->db->updateExecute("multidatabase_content", $params,  array("content_id" => $this->content_id), true);
		if($result === false) {
			return 'error';
		}
		
		$this->session->setParameter("multidatabase_mail_content_id", null);
		$this->session->setParameter("multidatabase_confirm_mail_content_id", null);
		if ($this->mdb_obj['mail_flag'] == _ON) {
			$this->session->setParameter("multidatabase_mail_content_id", array("content_id" => $this->content_id, "agree_flag" => MULTIDATABASE_STATUS_AGREE_VALUE));
		}
		
		if($this->mdb_obj['agree_mail_flag'] == _ON) {
			$this->session->setParameter("multidatabase_confirm_mail_content_id", $this->content_id);
		}

		//--新着情報関連 Start--
		$result = $this->mdbAction->setWhatsnew($this->content_id);
		if ($result === false) {
			return 'error';
		}
		//--新着情報関連 End--
		
		// --- 投稿回数更新 ---
		
		$before_content = isset($content_before_update[0]) ? $content_before_update[0] : null;
		$result = $this->mdbAction->setMonthlynumber(_ON, MULTIDATABASE_STATUS_RELEASED_VALUE, $before_content['temporary_flag'], $before_content);
		if ($result === false) {
			return 'error';
		}
        return 'success';
    }
}
?>
