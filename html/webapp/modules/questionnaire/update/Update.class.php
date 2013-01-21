<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュールアップデートクラス
 *   質問内容、説明にアップロードされた画像、添付ファイルのガベージフラグをOFFにする
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Questionnaire_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $dbObject = null;

	function execute()
	{

		// questionnaireにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."questionnaire` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// questionnaire_answerにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."questionnaire_answer` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_answer` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// questionnaire_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."questionnaire_block` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_questionnaire_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "questionnaire_id") {
				$alter_table_questionnaire_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_questionnaire_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_block` ADD INDEX ( `questionnaire_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// questionnaire_choiceにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."questionnaire_choice` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_choice` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// questionnaire_questionにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."questionnaire_question` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		$alter_table_questionnaire_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "questionnaire_id") {
				$alter_table_questionnaire_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if(!$alter_table_questionnaire_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_question`
						DROP INDEX `questionnaire_id` ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_question` ADD INDEX `questionnaire_id_2` ( `questionnaire_id`, `question_sequence` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_question` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// questionnaire_summaryにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."questionnaire_summary` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_questionnaire_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "questionnaire_id") {
				$alter_table_questionnaire_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if(!$alter_table_questionnaire_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_summary`
						DROP INDEX `questionnaire_id` ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_summary` ADD INDEX `questionnaire_id_2` ( `questionnaire_id`, `insert_user_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."questionnaire_summary` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		$sql = "SELECT COUNT(upload_id) "
				. "FROM {uploads} "
				. "WHERE garbage_flag = ? "
				. "AND file_path = ?";
		$input = array(
			_ON,
			'questionnaire/'
		);
		$counts = $this->dbObject->execute($sql, $input, 1, null, false);
		if (empty($counts[0][0])) {
			return true;
		}

		$sql = "UPDATE {uploads} SET "
					. "garbage_flag = ? "
				. "WHERE file_path = ?";
		$input = array(
			_OFF,
			'questionnaire/'
		);
		$result = $this->dbObject->execute($sql, $input);
		if ($result === false) {
			return false;
		}

		return true;
	}
}
?>