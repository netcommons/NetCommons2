<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯設定色変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Mobile_Action_Admin_Color extends Action
{
	var	$db = null;

	var	$color_back= null;
	var	$color_text= null;
	var	$color_link= null;
	var	$color_vlink= null;
	var $smartphone_theme_color = null;
	var	$actionChain = null;

	function execute()
	{
		$ret = $this->_update( COLOR_BACK_ITEMNAME, $this->color_back );
		if( $ret == false ) {
			return "error";
		}
		$ret = $this->_update( COLOR_TEXT_ITEMNAME, $this->color_text );
		if( $ret == false ) {
			return "error";
		}
		$ret = $this->_update( COLOR_LINK_ITEMNAME, $this->color_link );
		if( $ret == false ) {
			return "error";
		}
		$ret = $this->_update( COLOR_VLINK_ITEMNAME, $this->color_vlink );
		if( $ret == false ) {
			return "error";
		}
		$ret = $this->_update( COLOR_SMT_THEMECOLORNAME, $this->smartphone_theme_color);
		if( $ret == false ) {
			return "error";
		}
		return "success";
	}
	function _update( $name, $value )
	{
		$params = array( 'conf_value'=>$value );
		$where_params = array( 'conf_name'=>$name );
		$ret = $this->db->updateExecute( "config", $params, $where_params, true );
		if( $ret != true ) {
			return false;
		}
		else {
			return true;
		}
	}
}
?>
