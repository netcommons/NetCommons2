<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iCalインポートチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_Import extends Validator
{
	/**
	 * validate実行
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$reservationView =& $container->getComponent("reservationView");
		$csvMain =& $commonMain->registerClass(WEBAPP_DIR."/components/csv/Main.class.php", "Csv_Main", "csvMain");
		$dbObject =& $container->getComponent("DbObject");

		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR."/components/uploads/Action.class.php", "Uploads_Action", "uploadsAction");

		//アップロードファイルのチェック
		$filelist = $uploadsAction->uploads(_OFF);
    	if (!$filelist || $filelist[0]["file_name"] == "" || $filelist[0]["error_mes"] != "") {
    		return _INVALID_INPUT;
    	}
		$file = FILEUPLOADS_DIR."reservation/".$filelist[0]['physical_file_name'];
    	$handle = fopen($file, "r");
    	if ($handle == false) {
    		return RESERVATION_ERR_FILE_OPEN;
    	}

		$attributes["undo_import"] = intval($attributes["undo_import"]);
		$attributes["title_duplication"] = intval($attributes["title_duplication"]);

		//利用できるグループのチェック
		$attributes["reserve_room_id"] = intval($attributes["reserve_room_id"]);
		if ($attributes["reserve_room_id"] != 0 && !in_array($attributes["reserve_room_id"], $attributes["allow_add_rooms"])) {
    		return _INVALID_INPUT;
		}

		// ヘッダチェック
		$row_csv_header = $csvMain->fgets($handle);
		if (empty($row_csv_header)) {
			$uploadsAction->delUploadsById($filelist[0]["upload_id"]);
			return RESERVATION_ERR_FILE_FORMAT;
		}
		$header_format = explode("|", RESERVATION_IMPORT_FORMAT);
		foreach ($header_format as $i=>$val) {
			if (mb_convert_encoding($row_csv_header[$i], "UTF-8", "SJIS") != $val) {
				$uploadsAction->delUploadsById($filelist[0]["upload_id"]);
				return RESERVATION_ERR_FILE_FORMAT;
			}
		}

		$total = 0;
		$error = array();
		$import = array();
		$duplication_time = array();
		while (($data = $csvMain->fgets($handle)) !== FALSE) {
			//SJISからUTF-8に変換
			for ($c=0; $c<count($header_format); $c++) {
				$data[$c] = mb_convert_encoding($data[$c], "UTF-8", "SJIS");
			}
			$total++;

			if ($data[1] == _ON) {
				$data[3] = substr($attributes["location"]["start_time"],8);
				$data[4] = substr($attributes["location"]["end_time"],8);
			}

			$title = $data[0];
			$reserve_date = $data[2];
			$start_time = $data[3];
			$end_time = $data[4];

			//件名、開始時間、終了時間の必須チェック
			//件名チェック
			if (empty($title) || $title == "null") {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(_REQUIRED, $header_format[0]);
				continue;
			}

			$data[1] = ($data[1] == "null" ? _OFF : intval($data[1]));
			//予約日チェック
			if (empty($reserve_date) || $reserve_date == "null") {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(_REQUIRED, $header_format[2]);
				continue;
			}
			//開始時間チェック
			if (empty($start_time) || $start_time == "null") {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(_REQUIRED, $header_format[3]);
				continue;
			}
			//終了時間チェック
			if (empty($end_time) || $end_time == "null") {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(_REQUIRED, $header_format[4]);
				continue;
			}

			//件名、連絡先、詳細の文字数チェック
			//件名チェック
			if (strlen(bin2hex($title)) / 2 <= _VALIDATOR_TITLE_LEN) {
			} else {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total).sprintf(_MAXLENGTH_ERROR,$header_format[0],_VALIDATOR_TITLE_LEN);
				continue;
			}
			//連絡先チェック
			if (strlen(bin2hex($data[5])) / 2 <= _VALIDATOR_TITLE_LEN) {
			} else {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total).sprintf(_MAXLENGTH_ERROR,$header_format[5],_VALIDATOR_TITLE_LEN);
				continue;
			}
			//詳細チェック
			if (strlen(bin2hex($data[6])) / 2 <= _VALIDATOR_TEXTAREA_LEN) {
			} else {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total).sprintf(_MAXLENGTH_ERROR,$header_format[6],_VALIDATOR_TEXTAREA_LEN);
				continue;
			}

			//開始時間、終了時間の日時チェック
			if (!preg_match("/^[0-9]{8}$/isu", $reserve_date)) {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(_INVALID_DATE, $header_format[2]);
				continue;
			}
			if (!preg_match("/^[0-9]{6}$/isu", $start_time)) {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(RESERVATION_INVALID_TIME, $header_format[3]);
				continue;
			}
			if (!preg_match("/^[0-9]{6}$/isu", $end_time)) {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(RESERVATION_INVALID_TIME, $header_format[4]);
				continue;
			}
			if (intval(substr($reserve_date,4,2)) >= 1 && intval(substr($reserve_date,4,2)) <= 12 &&
				intval(substr($reserve_date,6,2)) >= 1 && intval(substr($reserve_date,6,2)) <= 31 &&
				intval(substr($reserve_date,8,2)) >= 0 && intval(substr($reserve_date,8,2)) <= 23) {
			} else {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(_INVALID_DATE, $header_format[2]);
				continue;
			}
			if (intval(substr($start_time,0,2)) >= 0 && intval(substr($start_time,0,2)) <= 23 &&
				intval(substr($start_time,2,2)) >= 0 && intval(substr($start_time,2,2)) <= 59 &&
				intval(substr($start_time,4,2)) >= 0 && intval(substr($start_time,4,2)) <= 59) {
			} else {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(RESERVATION_INVALID_TIME, $header_format[3]);
				continue;
			}
			if (intval(substr($end_time,0,2)) >= 0 && intval(substr($end_time,0,2)) <= 23 &&
				intval(substr($end_time,2,2)) >= 0 && intval(substr($end_time,2,2)) <= 59 &&
				intval(substr($end_time,4,2)) >= 0 && intval(substr($end_time,4,2)) <= 59
				|| intval(substr($end_time,0,2)) == 24 && intval(substr($end_time,2,2)) == 0 && intval(substr($end_time,4,2)) == 0) {
			} else {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). sprintf(RESERVATION_INVALID_TIME, $header_format[4]);
				continue;
			}

			//From-Toのチェック
			if ($start_time >= $end_time) {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). RESERVATION_ERR_RESERVE_FROM_TO_DATE;
				continue;
			}

			//利用時間のチェック
			if (!$reservationView->checkReserveTime($reserve_date.$start_time, $reserve_date.$end_time)) {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). RESERVATION_ERR_RESERVE_FROM_TO_DATE;
				continue;
			}

			if ($attributes["location"]["duplication_flag"] == _ON) {
				return true;
			}

			//重複チェック(同一ファイル内のチェック)
			if ($attributes["title_duplication"] == _ON &&
				isset($duplication_time[$reserve_date]) && isset($duplication_time[$reserve_date][$start_time.$end_time]) &&
				$duplication_time[$reserve_date][$start_time.$end_time]["title"] == $data[0]) {

				continue;
			}
			if (isset($duplication_time[$reserve_date])) {
				$duplication_flag = false;
				foreach ($duplication_time[$reserve_date] as $time_full => $dupli_data) {
					if ($dupli_data["start_time"] <= $start_time && $start_time < $dupli_data["end_time"] ||
						$dupli_data["start_time"] < $end_time && $end_time <= $dupli_data["end_time"] ||
						$start_time <= $dupli_data["start_time"] && $dupli_data["start_time"] < $end_time) {

						$duplication_flag = true;
						break;
					}
				}
				if ($duplication_flag) {
					$error[] = sprintf(RESERVATION_ERR_NUM, $total). RESERVATION_ERR_RESERVE_DUPLICATION;
					continue;
				}
			}

			//全て削除がONで利用するグループを指定しない場合、以下のチェックをしない
			if ($attributes["undo_import"] == _ON && $attributes["reserve_room_id"] == 0) {
				$duplication_time[$reserve_date][$start_time.$end_time] = array("start_time"=>$start_time, "end_time"=>$end_time, "title"=>$title);
				$import[] = $data;
				continue;
			}

			//重複チェック(DB内のチェック)
			$sql = "SELECT title, start_time_full, end_time_full" .
					" FROM {reservation_reserve}" .
					" WHERE location_id = ?";
			$sql .= " AND (";
			$sql .= "start_time_full >= ? AND start_time_full < ?" .
					" OR " .
					"end_time_full > ? AND end_time_full <= ?" .
					" OR " .
					"start_time_full <= ? AND end_time_full > ?";
			$sql .= ")";

			$start_time_full = timezone_date($reserve_date.$start_time);
			$end_time_full = timezone_date($reserve_date.$end_time);

			$params = array();
			$params[] = $attributes["location"]["location_id"];
			$params[] = $start_time_full;
			$params[] = $end_time_full;
			$params[] = $start_time_full;
			$params[] = $end_time_full;
			$params[] = $start_time_full;
			$params[] = $start_time_full;

			$funcParams = array(
				"start_time_full" => $start_time_full,
				"end_time_full" => $end_time_full,
				"title" => $title,
				"title_duplication" => $attributes["title_duplication"]
			);
			$result = $dbObject->execute($sql, $params, null, null, true, array($this,"_checkDuplication"), $funcParams);
			if ($result === _OFF) {
				if (!isset($duplication_time[$reserve_date])) {
					$duplication_time[$reserve_date] = array();
				}
				$duplication_time[$reserve_date][$start_time.$end_time] = array("start_time"=>$start_time, "end_time"=>$end_time, "title"=>$title);
				$import[] = $data;
			} elseif ($result === _ON) {
				continue;
			} elseif ($result === true) {
				$error[] = sprintf(RESERVATION_ERR_NUM, $total). RESERVATION_ERR_RESERVE_DUPLICATION;
				continue;
			} else {
				$dbObject->addError();
				return false;
			}
		}

		//エラーがあれば出力する
		if (!empty($error)) {
			$uploadsAction->delUploadsById($filelist[0]["upload_id"]);
			return implode("<br />", $error);
		}

		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();

		BeanUtils::setAttributes($action, array("import"=>$import));
		BeanUtils::setAttributes($action, $attributes);

		$uploadsAction->delUploadsById($filelist[0]["upload_id"]);
	}

	/**
	 * 予約チェック(重複)
	 *
	 * @access	private
	 */
	function _checkDuplication(&$recordSet, &$funcParams)
	{
		while ($row = $recordSet->fetchRow()) {
			if ($funcParams["title_duplication"] == _ON &&
				$row["start_time_full"] == $funcParams["start_time_full"] &&
				$row["end_time_full"] == $funcParams["end_time_full"] &&
				$row["title"] == $funcParams["title"]) {

				return _ON;
			}
			return true;
		}
		return _OFF;
	}

}
?>
