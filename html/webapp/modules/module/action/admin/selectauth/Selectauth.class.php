<?php
/**
 * 利用可能権限更新処理
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_Action_Admin_Selectauth extends Action
{
	// リクエストパラメータ
	var $act_module_id = null;
	var $module_authorities = null;

	// 使用コンポーネントを受け取るため
	var $modulesView = null;
	var $authoritiesView = null;
	var $authoritiesAction = null;
	
    /**
     * execute実行
     *
     * @access  public
     */
	function execute()
	{
		$authorities_modules_link =& $this->authoritiesView->getAuthoritiesModulesLinkByModuleId(intval($this->act_module_id), null, null, array($this, "_fetchcallbackAuthorityModuleLink"));
		
		//
		// AuthorityModuleLinkテーブル削除
		//
		foreach($authorities_modules_link as $authority_modules_link) {
			if($authority_modules_link["authority_id"] != null && !in_array($authority_modules_link['role_authority_id'], $this->module_authorities)) {
					//削除処理
					$where_params = array(
						"module_id" =>intval($this->act_module_id),
						"role_authority_id" => $authority_modules_link['role_authority_id']
					);
					$result = $this->authoritiesAction->delAuthorityModuleLink($where_params);
					if ($result === false) {
						return 'error';
					}
				}
		}
		
		//
		// 権限登録
		//
		$modules =& $this->modulesView->getModules(null, null, null, null, array($this, "_fetchcallbackModules"));
		if(is_array($this->module_authorities)) {
			foreach($this->module_authorities as $module_authority) {
				if ($authorities_modules_link[$module_authority]['system_flag'] == _ON) {
					// 管理系
					$authority_id = $authorities_modules_link[$module_authority]['user_authority_id'];
				} else {
					// 一般
					$authority_id = _AUTH_CHIEF;	//主担固定
				}
				
				
				if(isset($authorities_modules_link[$module_authority]) && $authorities_modules_link[$module_authority]["authority_id"] != null) {
					// 既に登録済み
					// 更新しない
					//$where_params = array(
					//					"role_authority_id" => $module_authority,
					//					"module_id" =>intval($this->act_module_id)
					//				);
					//$params = array("authority_id" => $authority_id);
					//$result = $this->authoritiesAction->updAuthorityModuleLink($params, $where_params);
					//if ($result === false) {
					//	return 'error';
					//}
				} else {
					// 新規
					$params = array(
										"role_authority_id" => $module_authority,
										"module_id" =>intval($this->act_module_id),
										"authority_id" => $authority_id
					);
					$result = $this->authoritiesAction->insAuthorityModuleLink($params);
					if ($result === false) {
						return 'error';
					}
				}
			}
		}
		
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackModules($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['module_id']] = $row;
		}
		return $ret;
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @return array configs
	 * @access	private
	 */
	function _fetchcallbackAuthorityModuleLink($result) 
	{
		$authorities_modules_link = array();
		while ($row = $result->fetchRow()) {
			//if($row["authority_id"] != null) {
				$authorities_modules_link[$row["role_authority_id"]] = $row;
			//}
		}
		return $authorities_modules_link;
	}
}
?>
