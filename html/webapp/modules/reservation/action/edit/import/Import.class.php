<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設の追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Action_Edit_Import extends Action
{
	//値を受け取るため
	var $reserve_room_id = null;
	var $undo_import = null;

	//validatorから受け取るため
	var $import = null;
	var $location = null;

	// 使用コンポーネントを受け取るため
	var $reservationAction = null;
	var $db = null;
	var $request = null;

	/**
	 * execute処理
	 *
	 * @access  public
	 */
	function execute()
	{
		//UNDO処理
		if ($this->undo_import == _ON) {
			//グループ指定の場合、削除対象の予約詳細IDを取得する
			if ($this->reserve_room_id != 0) {
				$sql = "SELECT reserve_details_id".
						" FROM {reservation_reserve}".
						" WHERE location_id = ?".
						" AND room_id = ?";
				$params = array(
					"location_id" => $this->location["location_id"],"room_id" => $this->reserve_room_id
				);
				$reserveDetailsIDs = $this->db->execute($sql, $params, null, null, true, array($this,"_makeIdArray"));
				if ($reserveDetailsIDs === false) {
					$this->db->addError();
					return 'error';
				}
			}
			//予約データ削除
			if ($this->reserve_room_id != 0) {
				$sql = "DELETE FROM {reservation_reserve}".
						" WHERE location_id = ?".
						" AND room_id = ?";
				$params = array(
					"location_id" => $this->location["location_id"],
					"room_id" => $this->reserve_room_id
				);
			} else {
				$sql = "DELETE FROM {reservation_reserve}".
						" WHERE location_id = ?";
				$params = array(
					"location_id" => $this->location["location_id"]
				);
			}
			$result = $this->db->execute($sql, $params);
			if ($result === false) {
				$this->db->addError();
				return 'error';
			}
			//予約詳細データ削除
			if ($this->reserve_room_id != 0) {
				if (!empty($reserveDetailsIDs)) {
					$sql = "SELECT reserve_details_id".
							" FROM {reservation_reserve}".
							" WHERE reserve_details_id IN (".implode(",",$reserveDetailsIDs).")";
					$params = array();
					$noDeleteIDs = $this->db->execute($sql, $params, null, null, true, array($this,"_makeIdArray"));
					if ($noDeleteIDs === false) {
						$this->db->addError();
						return 'error';
					}

					$deleteIDs = array_diff($reserveDetailsIDs, $noDeleteIDs);
					if (!empty($deleteIDs)) {
						$sql = "DELETE FROM {reservation_reserve_details}".
								" WHERE reserve_details_id IN (".implode(",",$deleteIDs).")";
						$params = array();
						$result = $this->db->execute($sql, $params);
						if ($result === false) {
							$this->db->addError();
							return 'error';
						}
					}
				}
			} else {
				$sql = "DELETE FROM {reservation_reserve_details}".
						" WHERE location_id = ?";
				$params = array(
					"location_id" => $this->location["location_id"]
				);
				$result = $this->db->execute($sql, $params);
				if ($result === false) {
					$this->db->addError();
					return 'error';
				}
			}
		}

		//予約のインポート処理
		if (!empty($this->import)) {
			foreach ($this->import as $i=>$data) {
				$this->request->setParameter("details_flag", _ON);
				$this->request->setParameter("entry_calendar", _OFF);
				$this->request->setParameter("title", $data[0]);
				$this->request->setParameter("icon_name", "");
				$this->request->setParameter("allday_flag", $data[1]);
				$this->request->setParameter("start_time_full", $data[2].$data[3]);
				$this->request->setParameter("end_time_full", $data[2].$data[4]);
				$this->request->setParameter("contact", $data[5]);
				$this->request->setParameter("description", $data[6]);
				$this->request->setParameter("timezone_offset", $this->location["timezone_offset"]);

				$result = $this->reservationAction->addReserve();
				if (!$result) {
					return 'error';
				}
			}
		}

		return 'success';
	}

	/**
	 * データ配列生成
	 *
	 * @param recordSet adodb object
	 *
     * @return array	ID配列
	 * @access	private
	 */
	function &_makeIdArray(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[] = $row["reserve_details_id"];
		}

		return $result;
	}

}
?>