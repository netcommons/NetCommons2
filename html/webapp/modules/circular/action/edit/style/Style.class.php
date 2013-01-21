<?php

/**
 * 表示方法設定
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Circular_Action_Edit_Style extends Action
{
	// 使用コンポーネントを受け取るため
	var $circularAction = null;

	/**
	 * execute処理
	 *
	 * @return アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$result = $this->circularAction->setupBlock();
		if($result === false) {
			return 'error';
		}
		return 'success';
	}
}


?>
