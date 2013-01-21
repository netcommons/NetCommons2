<?php
/**
 * カウンタ登録処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Counter_Action_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $zero_flag = null;

	// 使用コンポーネントを受け取るため
	var $counterAction=null;
	var $session = null;

	function execute()
	{
		if (!$this->counterAction->setCounter()) {
			return "error";
		}

		if ($this->zero_flag == _ON) {
			$key = "_counter_". $this->block_id;
			$this->session->removeParameter(array("_session_common", $key));
		}

		return "success";
	}
}
?>
