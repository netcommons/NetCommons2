<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システム管理>>メール設定画面表示
 * 		メール設定項目を表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_View_Main_Mail extends Action
{
	// リクエストパラメータを受け取るため
	var $languages = null;
	
    // 使用コンポーネントを受け取るため
    var $configView = null;
    
    // フィルタによりセット
    
    // 値をセットするため
    var $config = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$this->config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _MAIL_CONF_CATID);
    	if ($this->config === false) {
    		return 'error';
    	}
    	return 'success';
    }
}
?>
