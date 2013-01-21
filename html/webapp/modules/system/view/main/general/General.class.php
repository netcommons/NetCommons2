<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システム管理>>一般設定画面表示
 * 		一般設定項目を表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_View_Main_General extends Action
{
	// リクエストパラメータを受け取るため

    // 使用コンポーネントを受け取るため
    var $session = null;
    var $configView = null;
    var $languagesView = null;
    var $pagesView = null;
    var $fileView = null;
    var $languages = null;
    var $db = null;

    // フィルタによりセット

    // 値をセットするため
    var $config = null;
    var $pages = null;
    var $upload_capacity = array();

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
        $this->config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
        if ($this->config === false) {
            return 'error';
        }

        // 選択可能なルーム一覧取得
    	$where_params = array(
								"{users}.active_flag IN ("._USER_ACTIVE_FLAG_OFF.","._USER_ACTIVE_FLAG_ON.","._USER_ACTIVE_FLAG_PENDING.","._USER_ACTIVE_FLAG_MAILED.")" => null,
								"{users}.system_flag IN ("._ON.","._OFF.")" => null,
								"{users}.role_authority_id" => _SYSTEM_ROLE_AUTH_ID
		);
    	$users = $this->db->selectExecute("users", $where_params, null, 1, 0);
		if($users === false) {
			return 'error';
		}
		$where_params = array(
			"user_id" => $users[0]['user_id'],
			"{pages}.room_id={pages}.page_id" => null
		);
		$order_params = array(
			"space_type" => "ASC",
			"private_flag" => "ASC",
			"thread_num" => "ASC",
			"display_sequence" => "ASC"
		);
		$_user_auth_id = _AUTH_CHIEF;
		$more_than_authority_id = _AUTH_OTHER;
		$this->pages =& $this->pagesView->getShowPagesList($where_params, $order_params, null, null, array($this, '_showpages_fetchcallback'));

    	if ($this->pages === false) {
    		return 'error';
    	}
    	// グループルームのアップロード最大容量
    	$upload_cap_arr = explode("|",SYSTEM_UPLOAD_CAPACITY);
    	foreach($upload_cap_arr as $upload_cap_value) {
    		$this->upload_capacity[$upload_cap_value] = $this->fileView->formatSize($upload_cap_value);
    	}
    	return 'success';
    }

	function _showpages_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(($row['private_flag'] == _OFF && $row['space_type'] == _SPACE_TYPE_GROUP && $row['thread_num'] == 0) ||
				($row['private_flag'] == _ON && $row['space_type'] == _SPACE_TYPE_GROUP && $row['thread_num'] == 0 && $row['default_entry_flag'] == _ON)) {
				continue;
			}
			$ret[] = $row;
		}
		return $ret;
	}
}
?>
