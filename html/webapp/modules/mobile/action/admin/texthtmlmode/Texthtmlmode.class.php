<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 初期表示の設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action_Admin_Texthtmlmode extends Action
{
    // リクエストパラメータを受け取るため
	var $mobile_text_html_mode = null;
	var $module_id = null;

    // 使用コンポーネントを受け取るため
	var $configAction = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		switch( $this->mobile_text_html_mode ) {
			case MOBILE_TEXTHTML_MODE_TEXT:
				break;
			case  MOBILE_TEXTHTML_MODE_HTML:
				break;
			default:
				$this->mobile_text_html_mode = MOBILE_TEXTHTML_MODE_TEXT;
		}
    	$result = $this->configAction->updConfigValue($this->module_id, "mobile_text_html_mode", $this->mobile_text_html_mode);
    	if ($result === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>