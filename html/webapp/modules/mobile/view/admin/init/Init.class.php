<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯管理の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_View_Admin_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $module_id = null;

    // 使用コンポーネントを受け取るため
	var $mobileView = null;
	var $configView = null;

    // 値をセットするため
	var $modules = null;
	var $default_module = null;
	var $allow_emulator = null;
	var $mobile_text_html_mode = null;
	var $mobile_imgdsp_size = null;

	var	$gd_enable = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
		$config = $this->configView->getConfig($this->module_id, false);
		if ($config === false) {
    		return false;
    	}
		if( !function_exists( "gd_info" ) ) {
			$this->gd_enable = _OFF;
		}
		else {
			$this->gd_enable = _ON;
		}
    	$this->default_module = $config['default_module']['conf_value'];
    	$this->allow_emulator = $config['allow_emulator']['conf_value'];
		$this->mobile_text_html_mode = $config['mobile_text_html_mode']['conf_value'];
		if( $this->gd_enable == _OFF ) {
			$this->mobile_imgdsp_size = MOBILE_IMGDSP_SIZE_ORG;
		}
		else {
			$this->mobile_imgdsp_size = $config['mobile_imgdsp_size']['conf_value'];
		}

		$this->modules = $this->mobileView->getModules();
       	return 'success';
    }
}
?>
