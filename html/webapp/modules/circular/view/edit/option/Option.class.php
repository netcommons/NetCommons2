<?php

/**
 * メール設定表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Edit_Option extends Action
{
	// 使用コンポーネントを受け取るため
	var $circularView = null;

	// 値をセットするため
	var $config = null;

	/**
	 * execute処理
	 *
	 * @return アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$config = $this->circularView->getConfig();
		if ($config === false) {
			return 'error';
		}
		$this->config = $config;

		return 'success';
	}
}
?>