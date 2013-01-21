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
class Language_Action_Edit_Create extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $module_id = null;
	
	// バリデートによりセット
	var $lang_obj = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	
	function execute()
	{
		$params = array(
			'block_id' => intval($this->block_id),
			'display_type' => $this->lang_obj['display_type'],
			'display_language' => $this->lang_obj['display_language']
		);
		
    	$result = $this->db->insertExecute('language_block', $params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
		return 'success';
	}
}
?>