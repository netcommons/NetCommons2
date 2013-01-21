<?php
/**
 * モジュールアップデートクラス
 * 　　backup_uploadsにURLカラム追加(他サイトからのリストアを許可するため)
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Backup_Update extends Action
{	
	//使用コンポーネントを受け取るため
	var $db = null;
	
	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."backup_uploads");
		if(!isset($metaColumns["URL"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."backup_uploads` 
						ADD `url` TEXT NOT NULL AFTER `site_id` ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		
		$backup_uploads = $this->db->selectExecute("backup_uploads", array("url!"=> '',"room_id>0" => null));
		if($backup_uploads !== false && count($backup_uploads) > 0) {
			$this->db->updateExecute("backup_uploads", array("parent_id" => _OFF), array("url!"=> '',"room_id>0" => null,"thread_num" => 2));
			$this->db->updateExecute("backup_uploads", array("room_id" => _OFF), array("url!"=> '',"room_id>0" => null));
			
		}
		
		return true;
	}
}
?>
