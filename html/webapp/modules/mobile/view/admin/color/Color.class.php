<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯色設定画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_View_Admin_Color extends Action
{
	var		$db = null;
	var		$config = null;
	var		$configView = null;

	var		$module_id = null;

	function execute( )
	{
		$this->config = $this->configView->getConfig( $this->module_id, false );
		if( $this->config == false ) {
			return "error";
		}
		return "success";
	}
}
?>
