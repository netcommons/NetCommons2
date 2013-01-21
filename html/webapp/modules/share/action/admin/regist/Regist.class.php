<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サイトの登録・編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Share_Action_Admin_Regist extends Action
{
	// リクエストパラメータを受け取るため
	
	var $url = null;
	var $old_url = null;
	
    // 使用コンポーネントを受け取るため
	var $sitesView = null;
	var $sitesAction = null;
	var $db = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->old_url == null) {
			// 新規登録
			$result = $this->sitesAction->insertSite($this->url, _OFF);
		} else {
			// 更新
			$upd_params = array("url" => $this->url);
			$upd_where_params = array("url" => $this->old_url);
			$result = $this->db->updateExecute('sites', $upd_params, $upd_where_params);
		}
		if($result === false) return 'error';

		return 'success';
	}
}
?>
