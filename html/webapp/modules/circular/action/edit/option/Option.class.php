<?php

/**
 * メール設定情報設定
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Circular_Action_Edit_Option extends Action
{
	// 使用コンポーネントを受け取るため
	var $circularAction = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$result = $this->circularAction->setupConfig();
		if($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
