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
class Language_Update extends Action
{	
	var $module_id = null;
	//使用コンポーネントを受け取るため
	var $db = null;
	
	function execute()
	{
		$adodb = $this->db->getAdoDbObject();

		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."language_block", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."language_block` (" .
					"`block_id`            int(11) UNSIGNED NOT NULL default '0',".
					"`display_type`        tinyint(1) NOT NULL default '0',".
					"`display_language`    varchar(255) NOT NULL default '',".
					"`room_id`             int(11) UNSIGNED NOT NULL default '0',".
					"`insert_time`         varchar(14) NOT NULL default '',".
					"`insert_site_id`      varchar(40) NOT NULL default '',".
					"`insert_user_id`      varchar(40) NOT NULL default '',".
					"`insert_user_name`    varchar(255) NOT NULL default '',".
					"`update_time`         varchar(14) NOT NULL default '',".
					"`update_site_id`      varchar(40) NOT NULL default '',".
					"`update_user_id`      varchar(40) NOT NULL default '',".
					"`update_user_name`    varchar(255) NOT NULL default '',".
					" PRIMARY KEY (`block_id`)" .
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}
		
		$sql = "SELECT {blocks}.*, {pages}.room_id FROM {blocks}, {pages} WHERE {blocks}.page_id={pages}.page_id AND {blocks}.module_id=".$this->module_id;
		$blocks = $this->db->execute($sql);
		if($blocks === false) return false;
		if(!empty($blocks)) {
			foreach($blocks as $block) {
				//表示方法デフォルト0とする
				$params = array(
					'block_id' => $block['block_id'],
					'room_id' => $block['room_id'],
					'display_type' => 1,
					'display_language' => LANGUAGE_JAPANESE.'|'.LANGUAGE_ENGLISH.'|'.LANGUAGE_CHINESE,
					'insert_time' => $block['insert_time'],
					'insert_site_id' => $block['insert_site_id'],
					'insert_user_id' => $block['insert_user_id'],
					'insert_user_name' => $block['insert_user_name'],
					'update_time' => $block['update_time'],
					'update_site_id' => $block['update_site_id'],
					'update_user_id' => $block['update_user_id'],
					'update_user_name' => $block['update_user_name']
				);
				$result = $this->db->insertExecute('language_block', $params);
			}
		}

		return true;
	}
}
?>
