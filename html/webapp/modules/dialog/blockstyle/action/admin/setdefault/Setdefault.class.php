<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロックテーマ-カスタム　規定値に戻す
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Dialog_Blockstyle_Action_Admin_SetDefault extends Action
{
	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $theme_name = null;
	
	// コンポーネントを使用するため
	var $db = null;
	var $session = null;
	
    /**
     * ブロックテーマ-カスタム　規定値に戻す
     * @access  public
     */
    function execute()
    {
    	//style.cssを元の状態に復元する
    	if($this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
	    	$where_params = array(
				"dir_name" => $this->theme_name,
				"type" => _CSS_TYPE_BLOCK_CUSTOM,
				"block_id" => 0
			);
			$result = $this->db->deleteExecute("css_files", $where_params);
			if($result === false) {
				return 'error';	
			}
    	}
    	
		$where_params = array(
			"dir_name" => $this->theme_name,
			"type" => _CSS_TYPE_BLOCK_CUSTOM,
			"block_id" => intval($this->block_id)
		);
		$result = $this->db->deleteExecute("css_files", $where_params);
		if($result === false) {
			return 'error';	
		}
		
		return 'success';
    }
}
?>
