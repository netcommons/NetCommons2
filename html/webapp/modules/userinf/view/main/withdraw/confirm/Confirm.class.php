<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 退会画面-確認画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_View_Main_Withdraw_Confirm extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    
    // コンポーネントを使用するため
    var $configView = null;
    
    // 値をセットするため
    var $withdraw_disclaimer = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$withdraw_disclaimer = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "withdraw_disclaimer");
    	if($withdraw_disclaimer === false) {
			return $errStr;
		}
		$this->withdraw_disclaimer = $withdraw_disclaimer['conf_value'];
    	return 'success';
    }
}
?>
