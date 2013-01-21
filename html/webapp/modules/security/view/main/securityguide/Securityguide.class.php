<?php

/**
 * セキュリティガイド
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Security_View_Main_Securityguide extends Action
{
	// リクエストパラメータを受け取るため
	
	// 使用コンポーネントを受け取るため
    var $db = null;
    
    // フィルタによりセット
    
    // 値をセットするため
	var $securityguide_register_globals = null;
	var $securityguide_allow_url_fopen = null;
	var $securityguide_prefix = null;

    /**
     * セキュリティ設定値取得
     *
     * @access  public
     */
	function execute()
	{
        // register_globals
		$register_globals = ini_get('register_globals');        
		if ($register_globals) {
			$this->securityguide_register_globals = "on";
		} else {
			$this->securityguide_register_globals = "off";	
		}

		// allow_url_fopen
		$allow_url_fopen = ini_get('allow_url_fopen');
		if ($allow_url_fopen) {
			$this->securityguide_allow_url_fopen = "on";	
		} else {
			$this->securityguide_allow_url_fopen = "off";	
		}

		// 使用中のプレフィックス
		$this->securityguide_prefix = substr($this->db->getPrefix(), 0, strlen($this->db->getPrefix()) - 1);
		
		return 'success';
	}
}
?>
