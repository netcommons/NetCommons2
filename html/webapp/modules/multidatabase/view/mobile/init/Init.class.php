<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯対応汎用ＤＢ一覧表示
 *
 * @package	 NetCommons
 * @author	  Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2009 All Creator Co., Ltd.
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NC Support Project, provided by AllCreator Co., Ltd.
 * @access	  public
 */
class Multidatabase_View_Mobile_Init extends Action
{
	//値をセットするため
	var $multidatabase_list = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		return 'success';
	}
}
?>