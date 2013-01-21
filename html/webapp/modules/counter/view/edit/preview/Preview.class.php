<?php
/**
 * カウンタプレビュー表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Counter_View_Edit_Preview extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $counter_digit = null;
	var $show_type = null;
	var $show_char_before = null;
	var $show_char_after = null;
	var $comment = null;

	// 使用コンポーネントを受け取るため
	var $counterView = null;

	// 値をセットするため
	var $counter = null;
	var $imgSrcs = null;

	function execute()
	{
		$this->counter = $this->counterView->getCounter();
		if (!$this->counter) {
			$this->counter['counter_num'] = "1";
		}
		$this->counter['counter_digit'] = $this->counter_digit;
		$this->counter['show_type'] = $this->show_type;
		$this->counter['show_char_before'] = $this->show_char_before;
		$this->counter['show_char_after'] = $this->show_char_after;
		$this->counter['comment'] = $this->comment;

		$this->imgSrcs = $this->counterView->getImgSrcs($this->counter);

		return 'success';
	}
}
?>