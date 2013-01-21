<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アクセス状況画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_View_Main_Monthly extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    var $year = null;
    var $month = null;
    
    // コンポーネントを使用するため
    var $monthlynumberView = null;
    var $authoritiesView = null;
    
    // バリデートによりセット
    var $user = null;
    
    // 値をセットするため
    var $monthly_list = null;
    var $pages_list = null;
    var $rowspan_list = null;
    var $monthly_row_exists = null;
    var $monthly_login_list = null;
    
    var $myroom_use_flag = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$role_auth_id = $this->user['role_authority_id'];
    	$authority = $this->authoritiesView->getAuthorityById($role_auth_id);
    	if($authority === false) {
			return 'error';
		}
		$this->myroom_use_flag = $authority['myroom_use_flag'];
    	// 現状、固定値
    	$this->year = null;
    	$this->month = null;
    	list($this->month, $this->monthly_list, $this->pages_list, $this->monthly_row_exists, $this->rowspan_list, $this->monthly_login_list) = $this->monthlynumberView->get($this->year, $this->month, null, $this->user_id, $role_auth_id);
        
        return 'success';
    }
}
?>