<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アップデートクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

    /**
     * execute実行
     *
     * @access  public
     */
	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaTables = $adodb->MetaTables();
		// calendar_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."holiday` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_lang_dirname_flag = true;
		$alter_table_rrule_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "lang_dirname") {
				$alter_table_lang_dirname_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "rrule_id") {
				$alter_table_rrule_id_flag = false;
			}
		}
		if($alter_table_lang_dirname_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."holiday` ADD INDEX ( `lang_dirname` , `holiday` );";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_rrule_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."holiday` ADD INDEX ( `rrule_id` );";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		return true;
	}
}
?>
