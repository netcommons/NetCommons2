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
class Mobile_Action_Admin_Imgdspopt extends Action
{
    // リクエストパラメータを受け取るため
	var $mobile_imgdsp_size = null;
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
		$imgsize = intval($this->mobile_imgdsp_size);
		switch( $imgsize ) {
			case MOBILE_IMGDSP_SIZE_240:
			case MOBILE_IMGDSP_SIZE_480:
				break;
			default:
				$imgsize = MOBILE_IMGDSP_SIZE_ORG;
		}
    	$result = $this->configAction->updConfigValue($this->module_id, "mobile_imgdsp_size", $imgsize);
    	if ($result === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>