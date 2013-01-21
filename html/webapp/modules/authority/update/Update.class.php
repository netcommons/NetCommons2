<?php
/**
 * モジュールアップデートクラス
 * 　　マイポータル使用可否追加
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaColumns = $adodb->MetaColumns($this->db->getPrefix()."authorities");
		if(!isset($metaColumns["ALLOW_VIDEO"])) {
			$sql = "ALTER TABLE `".$this->db->getPrefix()."authorities`
						ADD `allow_video` TINYINT(1) NOT NULL DEFAULT '0' AFTER `allow_attachment`;";
			$result = $this->db->execute($sql);
			if($result === false) return false;

			// ベース権限が管理者のみONへ
			$where_params = array("user_authority_id>" => _AUTH_MODERATE);
			$this->db->updateExecute("authorities", array("allow_video" => _ON), $where_params);
		}

		//モジュールIDを抽出
		$bindValues = array(
			'action_name' => 'cleanup_view_main_init'
		);
		$sql = "SELECT module_id "
				. "FROM {modules} "
				. "WHERE action_name = ?;";
		$modules = $this->db->execute($sql, $bindValues);
		if ($modules === false) {
			$this->db->addError();
			return false;
		}
		if (!empty($modules)) {
			//管理者以外のcleanup権限を削除
			$bindValues = array(
				'authority_id' => _AUTH_ADMIN,
				'module_id' => $modules[0]['module_id']
			);
			$sql = "DELETE FROM {authorities_modules_link} "
					. "WHERE authority_id != ? "
					. "AND module_id = ?;";
			if (!$this->db->execute($sql, $bindValues)) {
				$this->db->addError();
				return false;
			}
		}

		return true;
	}
}
?>
