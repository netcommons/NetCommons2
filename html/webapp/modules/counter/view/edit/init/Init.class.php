<?php
/**
 * カウンタ編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Counter_View_Edit_Init extends Action
{
	// 使用コンポーネントを受け取るため
	var $counterView=null;
	var $fileView=null;

	// 値をセットするため
	var $counter=null;

	// 画像選択
	var $picture_arry=null;
	// 表示桁数
	var $counter_arry=null;

	function execute()
	{
		$this->counter = $this->counterView->getCounter();
		if(!isset($this->counter['counter_digit'])) {
			$this->counter['counter_digit'] = COUNTER_DIGIT;
			$this->counter['show_type'] = SHOW_TYPE;
			$this->counter['show_char_before'] = SHOW_CHAR_BEFORE;
			$this->counter['show_char_after'] = SHOW_CHAR_AFTER;
			$this->counter['comment'] = OTHER_DISP_CHAR;
		}

		//画像選択リストの取得
		$this->picture_arry = $this->fileView->getCurrentDir(HTDOCS_DIR. "/images/counter/common/");
		sort($this->picture_arry);	//ソート

		return 'success';
	}
}
?>