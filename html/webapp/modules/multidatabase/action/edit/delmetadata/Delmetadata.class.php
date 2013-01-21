<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Delmetadata extends Action
{
	// リクエストパラメータを受け取るため
	var $metadata_id = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mdbAction = null;
	
	// バリデートによりセットするため
	var $metadata = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{	
		$result = $this->mdbAction->deleteMetadata($this->metadata_id);
		if ($result === false) {
			return 'error';
		}
		
		// 表示順-前詰め処理
		$params = array(
			"multidatabase_id" => $this->metadata['multidatabase_id'],
			"display_pos" => $this->metadata['display_pos']
		);
		$sequence_param = array(
			"display_sequence" => $this->metadata['display_sequence']
		);
    	$result = $this->db->seqExecute("multidatabase_metadata", $params, $sequence_param);
		if ($result === false) {
			return 'error';
		}
		
		return 'success';
	}
}
?>
