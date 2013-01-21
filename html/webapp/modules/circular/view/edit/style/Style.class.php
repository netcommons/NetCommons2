<?php

/**
 * 表示方法変更表示
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Edit_Style extends Action
{
	// 使用コンポーネントを受け取るため
	var $circularView = null;
	var $circularCommon = null;

	// 値をセットするため
	var $visible_row = null;
	var $visible_row_map = null;
	var $block_type = null;

	/**
	 * execute処理
	 *
	 * @return アクション文字列
	 * @access  public
	 */
	function execute()
	{
		$result = $this->circularView->getBlock();
		if ($result === false) {
			return 'error';
		}
		$this->visible_row = intval($result['visible_row']);
		$this->block_type = intval($result['block_type']);

		$this->visible_row_map =& $this->circularCommon->getMap(CIRCULAR_VISIBLE_ROW);

		return 'success';
	}
}
?>