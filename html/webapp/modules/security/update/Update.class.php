<?php
/**
 * モジュールアップデートクラス
 * 　　css_filesにカラム追加
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Security_Update extends Action
{	
	//使用コンポーネントを受け取るため
	var $db = null;
	
	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."security_access");
		if ($metaColumns["REQUEST_URI"]->type == "varchar") {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."security_access` CHANGE `request_uri` `request_uri` TEXT;";
			$result = $this->db->execute($sql);
			if($result === false) return false;
		}
		
		//
		// セキュリティレベル：中の場合、F5アタック、悪意あるクローラの対処を「ログ出力なし」にするように修正。
		//
		$ret = $this->db->selectExecute("config", array("conf_modid"=> _SYS_CONF_MODID, "conf_name"=> "security_level", "conf_value"=> _SECURITY_LEVEL_MEDIUM));
		if($ret !== false && count($ret) > 0) {
			$this->db->updateExecute("config", array("conf_value" => 0), array("conf_modid"=> _SYS_CONF_MODID, "conf_name" => "dos_f5action"));
			$this->db->updateExecute("config", array("conf_value" => 0), array("conf_modid"=> _SYS_CONF_MODID, "conf_name" => "dos_craction"));
		}

		return true;
	}
}
?>
