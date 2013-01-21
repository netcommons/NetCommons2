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
class Quiz_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $dbObject = null;

	function execute()
	{

		// quizにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."quiz` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// quiz_answerにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."quiz_answer` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_answer` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// quiz_blockにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."quiz_block` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_quiz_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "quiz_id") {
				$alter_table_quiz_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_quiz_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_block` ADD INDEX ( `quiz_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_block` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// quiz_choiceにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."quiz_choice` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_choice` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// quiz_questionにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."quiz_question` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_room_id_flag = true;
		$alter_table_quiz_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "quiz_id") {
				$alter_table_quiz_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if(!$alter_table_quiz_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_question`
						DROP INDEX `quiz_id` ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_question` ADD INDEX `quiz_id_2` ( `quiz_id`, `question_sequence` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_question` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		// quiz_summaryにindexを追加
		$sql = "SHOW INDEX FROM `".$this->dbObject->getPrefix()."quiz_summary` ;";
		$results = $this->dbObject->execute($sql);
		if($results === false) return false;
		$alter_table_quiz_id_flag = true;
		$alter_table_room_id_flag = true;
		foreach($results as $result) {
			if(isset($result['Key_name']) && $result['Key_name'] == "quiz_id") {
				$alter_table_quiz_id_flag = false;
			}
			if(isset($result['Key_name']) && $result['Key_name'] == "room_id") {
				$alter_table_room_id_flag = false;
			}
		}
		if(!$alter_table_quiz_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_summary`
						DROP INDEX `quiz_id` ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;

			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_summary` ADD INDEX `quiz_id_2` ( `quiz_id`, `insert_user_id` ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}
		if($alter_table_room_id_flag) {
			$sql = "ALTER TABLE `".$this->dbObject->getPrefix()."quiz_summary` ADD INDEX ( `room_id`  ) ;";
			$result = $this->dbObject->execute($sql);
			if($result === false) return false;
		}

		return true;
	}
}
?>