<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * レスキューパスワードチェックアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_Action_Rescue extends Action
{
	// リクエストパラメータを受け取るため
	var $password = null;
	
	// 使用コンポーネントを受け取るため
	var $configAction = null;
	
	// 値をセットするため
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$result = $this->configAction->updConfigValue(_SYS_CONF_MODID, "bad_ips", '', _SECURITY_CONF_CATID);
		if($result === false) {
			return 'error';	
		}
		return 'success';
	}
}
?>