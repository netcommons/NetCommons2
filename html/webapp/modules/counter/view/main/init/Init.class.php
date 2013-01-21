<?php
/**
 * カウンタ一般画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Counter_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $counterView = null;
	var $counterAction = null;
	var $session = null;

	// 値をセットするため
	var $counter = null;
	var $imgSrcs = null;

	function execute()
	{
		$key = "_counter_". $this->block_id;
		$sessionNumber = $this->session->getParameter(array("_session_common", $key));
		if (!isset($sessionNumber)) {
			if (!$this->counterAction->incrementCounter()) {
				return "error";
			}

		}

		$this->counter = $this->counterView->getCounter();

		if (isset($sessionNumber)) {
			$this->counter["counter_num"] = intval($sessionNumber);
		} else {
			$this->session->setParameter(array("_session_common", $key), $this->counter["counter_num"]);
		}

		$this->imgSrcs = $this->counterView->getImgSrcs($this->counter);

		return "success";
	}
}
?>
