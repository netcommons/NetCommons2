<?php

/**
 * 選択肢追加要素表示アクションクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Main_Choice_Add extends Action
{
	// リクエストパラメータを受け取るため
	var $choice_count = null;

	// 値をセットするため
	var $choice = array();

	/**
	 * 選択肢追加要素表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$choiceLabels = explode("|", CIRCULAR_REPLY_CHOICE_LABEL);
		$label = $choiceLabels[$this->choice_count % count($choiceLabels)];
		$this->choice = array('choice_sequence'=>$this->choice_count, 'label'=>$label);

		$this->choice_count++;

		return "success";
	}
}
?>
