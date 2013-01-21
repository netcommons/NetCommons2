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
class Share_Action_Admin_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $url = null;
	
    // 使用コンポーネントを受け取るため
	var $sitesView = null;
	// var $sitesAction = null;
	var $db = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$site = $this->sitesView->getSitesByUrl($this->url);
		if(isset($site['self_flag']) && $site['self_flag'] == _OFF) {
			$del_params = array("url" => $this->url);
			$result = $this->db->deleteExecute('sites', $del_params);
			if($result === false) return 'error';
		}
		
		return 'success';
	}
}
?>
