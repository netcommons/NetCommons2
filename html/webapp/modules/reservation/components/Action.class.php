<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 施設予約登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var reservationViewオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_reservationView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Reservation_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_reservationView =& $this->_container->getComponent("reservationView");
	}

	/**
	 * カテゴリーを登録する
	 *
	 * @access  public
	 */
	function setCategory()
	{
		$site_id = $this->_session->getParameter("_site_id");
		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");
		$time = timezone_date();

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($actionName == "reservation_action_edit_addblock") {
			$params = array(
				"category_name" => "",
				"display_sequence" => 1,
				"insert_time" => $time,
				"insert_site_id" => $site_id,
				"insert_user_id" => $user_id,
				"insert_user_name" => $user_name,
				"update_time" => $time,
				"update_site_id" => $site_id,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name
			);

			$result = $this->_db->insertExecute("reservation_category", $params, false, "category_id");
			if ($result === false) {
				return false;
			}
		} elseif ($actionName == "reservation_action_edit_category_add") {
			$display_sequence = $this->_reservationView->getCategorySequence();

			$params = array(
				"category_name" => $this->_request->getParameter("category_name"),
				"display_sequence" => $display_sequence + 1,
				"insert_time" => $time,
				"insert_site_id" => $site_id,
				"insert_user_id" => $user_id,
				"insert_user_name" => $user_name,
				"update_time" => $time,
				"update_site_id" => $site_id,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name
			);

			$result = $this->_db->insertExecute("reservation_category", $params, false, "category_id");
			if ($result === false) {
				return false;
			}
		} elseif ($actionName == "reservation_action_edit_category_rename") {
			$params = array(
				"category_name" => $this->_request->getParameter("category_name"),
				"update_time" => $time,
				"update_site_id" => $site_id,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name
			);
			$where_params = array(
				"category_id" => $this->_request->getParameter("category_id")
			);
			$result = $this->_db->updateExecute("reservation_category", $params, $where_params, false);
			if($result === false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * カテゴリーを登録する
	 *
	 * @access  public
	 */
	function setCategorySequence()
	{
		$site_id = $this->_session->getParameter("_site_id");
		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");
		$time = timezone_date();

		//データ取得
		$position = $this->_request->getParameter("position");
		$drag_category = $this->_request->getParameter("drag_category");
		$drop_category = $this->_request->getParameter("drop_category");

		//移動元デクリメント
		//前詰め処理
		$sequence_param = array(
			"display_sequence"=> $drag_category["display_sequence"]
		);
		$result = $this->_db->seqExecute("reservation_category", array(), $sequence_param);
		if ($result === false) {
			return false;
		}

		if ($drag_category["display_sequence"] > $drop_category["display_sequence"]) {
			if ($position == "top") {
				$drop_display_sequence = $drop_category["display_sequence"];
			} else {
				$drop_display_sequence = $drop_category["display_sequence"] + 1;
			}
		} else {
			if ($position == "top") {
				$drop_display_sequence = $drop_category["display_sequence"] - 1;
			} else {
				$drop_display_sequence = $drop_category["display_sequence"];
			}
		}

		//移動先インクリメント
		$sequence_param = array(
			"display_sequence"=> $drop_display_sequence
		);
		$result = $this->_db->seqExecute("reservation_category", array(), $sequence_param, 1);
		if($result === false) {
			return false;
		}

		//更新
		$params = array(
			"display_sequence" => $drop_display_sequence,
			"update_time" => $time,
			"update_site_id" => $site_id,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name
		);
		$where_params = array(
			"category_id" => $this->_request->getParameter("drag_category_id")
		);
		$result = $this->_db->updateExecute("reservation_category", $params, $where_params, false);
		if($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * カテゴリーを登録する
	 *
	 * @access  public
	 */
	function deleteCategory()
	{
		$category_id = $this->_request->getParameter("category_id");

		$category = $this->_db->selectExecute("reservation_category", array("category_id"=>$category_id));
		if ($category === false || !isset($category[0])) {
			return false;
		}
		//前詰め処理
		$sequence_param = array(
			"display_sequence" => $category[0]["display_sequence"]
		);
		$result = $this->_db->seqExecute("reservation_category", array(), $sequence_param);
		if ($result === false) {
			return false;
		}
		//カテゴリーなしに変更処理
		$location_list = $this->_db->selectExecute("reservation_location", array("category_id"=>$category_id));
		if ($location_list === false) {
			return false;
		}
		if (!empty($location_list)) {
			$non_category = $this->_reservationView->getNonCategory();
			$display_sequence = $this->_reservationView->getLocationSequence($non_category["category_id"]);

			$site_id = $this->_session->getParameter("_site_id");
			$user_id = $this->_session->getParameter("_user_id");
			$user_name = $this->_session->getParameter("_handle");
			$time = timezone_date();

			foreach ($location_list as $i=>$location) {
				$display_sequence++;
				$params = array(
					"category_id" => $non_category["category_id"],
					"display_sequence" => $display_sequence,
					"update_time" => $time,
					"update_site_id" => $site_id,
					"update_user_id" => $user_id,
					"update_user_name" => $user_name
				);
				$result = $this->_db->updateExecute("reservation_location", $params, array("location_id"=>$location["location_id"]), false);
				if ($result === false) {
					return false;
				}
			}
		}
		//削除
		$result = $this->_db->deleteExecute("reservation_category", array("category_id"=>$category_id));
		if($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * ブロックを登録する
	 *
	 * @return  integer	カテゴリー件数
	 * @access  public
	 */
	function setBlock()
	{
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($actionName == "reservation_action_edit_addblock") {
			$default = $this->_reservationView->getDefaultBlock();
			$params = array(
				"block_id" => $this->_request->getParameter("block_id"),
				"display_type" => $default["display_type"],
				'display_timeframe'=> $default['display_timeframe'],
				"display_start_time" => $default["display_start_time"],
				"display_interval" => $default["display_interval"],
				"category_id" => $default["category_id"],
				"location_id" => $default["location_id"]
			);
			$result = $this->_db->insertExecute("reservation_block", $params, true);
			if ($result === false) {
				return false;
			}
		} elseif ($actionName == "reservation_action_edit_style") {
			$params = array(
				"display_type" => intval($this->_request->getParameter("display_type")),
				'display_timeframe' => intval($this->_request->getParameter('display_timeframe')),
				"display_start_time" => $this->_request->getParameter("display_start_time"),
				"display_interval" => intval($this->_request->getParameter("display_interval")),
				"category_id" => $this->_request->getParameter("category_id"),
				"location_id" => $this->_request->getParameter("location_id")
			);
			$where_params = array(
				"block_id" => $this->_request->getParameter("block_id"),
			);
			$result = $this->_db->updateExecute("reservation_block", $params, $where_params, true);
			if ($result === false) {
				return false;
			}

		// 2013.02.12 bugfix 施設=0件か、表示設定方法に削除した施設を指定していた時は、表示方法変更「表示方法」を「日」にアップデート
		} elseif ($actionName == "reservation_action_edit_location_delete") {

			// 初期設定
			$default = $this->_reservationView->getDefaultBlock();

			// 施設件数
			$location_count = $this->_reservationView->getCountLocation();

			// 表示設定方法取得 by location_id
			$block_by_location_id = $this->_reservationView->getBlockByLocationId();

			// 施設=0件か、表示設定方法に削除した施設を指定していた
			if ( $location_count == 0 || !empty($block_by_location_id) ) {
				$params = array(
					"display_type" => $default["display_type"],
					"location_id" => $default["location_id"]
				);
				$where_params = array(
					"block_id" => $this->_request->getParameter("block_id"),
				);

				// 更新
				$result = $this->_db->updateExecute("reservation_block", $params, $where_params, true);
				if ($result === false) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 施設を登録する
	 *
	 * @access  public
	 */
	function addLocation()
	{
		$site_id = $this->_session->getParameter("_site_id");
		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");
		$time = timezone_date();

		$category_id = $this->_request->getParameter("category_id");
		$display_sequence = $this->_reservationView->getLocationSequence($category_id);
		$allroom_flag = intval($this->_request->getParameter("allroom_flag"));
		$description = $this->_request->getParameter("description");

		$params = array(
			"category_id" => $category_id,
			"location_name" => $this->_request->getParameter("location_name"),
			"active_flag" => _ON,
			"add_authority" => $this->_request->getParameter("add_authority"),
			"time_table" => $this->_request->getParameter("time_table"),
			"start_time" => timezone_date($this->_request->getParameter("start_time"), true, "YmdHis"),
			"end_time" => timezone_date($this->_request->getParameter("end_time"), true, "YmdHis"),
			"timezone_offset" => $this->_request->getParameter("timezone_offset"),
			"duplication_flag" => intval($this->_request->getParameter("duplication_flag")),
			"use_private_flag" => intval($this->_request->getParameter("use_private_flag")),
			"use_auth_flag" => intval($this->_request->getParameter("use_auth_flag")),
			"allroom_flag" => $allroom_flag,
			"display_sequence" => $display_sequence + 1,
			"insert_time" => $time,
			"insert_site_id" => $site_id,
			"insert_user_id" => $user_id,
			"insert_user_name" => $user_name,
			"update_time" => $time,
			"update_site_id" => $site_id,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name
		);
		$location_id = $this->_db->insertExecute("reservation_location", $params, false, "location_id");
		if ($location_id === false) {
			return false;
		}
		$params = array(
			"location_id" => $location_id,
			"contact" => $this->_request->getParameter("contact"),
			"description" => $description
		);
		$result = $this->_db->insertExecute("reservation_location_details", $params, false);
		if ($result === false) {
			return false;
		}

		$commonMain =& $this->_container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$upload_id_arr = $uploadsAction->getUploadId($description);
		if (!empty($upload_id_arr)) {
			$params = array(
				"room_id" => 0
			);
			$where_params = array(
				"upload_id IN (". implode(",", $upload_id_arr) .")" => null
			);
			$result = $uploadsAction->updUploads($params, $where_params);
			if ($result === false) {
				return false;
			}
		}

		$select_room = $this->_request->getParameter("select_room");
		if ($allroom_flag == _ON || empty($select_room)) {
			return true;
		}

		foreach ($select_room as $i=>$room_id) {
			$params = array(
				"location_id" => $location_id,
				"room_id" => intval($room_id)
			);
			$result = $this->_db->insertExecute("reservation_location_rooms", $params, true);
			if ($result === false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 施設を登録する
	 *
	 * @access  public
	 */
	function updateLocation()
	{
		$site_id = $this->_session->getParameter("_site_id");
		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");
		$time = timezone_date();

		$location = $this->_request->getParameter("location");
		$old_select_rooms = $this->_request->getParameter("select_rooms");

		$location_id = $this->_request->getParameter("location_id");
		$category_id = $this->_request->getParameter("category_id");
		$display_sequence = $this->_reservationView->getLocationSequence($category_id);
		$allroom_flag = intval($this->_request->getParameter("allroom_flag"));
		$description = $this->_request->getParameter("description");

		$new_select_room = $this->_request->getParameter("select_room");
		if (empty($new_select_room)) {
			$new_select_room = array();
		}

		if ($allroom_flag == _OFF) {
			$diff_array = array_diff($old_select_rooms, $new_select_room);
			if (!empty($diff_array) && count($diff_array) > 0) {
				$params = array(
					"location_id" => $location_id,
					"room_id IN (".implode(",", $diff_array).")" => null
				);
				$result = $this->_db->deleteExecute("reservation_location_rooms", $params);
				if($result === false) {
					return false;
				}
			}

			$diff_array = array_diff($new_select_room, $old_select_rooms);
			foreach ($diff_array as $i=>$room_id) {
				$params = array(
					"location_id" => $location_id,
					"room_id" => intval($room_id)
				);
				$result = $this->_db->insertExecute("reservation_location_rooms", $params, true);
				if ($result === false) {
					return false;
				}
			}
		} else {
			$result = $this->_db->deleteExecute("reservation_location_rooms", array("location_id"=>$location_id));
			if($result === false) {
				return false;
			}
		}

		if ($location["category_id"] != $category_id) {
			//移動元デクリメント
			//前詰め処理
			$params = array(
				"category_id" => $location["category_id"]
			);
			$sequence_param = array(
				"display_sequence" => $location["display_sequence"]
			);
			$result = $this->_db->seqExecute("reservation_location", $params, $sequence_param);
			if ($result === false) {
				return false;
			}

			//移動元インクリメント
			$display_sequence = $this->_reservationView->getLocationSequence($category_id);
			$display_sequence++;
		} else {
			$display_sequence = $location["display_sequence"];
		}

		$params = array(
			"category_id" => $category_id,
			"location_name" => $this->_request->getParameter("location_name"),
			"active_flag" => _ON,
			"add_authority" => $this->_request->getParameter("add_authority"),
			"time_table" => $this->_request->getParameter("time_table"),
			"start_time" => timezone_date($this->_request->getParameter("start_time"), true, "YmdHis"),
			"end_time" => timezone_date($this->_request->getParameter("end_time"), true, "YmdHis"),
			"timezone_offset" => $this->_request->getParameter("timezone_offset"),
			"duplication_flag" => intval($this->_request->getParameter("duplication_flag")),
			"use_private_flag" => intval($this->_request->getParameter("use_private_flag")),
			"use_auth_flag" => intval($this->_request->getParameter("use_auth_flag")),
			"allroom_flag" => $allroom_flag,
			"display_sequence" => $display_sequence,
			"update_time" => $time,
			"update_site_id" => $site_id,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name
		);
		$where_params = array(
			"location_id" => $location_id
		);
		$result = $this->_db->updateExecute("reservation_location", $params, $where_params, false);
		if ($result === false) {
			return false;
		}
		$params = array(
			"contact" => $this->_request->getParameter("contact"),
			"description" => $description
		);
		$result = $this->_db->updateExecute("reservation_location_details", $params, $where_params, false);
		if ($result === false) {
			return false;
		}

		$commonMain =& $this->_container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");

		$upload_id_arr = $uploadsAction->getUploadId($description);
		if (!empty($upload_id_arr)) {
			$params = array(
				"room_id" => 0
			);
			$where_params = array(
				"upload_id IN (". implode(",", $upload_id_arr) .")" => null
			);
			$result = $uploadsAction->updUploads($params, $where_params);
			if ($result === false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 施設を登録する
	 *
	 * @access  public
	 */
	function renameLocation()
	{
		$site_id = $this->_session->getParameter("_site_id");
		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");
		$time = timezone_date();

		$params = array(
			"location_name" => $this->_request->getParameter("location_name"),
			"update_time" => $time,
			"update_site_id" => $site_id,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name
		);
		$where_params = array(
			"location_id" => $this->_request->getParameter("location_id")
		);
		$result = $this->_db->updateExecute("reservation_location", $params, $where_params, false);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * 施設を削除する
	 *
	 * @access  public
	 */
	function deleteLocation()
	{
		$location_id = $this->_request->getParameter("location_id");

		$location = $this->_db->selectExecute("reservation_location", array("location_id"=>$location_id));
		if ($location === false || !isset($location[0])) {
			return false;
		}
		//前詰め処理
		$params = array(
			"category_id" => $location[0]["category_id"]
		);
		$sequence_param = array(
			"display_sequence" => $location[0]["display_sequence"]
		);
		$result = $this->_db->seqExecute("reservation_location", $params, $sequence_param);
		if ($result === false) {
			return false;
		}

		$result = $this->_db->deleteExecute("reservation_location", array("location_id"=>$location_id));
		if($result === false) {
			return false;
		}
		$result = $this->_db->deleteExecute("reservation_location_details", array("location_id"=>$location_id));
		if($result === false) {
			return false;
		}
		$result = $this->_db->deleteExecute("reservation_location_rooms", array("location_id"=>$location_id));
		if($result === false) {
			return false;
		}
		$result = $this->_db->deleteExecute("reservation_reserve", array("location_id"=>$location_id));
		if($result === false) {
			return false;
		}
		$result = $this->_db->deleteExecute("reservation_reserve_details", array("location_id"=>$location_id));
		if($result === false) {
			return false;
		}

		// 2013.02.12 bugfix 施設=0件か、表示設定方法に削除した施設を指定していた時は、表示方法変更「表示方法」を「日」にアップデート
		$result = $this->setBlock();
		if($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 施設を登録する
	 *
	 * @access  public
	 */
	function setLocationSequence()
	{
		$site_id = $this->_session->getParameter("_site_id");
		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");
		$time = timezone_date();

		//データ取得
		$position = $this->_request->getParameter("position");
		$drag_location = $this->_request->getParameter("drag_location");
		$drop_category = $this->_request->getParameter("drop_category");
		$drop_location = $this->_request->getParameter("drop_location");

		$drag_category_id = $drag_location["category_id"];

		if ($position == "inside") {
			$drop_category_id = $drop_category["category_id"];
		} else {
			$drop_category_id = $drop_location["category_id"];
		}

		//移動元デクリメント
		//前詰め処理
		$params = array(
			"category_id" => $drag_category_id
		);
		$sequence_param = array(
			"display_sequence" => $drag_location["display_sequence"]
		);
		$result = $this->_db->seqExecute("reservation_location", $params, $sequence_param);
		if ($result === false) {
			return false;
		}

		if ($position == "inside") {
			$drop_display_sequence = $this->_reservationView->getLocationSequence($drop_category_id);
			if ($drop_display_sequence === false) {
				return false;
			}
			if ($drag_category_id != $drop_category_id) {
				$drop_display_sequence = $drop_display_sequence + 1;
			}
		} else {
			if ($drag_category_id == $drop_category_id) {
				if ($drag_location["display_sequence"] > $drop_location["display_sequence"]) {
					if ($position == "top") {
						$drop_display_sequence = $drop_location["display_sequence"];
					} else {
						$drop_display_sequence = $drop_location["display_sequence"] + 1;
					}
				} else {
					if ($position == "top") {
						$drop_display_sequence = $drop_location["display_sequence"] - 1;
					} else {
						$drop_display_sequence = $drop_location["display_sequence"];
					}
				}
			} else {
				if ($position == "top") {
					$drop_display_sequence = $drop_location["display_sequence"];
				} else {
					$drop_display_sequence = $drop_location["display_sequence"] + 1;
				}
			}
			if ($drop_display_sequence == 0) $drop_display_sequence = 1;

			//移動先インクリメント
			$params = array(
				"category_id" => $drop_category_id
			);
			$sequence_param = array(
				"display_sequence"=> $drop_display_sequence
			);
			$result = $this->_db->seqExecute("reservation_location", $params, $sequence_param, 1);
			if ($result === false) {
				return false;
			}
		}
		//更新
		$params = array(
			"category_id" => $drop_category_id,
			"display_sequence" => $drop_display_sequence
		);
		$where_params = array(
			"location_id" => $drag_location["location_id"]
		);
		$result = $this->_db->updateExecute("reservation_location", $params, $where_params, true);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * 時間枠を登録する
	 *
	 * @access  public
	 */
    function setTimeframe()
    {
		$timeframe_id = $this->_request->getParameter('timeframe_id');
		$timezone_offset = $this->_request->getParameter('timezone_offset');
		$start_time = $this->_request->getParameter('start_time');
		$end_time = $this->_request->getParameter('end_time');

		$commonMain =& $this->_container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");

		//$timezone_offset_float = $timezoneMain->getFloatTimeZone($timezone_offset);
		$start_time = $this->_reservationView->dateFormat($start_time, $timezone_offset, true, 'His');
		$end_time = $this->_reservationView->dateFormat($end_time, $timezone_offset, true, 'His');

		$params = array(
				'timeframe_name' => $this->_request->getParameter('timeframe_name'),
				'start_time' => $start_time,
				'end_time' => $end_time,
				'timezone_offset' => $timezone_offset,
				'timeframe_color' => $this->_request->getParameter('timeframe_color')
		);

		// 新規登録
		if(empty($timeframe_id)) {
			$timeframe_id = $this->_db->insertExecute('reservation_timeframe', $params, true, 'timeframe_id');
			if ($timeframe_id === false) {
				return false;
			}
		}
		// 再編集
		else {
			$where_param = array('timeframe_id'=>$timeframe_id);
			$ret = $this->_db->updateExecute('reservation_timeframe', $params, $where_param);
			if ($ret === false) {
				return false;
			}
		}
		return true;
    }

    /**
     * 時間枠を削除する
     *
     * @access  public
     */
    function deleteTimeframe()
    {
    	$timeframe_id = $this->_request->getParameter('timeframe_id');
    	$where_param = array('timeframe_id'=>$timeframe_id);
    	$ret = $this->_db->deleteExecute('reservation_timeframe', $where_param);
    	if ($ret === false) {
    		return false;
    	}
		return true;
    }

	/**
	 * 予約を登録する
	 *
	 * @access  public
	 */
	function addReserve()
	{
		$calendarAction =& $this->_container->getComponent("calendarAction");

		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");

		$details_flag = intval($this->_request->getParameter("details_flag"));
		$entry_calendar = intval($this->_request->getParameter("entry_calendar"));
		$allday_flag = intval($this->_request->getParameter("allday_flag"));
		$contact = $this->_request->getParameter("contact");
		$description = $this->_request->getParameter("description");

		$location = $this->_request->getParameter("location");
		$reserve_room_id = $this->_request->getParameter("reserve_room_id");

		if ($details_flag == _ON) {
			$rrule = $this->_request->getParameter("rrule");
			$rrule_str = $calendarAction->concatRRule($rrule);
			$details_params = array(
				"contact" => $contact,
				"description" => $description,
				"rrule" => $rrule_str,
				"location_id" => $location["location_id"],
				"room_id" => $reserve_room_id
			);
		} else {
			$rrule_str = "";
			$details_params = array(
				"location_id" => $location["location_id"],
				"room_id" => $reserve_room_id
			);
		}

		$reserve_details_id = $this->_db->insertExecute("reservation_reserve_details", $details_params, false, "reserve_details_id");
		if ($reserve_details_id === false) {
			return false;
		}
		$this->_request->setParameter("reserve_details_id", $reserve_details_id);

		$start_time_full = timezone_date($this->_request->getParameter("start_time_full"));
		$end_time_full = timezone_date($this->_request->getParameter("end_time_full"));

		$base_params = array(
			"reserve_details_id" => $reserve_details_id,
			"location_id" => $location["location_id"],
			"room_id" => $reserve_room_id,
			"user_id" => $user_id,
			"user_name" => $user_name,
			"title" => $this->_request->getParameter("title"),
			"title_icon" => $this->_request->getParameter("icon_name"),
			"allday_flag" => $allday_flag,
			"start_date" => substr($start_time_full, 0, 8),
			"start_time" => substr($start_time_full, 8),
			"start_time_full" => $start_time_full,
			"end_date" => substr($end_time_full, 0, 8),
			"end_time" => substr($end_time_full, 8),
			"end_time_full" => $end_time_full,
			"timezone_offset" => $this->_request->getParameter("timezone_offset")
		);

		$reserve_id = $this->_db->insertExecute("reservation_reserve", $base_params, true, "reserve_id");
		if ($reserve_id === false) {
			return false;
		}
		$this->_request->setParameter("reserve_id", $reserve_id);

		$block_id = $this->_reservationView->getBlockIdByWhatsnew();

		$repeat_time = $this->_request->getParameter("repeat_time");
		if (!empty($repeat_time)) {
			foreach ($repeat_time as $i=>$time) {
				$start_time_full = timezone_date($time["start_time_full"]);
				$end_time_full = timezone_date($time["end_time_full"]);
				$params = array(
					"start_date" => substr($start_time_full, 0, 8),
					"start_time" => substr($start_time_full, 8),
					"start_time_full" => $start_time_full,
					"end_date" => substr($end_time_full, 0, 8),
					"end_time" => substr($end_time_full, 8),
					"end_time_full" => $end_time_full
				);
				$params = array_merge($base_params, $params);
				$result = $this->_db->insertExecute("reservation_reserve", $params, true, "reserve_id");
				if ($result === false) {
					return false;
				}
			}
		}

		if ($entry_calendar == _ON) {
			$link_params = array(
				"location" => $location["location_name"],
				"link_module" => CALENDAR_LINK_RESERVATION,
				"link_id" => $reserve_details_id,
				"link_action_name" => "action=" .DEFAULT_ACTION.
							"&active_action=reservation_view_main_init" .
							"&view_date=".substr($start_time_full, 0, 8).
							"&reserve_id=".$reserve_id.
							"&display_type=".RESERVATION_DEF_LOCATION.
							"&block_id=".$block_id."#_".$block_id
			);
			$params = array_merge($base_params, $details_params, $link_params);
			$result = $calendarAction->insertPlan($params);
			if ($result === false) {
				return false;
			}
		}
		$commonMain =& $this->_container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");

		$upload_id_arr = $uploadsAction->getUploadId($description);
		if (!empty($upload_id_arr)) {
			$params = array(
				"room_id" => $reserve_room_id
			);
			$where_params = array(
				"upload_id IN (". implode(",", $upload_id_arr) .")" => null
			);
			$result = $uploadsAction->updUploads($params, $where_params);
			if ($result === false) {
				return false;
			}
		}

		//--新着情報関連 Start--
		$result = $this->setWhatsnew();
		if ($result === false) {
			return false;
		}
		//--新着情報関連 End--

		$notification_mail = intval($this->_request->getParameter("notification_mail"));
		if ($notification_mail == _ON) {
			$this->_session->setParameter("reservation_mail_reserve_id", $reserve_id);
		} else {
			$this->_session->setParameter("reservation_mail_reserve_id", 0);
		}
		return true;
	}

	/**
	 * 予約を登録する
	 *
	 * @access  public
	 */
	function deleteReserve()
	{
		$calendarAction =& $this->_container->getComponent("calendarAction");

		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");

		$edit_rrule = intval($this->_request->getParameter("edit_rrule"));
		$reserve = $this->_request->getParameter("reserve");

		$reserve_details_id = $reserve["reserve_details_id"];
		$start_date = $reserve["start_date"];
		$start_time = $reserve["start_time"];
		$start_time_full = $reserve["start_time_full"];

		$calendar_id = $reserve["calendar_id"];
		if ($edit_rrule == RESERVATION_RESERVE_EDIT_ALL) {
			$params = array(
				"reserve_details_id" => $reserve_details_id
			);
			$result = $this->_db->deleteExecute("reservation_reserve", $params);

		} elseif ($edit_rrule == RESERVATION_RESERVE_EDIT_AFTER) {
			$sql = "DELETE FROM {reservation_reserve}" .
					" WHERE reserve_details_id = ?" .
					" AND start_time_full >= ?";

			$params = array(
				"reserve_details_id" => $reserve_details_id,
				"start_time_full" => $start_time_full
			);

			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->addError();
				return false;
			}

			$rrule_arr = $reserve["rrule_arr"];
			$freq = $rrule_arr["FREQ"];

			$rrule_arr = $rrule_arr[$freq];
			$rrule_arr["FREQ"] = $freq;

			$timestamp = mktime(0,0,0,substr($start_date,4,2),substr($start_date,6,2)-1,substr($start_date,0,4));
			$rrule_arr["UNTIL"] = date("Ymd", $timestamp)."T".$start_time;

			$rrule_str = $calendarAction->concatRRule($rrule_arr);
			$result = $this->_db->updateExecute("reservation_reserve_details", array("rrule"=>$rrule_str), array("reserve_details_id"=>$reserve_details_id));

		} else {
			$params = array(
				"reserve_id" => $reserve["reserve_id"]
			);
			$result = $this->_db->deleteExecute("reservation_reserve", $params);
		}
		if ($result === false) {
			return false;
		}

		$sql = "SELECT COUNT(*) FROM {reservation_reserve} ";
		$sql .= "WHERE reserve_details_id = ? ";
		$params = array(
			"reserve_details_id" => $reserve_details_id
		);
		$result = $this->_db->execute($sql, $params, null, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if ($result[0][0] == 0) {
			$result = $this->_db->deleteExecute("reservation_reserve_details", $params);

			if ($result === false) {
				return false;
			}

			//--新着情報関連 Start--
			$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
			$result = $whatsnewAction->delete($reserve_details_id);
			if ($result === false) {
				return false;
			}
			//--新着情報関連 End--
		}
		if ($calendar_id != 0) {
			$result = $calendarAction->deletePlan($calendar_id, $edit_rrule);
		}

		return true;
	}

	/**
	 * 予約を登録する
	 *
	 * @access  public
	 */
	function updateReserve()
	{
		$calendarAction =& $this->_container->getComponent("calendarAction");

		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");

		$edit_rrule = intval($this->_request->getParameter("edit_rrule"));
		$reserve = $this->_request->getParameter("reserve");
		$location = $this->_request->getParameter("location");
		$description = $this->_request->getParameter("description");
		$reserve_room_id = $this->_request->getParameter("reserve_room_id");

		$allday_flag = intval($this->_request->getParameter("allday_flag"));
		$entry_calendar = intval($this->_request->getParameter("entry_calendar"));

		$reserve_details_id = $reserve["reserve_details_id"];
		$calendar_id = $reserve["calendar_id"];

		$rrule = $this->_request->getParameter("rrule");
		$rrule_str = $calendarAction->concatRRule($rrule);

		$timezone_offset = $this->_request->getParameter("timezone_offset");

		$start_time_full = $this->_request->getParameter("start_time_full");
		$start_time_full = $this->_reservationView->dateFormat($start_time_full, $timezone_offset, true);

		$end_time_full = $this->_request->getParameter("end_time_full");
		$end_time_full = $this->_reservationView->dateFormat($end_time_full, $timezone_offset, true);

		$block_id = $this->_reservationView->getBlockIdByWhatsnew();

		$details_params = array(
			"contact" => $this->_request->getParameter("contact"),
			"description" => $description,
			"rrule" => $rrule_str,
			'room_id' => $reserve_room_id
		);

		if ($edit_rrule == RESERVATION_RESERVE_EDIT_ALL) {
			$result = $this->_updateReserveByAll($reserve_details_id, $details_params);
			if ($result === false) {
				return false;
			}

		} elseif ($edit_rrule == RESERVATION_RESERVE_EDIT_AFTER) {
			$result = $this->_updateReserveByAfter($reserve_details_id, $start_time_full, $details_params);
			if ($result === false) {
				return false;
			}

		} else {
			$reserve_details_id = $this->_db->insertExecute("reservation_reserve_details", $details_params, false, "reserve_details_id");
			if ($reserve_details_id === false) {
				return false;
			}
		}
		$this->_request->setParameter("reserve_details_id", $reserve_details_id);

		$time = timezone_date();
		$reserve_params = array(
			"reserve_details_id" => $reserve_details_id,
			"location_id" => $this->_request->getParameter("location_id"),
			"room_id" => $reserve_room_id,
			"user_id" => $reserve["user_id"],
			"user_name" => $reserve["user_name"],
			"title" => $this->_request->getParameter("title"),
			"title_icon" => $this->_request->getParameter("icon_name"),
			"allday_flag" => $allday_flag,
			"start_date" => substr($start_time_full,0,8),
			"start_time" => substr($start_time_full,8),
			"start_time_full" => $start_time_full,
			"end_date" => substr($end_time_full,0,8),
			"end_time" => substr($end_time_full,8),
			"end_time_full" => $end_time_full,
			"timezone_offset" => $this->_request->getParameter("timezone_offset"),
			"insert_time" => $reserve["insert_time"],
			"insert_site_id" => $reserve["insert_site_id"],
			"insert_user_id" => $reserve["insert_user_id"],
			"insert_user_name" => $reserve["insert_user_name"],
			"update_time" => timezone_date(),
			"update_site_id" => $this->_session->getParameter("_site_id"),
			"update_user_id" => $user_id,
			"update_user_name" => $user_name
		);
		$where_params = array(
			"reserve_id" => $reserve["reserve_id"]
		);
		$result = $this->_db->updateExecute("reservation_reserve", $reserve_params, $where_params, false);
		if ($result === false) {
			return false;
		}

		if ($edit_rrule != RESERVATION_RESERVE_EDIT_THIS) {
			$sql = "DELETE FROM {reservation_reserve} " .
					"WHERE reserve_details_id = ? " .
					"AND reserve_id <> ?";
			$params = array(
				"reserve_details_id" => $reserve_details_id,
				"reserve_id" => $reserve["reserve_id"]
			);
			$result = $this->_db->execute($sql, $params);
			if ($result === false) {
				$this->_db->addError();
				return false;
			}

			$repeat_time = $this->_request->getParameter("repeat_time");
			if (!empty($repeat_time)) {
				foreach ($repeat_time as $i=>$time) {
					$start_time_full = $this->_reservationView->dateFormat($time["start_time_full"], $timezone_offset, true);
					$end_time_full = $this->_reservationView->dateFormat($time["end_time_full"], $timezone_offset, true);
					$params = array(
						"start_date" => substr($start_time_full, 0, 8),
						"start_time" => substr($start_time_full, 8),
						"start_time_full" => $start_time_full,
						"end_date" => substr($end_time_full, 0, 8),
						"end_time" => substr($end_time_full, 8),
						"end_time_full" => $end_time_full
					);
					$params = array_merge($reserve_params, $params);
					$result = $this->_db->insertExecute("reservation_reserve", $params, false, "reserve_id");
					if ($result === false) {
						return false;
					}
				}
			}
		}

		if ($entry_calendar == _ON) {
			$link_params = array(
				"location_name" => $location["location_name"],
				"link_module" => CALENDAR_LINK_RESERVATION,
				"link_id" => $reserve_details_id,
				"link_action_name" => "action=" .DEFAULT_ACTION.
							"&active_action=reservation_view_main_init" .
							"&view_date=".substr($start_time_full, 0, 8).
							"&reserve_id=".$reserve["reserve_id"].
							"&display_type=".RESERVATION_DEF_LOCATION.
							"&block_id=".$block_id."#_".$block_id
			);
			$params = array_merge($reserve_params, $details_params, $link_params);
			if ($calendar_id != 0) {
				$result = $calendarAction->updatePlan($calendar_id, $params, $edit_rrule);
			} else {
				$result = $calendarAction->insertPlan($params);
			}
			if ($result === false) {
				return false;
			}
		} elseif ($calendar_id != 0) {
			$result = $calendarAction->deletePlan($calendar_id);
			if ($result === false) {
				return false;
			}
			$params = array(
				"reserve_id" => $reserve["reserve_id"],
				"calendar_id" => 0
			);
			if (!$this->_db->updateExecute("reservation_reserve", $params, "reserve_id", false)) {
				return false;
			}
		}

		$commonMain =& $this->_container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");

		$upload_id_arr = $uploadsAction->getUploadId($description);
		if (!empty($upload_id_arr)) {
			$params = array(
				"room_id" => $reserve_room_id
			);
			$where_params = array(
				"upload_id IN (". implode(",", $upload_id_arr) .")" => null
			);
			$result = $uploadsAction->updUploads($params, $where_params);
			if ($result === false) {
				return false;
			}
		}

		//--新着情報関連 Start--
		$result = $this->setWhatsnew();
		if ($result === false) {
			return false;
		}
		//--新着情報関連 End--

		$notification_mail = intval($this->_request->getParameter("notification_mail"));
		if ($notification_mail == _ON) {
			$this->_session->setParameter("reservation_mail_reserve_id", $reserve["reserve_id"]);
		} else {
			$this->_session->setParameter("reservation_mail_reserve_id", 0);
		}
		return true;
	}

	/**
	 * 予定の変更
	 *
	 * @access	public
	 */
	function _updateReserveByAll($reserve_details_id, $details_params)
	{
		$params = array("reserve_details_id" => $reserve_details_id);

		$result = $this->_db->updateExecute("reservation_reserve_details", $details_params, array("reserve_details_id"=>$reserve_details_id));
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * 予定の変更
	 *
	 * @access	public
	 */
	function _updateReserveByAfter(&$reserve_details_id, $start_time_full, $details_params)
	{
		$calendarAction =& $this->_container->getComponent("calendarAction");

		$reserve = $this->_request->getParameter("reserve");

		$sql = "DELETE FROM {reservation_reserve} " .
				"WHERE reserve_details_id = ? " .
				"AND start_time_full >= ? " .
				"AND reserve_id <> ?";
		$params = array(
			"reserve_details_id" => $reserve_details_id,
			"start_time_full" => $start_time_full,
			"reserve_id" => $reserve["reserve_id"]
		);
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->addError();
			return false;
		}

		$sql = "SELECT COUNT(*) FROM {reservation_reserve} " .
				"WHERE reserve_details_id = ? " .
				"AND reserve_id <> ?";
		$params = array(
			"reserve_details_id" => $reserve_details_id,
			"reserve_id" => $reserve["reserve_id"]
		);
		$result = $this->_db->execute($sql, $params, null, null, false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if ($result[0][0] == 0) {
			$result = $this->_db->deleteExecute("reservation_reserve_details", array("reserve_details_id"=>$reserve_details_id));
			if($result === false) {
				return false;
			}
		} else {
			$rrule_arr = $reserve["rrule_arr"];
			$freq = $rrule_arr["FREQ"];

			$rrule_arr = $rrule_arr[$freq];
			$rrule_arr["FREQ"] = $freq;

			$timestamp = mktime(0,0,0,substr($start_time_full,4,2),substr($start_time_full,6,2)-1,substr($start_time_full,0,4));
			$rrule_arr["UNTIL"] = date("Ymd", $timestamp)."T".substr($start_time_full,8);

			$rrule_before_str = $calendarAction->concatRRule($rrule_arr);
			$result = $this->_db->updateExecute("reservation_reserve_details", array("rrule"=>$rrule_before_str), array("reserve_details_id"=>$reserve_details_id));
			if ($result === false) {
				return false;
			}
		}
		$reserve_details_id = $this->_db->insertExecute("reservation_reserve_details", $details_params, false, "reserve_details_id");
		if ($reserve_details_id === false) {
			return false;
		}
		return true;
	}

	/**
	 * 新着情報の更新
	 *
	 * @access	public
	 */
	function setWhatsnew()
	{
		//--新着情報関連 Start--
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");

		$user_id = $this->_session->getParameter("_user_id");
		$user_name = $this->_session->getParameter("_handle");

		$block_id = $this->_reservationView->getBlockIdByWhatsnew();

		$reserve_id = $this->_request->getParameter("reserve_id");
		$reserve = $this->_reservationView->getReserve($reserve_id);
		if ($reserve === false) {
			return false;
		}
		$location = $this->_request->getParameter("location");

		$result = $whatsnewAction->delete($reserve["reserve_details_id"]);
		if ($result === false) {
			return false;
		}

		$whatsnew_description = "";
		$whatsnew_description .= sprintf(RESERVATION_WHATSNEW_LOCATION, $location["location_name"]);
		if ($reserve["start_date_view"] == $reserve["end_date_view"]) {
			$whatsnew_description .= sprintf(RESERVATION_WHATSNEW_TIME_FMTO, $reserve["start_date_str"]." ".$reserve["start_time_str"], $reserve["end_time_str"]);
		} else {
			$whatsnew_description .= sprintf(RESERVATION_WHATSNEW_TIME_FMTO, $reserve["start_date_str"]." ".$reserve["start_time_str"], $reserve["end_date_str"]." ".$reserve["end_time_str"]);
		}

		if (!empty($reserve["contact"])) {
			$whatsnew_description .= sprintf(RESERVATION_WHATSNEW_CONTACT, $reserve["contact"]);
		}
		if (!empty($reserve["description"])) {
			$whatsnew_description .= sprintf(RESERVATION_WHATSNEW_DESCRIPTION, $reserve["description"]);
		}
		if (!empty($reserve["rrule_str"])) {
			$whatsnew_description .= sprintf(RESERVATION_WHATSNEW_RRULE, $reserve["rrule_str"]);
		}

		$whatsnew = array(
			"room_id" => $reserve["room_id"],
			"unique_id" => $reserve["reserve_details_id"],
			"title" => $reserve["title"]." ",
			"description" => $whatsnew_description,
			"action_name" => "reservation_view_main_init",
			"parameters" => "reserve_details_id=".$reserve["reserve_details_id"].
							"&block_id=".$block_id.
							"#_".$block_id
		);

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "reservation_action_main_reserve_modify") {
			$whatsnew["insert_time"] = $reserve["insert_time"];
			$whatsnew["insert_user_id"] = $reserve["insert_user_id"];
			$whatsnew["insert_user_name"] = $reserve["insert_user_name"];
		}

		if ($reserve["room_id"] == 0 && $location["allroom_flag"] == _OFF) {
			if ($location["use_private_flag"] == _ON) {
				$whatsnew["user_id"] = $user_id;
				$whatsnew["authority_id"] = _AUTH_ADMIN;
				$result = $whatsnewAction->insert($whatsnew, _ON);
				if ($result === false) {
					return false;
				}
			}
			$whatsnew["authority_id"] = _AUTH_GUEST;
			$select_rooms = $this->_reservationView->getLocationRoom($location["location_id"]);
			if (empty($select_rooms)) {
				$select_rooms = array();
			}
			$whatsnew["user_id"] = 0;
			$whatsnew["room_id"] = $select_rooms;
		}
		$result = $whatsnewAction->insert($whatsnew, _ON);
		if ($result === false) {
			return false;
		}
		//--新着情報関連 End--
		return true;
	}
}
?>
