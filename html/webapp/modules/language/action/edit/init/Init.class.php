<?php
/**
 * モジュール追加時に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Language_Action_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $display_type = null;
	var $display_language = null;
	
	// バリデートによりセット
	var $lang_obj = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	
	function execute()
	{
		$display_language = implode('|',$this->display_language);
		$params = array(
			'display_type' => $this->display_type,
			'display_language' => $display_language
		);

		$result = $this->db->updateExecute('language_block', $params, array('block_id' => intval($this->block_id)), true);
		if($result === false) {
			return 'error';
		}

		return 'success';
	}
}
?>