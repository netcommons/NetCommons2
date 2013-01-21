<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限管理-レベル設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_View_Admin_Level extends Action
{
	//リクエストパラメータ
	var $role_authority_id = null;
	var $role_authority_name = null;
	var $user_authority_id = null;
	
	var $detail = null;		// 詳細情報配列
	
	// 使用コンポーネントを受け取るため
	var $authoritiesView = null;
	var $session = null;
	var $authorityCompmain = null;
	
	// バリデートによりセット
	var $authority = null;
	var $mod_authorities = null; // その他モデレータ一覧

	// 値をセットするため
	

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if(isset($this->authority)) {
			$this->authority['hierarchy_level'] = $this->authority['hierarchy'] - 3;
		} else {
			$this->authority = array();
			$this->authority['hierarchy_level'] = AUTHORITY_DEFAULT_LEVEL;
		}
		// sessionの値をセット
		$hierarchy = $this->session->getParameter(array("authority", $this->role_authority_id, "level", "hierarchy"));
		if(isset($hierarchy)) {
			$this->authority['hierarchy_level'] = $hierarchy;
		}
		//
		// モデレータの細分化された一覧を取得
		//
		$mod_where_params = array(
								"user_authority_id" => _AUTH_MODERATE,
								"role_authority_id != ".$this->role_authority_id => null
								);
		$mod_order_params = array("hierarchy" => "DESC");
		$this->mod_authorities = $this->authoritiesView->getAuthorities($mod_where_params, $mod_order_params);
		if($this->mod_authorities === false) {
			return 'error';
		}
		//
		// Detailの値があればSessionへセット
		//
		$this->authorityCompmain->setSessionDetail($this->detail);
		
		return 'success';
	}
}
?>
