<?php

/**
 * 回覧追記ポップアップ表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Postscript_Init extends Action
{
	// 使用コンポーネントを受け取るため
	var $circularView = null;

	// 値をセットするため
	var $circular_id = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		return 'success';
	}
}
?>
