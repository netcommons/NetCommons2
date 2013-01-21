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
class Online_Update extends Action
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
		if (!in_array($this->db->getPrefix()."online", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."online` (".
					  "`block_id`            int(11) unsigned NOT NULL,".
					  "`user_flag`           tinyint(1) NOT NULL default 0,".
					  "`member_flag`         tinyint(1) NOT NULL default 0,".
					  "`total_member_flag`   tinyint(1) NOT NULL default 0,".
					  "`room_id`             int(11) NOT NULL default 0,".
					  "`insert_time`         varchar(14) NOT NULL default '',".
					  "`insert_site_id`      varchar(40) NOT NULL default '',".
					  "`insert_user_id`      varchar(40) NOT NULL default '',".
					  "`insert_user_name`    varchar(255) NOT NULL,".
					  "`update_time`         varchar(14) NOT NULL default '',".
					  "`update_site_id`      varchar(40) NOT NULL default '',".
					  "`update_user_id`      varchar(40) NOT NULL default '',".
					  "`update_user_name`    varchar(255) NOT NULL,".
					  "PRIMARY KEY  (`block_id`)".
				   ") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		
		// onlineにindexを追加
		$sql = "SHOW INDEX FROM `".$this->db->getPrefix()."online` ;";
		$results = $this->db->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."online` ADD INDEX ( `room_id`  ) ;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		
		return true;
	}
}
?>
