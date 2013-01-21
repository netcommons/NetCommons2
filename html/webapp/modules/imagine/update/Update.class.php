<?php
/**
 * モジュールアップデートクラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Imagine_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		// imagine_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."imagine_block` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."imagine_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		
		return true;
	}
}
?>
