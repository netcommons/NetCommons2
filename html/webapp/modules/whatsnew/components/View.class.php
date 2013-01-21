<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Components_View
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

	var $_session = null;
	var $_modulesView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Whatsnew_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_modulesView =& $this->_container->getComponent("modulesView");
	}

	/**
	 * ブロックのデフォルトデータを取得
	 *
	 * @access	public
	 */
	function getDefaultBlock($module_id)
	{
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return false;
    	}
    	$display_type = $config['display_type']['conf_value'];
    	if (defined($display_type)) {
    		$display_type = constant($display_type);
    	}
    	$display_days = $config['display_days']['conf_value'];
    	if (defined($display_days)) {
    		$display_days = constant($display_days);
    	}
	    $display_number = $config['display_number']['conf_value'];
    	if (defined($display_number)) {
    		$display_number = constant($display_number);
    	}
		$display_flag = $config['display_flag']['conf_value'];
    	if (defined($display_flag)) {
    		$display_flag = constant($display_flag);
    	}
     	if (!empty($config['display_modules']['conf_value'])) {
	     	$display_modules_dirname = explode(",", $config['display_modules']['conf_value']);
	     	$display_modules = "";
	     	foreach ($display_modules_dirname as $i=>$dirname) {
				$module_obj = $this->_modulesView->getModuleByDirname($dirname);
	     		$display_modules .= ",".$module_obj["module_id"];
	     	}
     	}
     	$display_modules = !empty($display_modules) ? substr($display_modules, 1) : "";

 		$mobile_flag = $this->_session->getParameter("_mobile_flag");

    	$display_title = _ON;
    	$display_room_name = ($mobile_flag == _ON) ? _ON : $config['display_room_name']['conf_value'];
    	if (defined($display_room_name)) {
    		$display_room_name = constant($display_room_name);
    	}
    	$display_module_name = ($mobile_flag == _ON) ? _ON : $config['display_module_name']['conf_value'];
    	if (defined($display_module_name)) {
    		$display_module_name = constant($display_module_name);
    	}
    	$display_user_name = ($mobile_flag == _ON) ? _ON : $config['display_user_name']['conf_value'];
    	if (defined($display_user_name)) {
    		$display_user_name = constant($display_user_name);
    	}
    	$display_insert_time = ($mobile_flag == _ON) ? _ON : $config['display_insert_time']['conf_value'];
    	if (defined($display_insert_time)) {
    		$display_insert_time = constant($display_insert_time);
    	}
    	$display_description = ($mobile_flag == _ON) ? _ON : $config['display_description']['conf_value'];
    	if (defined($display_description)) {
    		$display_description = constant($display_description);
    	}
    	$allow_rss_feed = ($mobile_flag == _ON) ? _ON : $config['allow_rss_feed']['conf_value'];
    	if (defined($allow_rss_feed)) {
    		$allow_rss_feed = constant($allow_rss_feed);
    	}
    	if ($this->_session->getParameter("_space_type") != _SPACE_TYPE_PUBLIC && $mobile_flag == _OFF) {
    		$allow_rss_feed = _OFF;
    	}
     	$select_room = ($mobile_flag == _ON) ? _OFF : $config['select_room']['conf_value'];
    	if (defined($select_room)) {
    		$select_room = constant($select_room);
    	}

    	$default = array(
    		"block_id" => 0,
			"display_type" => $display_type,
			"display_days" => $display_days,
    		"display_number" => $display_number,
    		"display_flag" => $display_flag,
    		"display_modules" => $display_modules,
			"display_title" => $display_title,
			"display_room_name" => $display_room_name,
			"display_module_name" => $display_module_name,
			"display_user_name" => $display_user_name,
			"display_insert_time" => $display_insert_time,
			"display_description" => $display_description,
			"allow_rss_feed" => $allow_rss_feed,
			"select_room" => $select_room,
    		"rss_title" => WHATSNEW_RSS_TITLE,
    		"rss_description" => WHATSNEW_RSS_DESCRIPTION
		);
        return $default;
	}

	/**
	 * ブロックのデータを取得
	 *
	 * @access	public
	 */
	function &getBlock($block_id, $display_type=null, $display_days=null, $display_number = null)
	{
		$block_id = intval($block_id);
    	$result =& $this->_db->selectExecute("whatsnew_block", array("block_id"=>$block_id));
        if (empty($result)) {
        	return $result;
        }
        $whatsnew_obj = $result[0];
        if (!empty($whatsnew_obj["display_modules"])) {
	        $whatsnew_obj =& $this->_modulesView->getModulesById(explode(",", $whatsnew_obj["display_modules"]), array($this,"_callbackBlock"), array($whatsnew_obj));
			if ($whatsnew_obj === false) {
		       	$this->_db->addError();
		       	return $whatsnew_obj;
			}
        }
    	if (isset($display_type)) {
	    	if ($whatsnew_obj["display_type"] != $display_type) {
	    		if ($whatsnew_obj["display_type"] == WHATSNEW_DEF_MODULE) {
	    			$whatsnew_obj["display_module_name"] = _ON;
	    		}
	    		if ($whatsnew_obj["display_type"] == WHATSNEW_DEF_ROOM) {
	    			$whatsnew_obj["display_room_name"] = _ON;
	    		}
	    		if ($display_type == WHATSNEW_DEF_MODULE && $whatsnew_obj["display_module_name"] == _ON) {
	    			$whatsnew_obj["display_module_name"] = _OFF;
	    		}
	    		if ($display_type == WHATSNEW_DEF_ROOM && $whatsnew_obj["display_room_name"] == _ON) {
	    			$whatsnew_obj["display_room_name"] = _OFF;
	    		}
	    	}
    		$whatsnew_obj["display_type"] = intval($display_type);
    	}
    	if (isset($display_days)) {
    		$whatsnew_obj["display_days"] = intval($display_days);
    	}
    	if (isset($display_number)) {
    		$whatsnew_obj["display_number"] = intval($display_number);
    	}

		$modulesView =& $this->_container->getComponent("modulesView");
		$whatsnew_module =& $modulesView->getModuleByDirname("whatsnew");
		$module_id = $whatsnew_module['module_id'];

		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
    		return false;
    	}

    	$allow_switch_type = $config['allow_switch_type']['conf_value'];
    	if (defined($allow_switch_type)) {
    		$allow_switch_type = constant($allow_switch_type);
    	}
    	$whatsnew_obj["allow_switch_type"] = intval($allow_switch_type);

    	$allow_switch_days = $config['allow_switch_days']['conf_value'];
    	if (defined($allow_switch_days)) {
    		$allow_switch_days = constant($allow_switch_days);
    	}
		$whatsnew_obj["allow_switch_days"] = intval($allow_switch_days);

		if ($whatsnew_obj["select_room"] == _ON) {
			$sql = "SELECT room_id" .
					" FROM {whatsnew_select_room}" .
					" WHERE block_id = ?";
			$params = array("block_id" => $block_id);
	        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackSelectRoom"));
			if ($result === false) {
		       	$this->_db->addError();
		       	return $result;
			}
			$whatsnew_obj["select_room_list"] = $result;
		} else {
			$whatsnew_obj["myroom_flag"] = _OFF;
		}

		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($whatsnew_obj["select_room"] == _OFF && strpos(strtolower($actionName), "whatsnew_view") !== false && empty($whatsnew_obj["select_room_list"])) {
			$whatsnew_obj["select_room_list"] = array($this->_session->getParameter("_main_room_id"));
		}
        return $whatsnew_obj;
	}

	/**
	 * ブロックのデータを取得
	 *
	 * @access	private
	 */
	function _callbackBlock(&$recordSet, $params)
	{
		$whatsnew_obj = $params[0];
		if (defined($whatsnew_obj["rss_title"])) {
			$whatsnew_obj["rss_title"] = constant($whatsnew_obj["rss_title"]);
		}
		if (defined($whatsnew_obj["rss_description"])) {
			$whatsnew_obj["rss_description"] = constant($whatsnew_obj["rss_description"]);
		}

		while ($row = $recordSet->fetchRow()) {
			if ($row["whatnew_flag"] != _ON) { continue; }
			$pathList = explode("_", $row["action_name"]);
			$whatsnew_obj["modules"][$row["module_id"]]["module_id"] = $row["module_id"];
			$whatsnew_obj["modules"][$row["module_id"]]["dir_name"] = $pathList[0];
			$whatsnew_obj["modules"][$row["module_id"]]["module_name"] = $this->_modulesView->loadModuleName($pathList[0]);
		}
		return $whatsnew_obj;
	}

	/**
	 * ブロックのデータを取得
	 *
	 * @access	private
	 */
	function _callbackSelectRoom(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[] = $row["room_id"];
		}
		return $result;
	}

	/**
	 * 携帯用(ヘッダメニューの新着情報)の設定データを取得
	 *
	 * @access	public
	 */
	function getMobileBlock($module_id)
	{
		$mobile_module_obj = $this->_modulesView->getModuleByDirname('mobile');
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfig($mobile_module_obj['module_id'], false);
		if ($config === false) {
			return false;
		}
		$display_type = $config['mobile_whatsnew_display_type']['conf_value'];
		$display_days = $config['mobile_whatsnew_display_days']['conf_value'];
		$display_number = $config['mobile_whatsnew_display_number']['conf_value'];
		$display_flag = $config['mobile_whatsnew_display_flag']['conf_value'];
		$display_modules = $config['mobile_whatsnew_select_module']['conf_value'];

		if (empty($display_modules)) {	// 初期状態＝全て選択
			//携帯モジュールのIDを集める
			$getdata =& $this->_container->getComponent("GetData");
			$mobile_modules = $getdata->getParameter("mobile_modules");
			$mobile_modules_arr = array();
			foreach ($mobile_modules as $display_position=>$mmodules) {
				foreach ($mmodules as $dir_name=>$mmodule) {
					$mobile_modules_arr[] = $mmodule["module_id"];
				}
			}
			$display_modules = implode(',',$mobile_modules_arr);
		}

		$whatsnew_config = $configView->getConfig($module_id, false);
		if($whatsnew_config==false) {
			return false;
		}

		$display_title = _ON;
		$display_room_name = _ON;
		$display_module_name = _ON;
		$display_user_name = _ON ;
		$display_insert_time = _ON;
		$display_description = _ON ;
		$allow_rss_feed = _OFF;

		$select_room_flag = $config['mobile_whatsnew_select_room_flag']['conf_value'];
		$select_room = $config['mobile_whatsnew_select_room']['conf_value'];
		$select_room_list = explode(',',$select_room);
		$myroom_flag = $config['mobile_whatsnew_select_myroom']['conf_value'];
		$mobile = array(
			'block_id' => 0,
			'display_type' => $display_type,
			'display_days' => $display_days,
			'display_number' => $display_number,
			'display_flag' => $display_flag,
			'display_modules' => $display_modules,
			'display_title' => $display_title,
			'display_room_name' => $display_room_name,
			'display_module_name' => $display_module_name,
			'display_user_name' => $display_user_name,
			'display_insert_time' => $display_insert_time,
			'display_description' => $display_description,
			'allow_rss_feed' => $allow_rss_feed,
			'select_room' => $select_room_flag,
			'myroom_flag'=>$myroom_flag,
			'rss_title' => WHATSNEW_RSS_TITLE,
			'rss_description' => WHATSNEW_RSS_DESCRIPTION,
			'select_room_list'=>$select_room_list,
			'allow_switch_days'=>defined($whatsnew_config['allow_switch_days']['conf_value'])?constant($whatsnew_config['allow_switch_days']['conf_value']):intval($whatsnew_config['allow_switch_days']['conf_value'])
		);

		$mobile =& $this->_modulesView->getModulesById(explode(',',$display_modules), array($this,"_callbackBlock"), array($mobile));
		if ($mobile === false) {
			return false;
		}

		return $mobile;
	}

	/**
	 * モジュールのデータを取得
	 *
	 * @access	public
	 */
	function &getModules($as_key=null)
	{
		$result = array();
		$result =& $this->_modulesView->getModules(null, array("display_sequence"=>"ASC"), null, null, array($this, "_callbackModules"), array($as_key));
		if ($result === false) {
        	return $result;
        }
        return $result;
	}

	/**
	 * モジュールのデータを取得
	 *
	 * @access	private
	 */
	function _callbackModules(&$recordSet, &$params)
	{
		$as_key = $params[0];
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			if ($row["whatnew_flag"] != _ON) { continue; }
			$pathList = explode("_", $row["action_name"]);
			$row["dir_name"] = $pathList[0];
			$row["module_name"] = $this->_modulesView->loadModuleName($row["dir_name"]);
			if (isset($as_key) && $as_key == "id_only") {
				$ret[] = $row["module_id"];
			} elseif (isset($as_key)) {
				$ret[$row[$as_key]] = $row;
			} else {
				$ret[] = $row;
			}
		}
		return $ret;
	}
	/**
	 * データを取得
	 *
	 * @access	public
	 */
	function &getResults(&$whatsnew_obj, $room_arr_flat, $limit=null, $offset=null)
	{
		if (empty($whatsnew_obj["display_modules"]) || empty($room_arr_flat)) {
			$result = array();
			return $result;
		}
		//件数表示のとき件数の値をセット
		if($whatsnew_obj['display_flag'] == _ON){
			if($limit == null && $offset == null){
				$limit = $whatsnew_obj['display_number'];
				$offset = 0;
			}
		}
		$params = array();
		//日数表示の場合、日時の条件を作成
		if($whatsnew_obj['display_flag'] == _OFF){
			$today = timezone_date(null, false, "YmdHi60");
			$target_date = date("Ymd000000", mktime(0, 0, 0,
							intval(substr($today,4,2)),
							intval(substr($today,6,2))-$whatsnew_obj["display_days"],
							intval(substr($today,0,4))));
			$params["fm_insert_time"] = timezone_date($target_date, true, "YmdHis");
			$params["to_insert_time"] = timezone_date($today, true, "YmdHis");
		}

		$_user_id = $this->_session->getParameter("_user_id");

		//ルーム表示で件数表示以外の時
		if(!($whatsnew_obj['display_type'] == WHATSNEW_DEF_ROOM && ($limit!==null&&$offset!==null)/*$whatsnew_obj['display_flag'] == _ON*/)){
			$chief_room = array();
			$moderator_room = array();
			foreach ($room_arr_flat as $room_id=>$room_obj) {
				$auth_id = $room_obj['authority_id'];
				if ($auth_id == _AUTH_CHIEF) {
					$chief_room[] = $room_id;
				} elseif ($auth_id == _AUTH_MODERATE) {
					$moderator_room[] = $room_id;
				}
			}
			$params["guest_authority_id"] = _AUTH_GUEST;
			if (!empty($_user_id)) {
				$params["user_id"] = $_user_id;
			}
			if (!empty($chief_room)) {
				$params["chief_user_id"] = $_user_id;
				$params["chief_authority_id"] = _AUTH_CHIEF;
			}
			if (!empty($moderator_room)) {
				$params["moderator_user_id"] = $_user_id;
				$params["moderator_authority_id"] = _AUTH_MODERATE;
			}
		}

		$sql = "SELECT whatsnew.room_id, whatsnew.module_id, whatsnew.unique_id, whatsnew.action_name, COUNT(*) AS total" .
			   " FROM {whatsnew} whatsnew";

		$sql2 = "SELECT whatsnew.*, module.action_name AS module_action_name, read_user.whatsnew_id AS read_flag, page.page_name" .
				" FROM {whatsnew} whatsnew" .
				" INNER JOIN {modules} module ON (whatsnew.module_id=module.module_id)" .
				" LEFT JOIN {whatsnew_user} read_user ON (whatsnew.whatsnew_id=read_user.whatsnew_id AND read_user.user_id='".$_user_id."')" .
				" LEFT JOIN {pages} page ON (whatsnew.room_id=page.page_id)";

		//表示方法のタイプによりGROUPBYを指定する。
		$select_modules = explode(",",$whatsnew_obj["display_modules"]);
		$module_obj = $this->_modulesView->getModuleByDirname("reservation");
		if(isset($module_obj["module_id"]) && in_array($module_obj["module_id"], $select_modules)) {
			$reservation_flag = true;
		} else {
			$reservation_flag = false;
		}
		if($reservation_flag) {
			if ($whatsnew_obj["display_type"] == WHATSNEW_DEF_ROOM) {
				$sql_groupby = " GROUP BY whatsnew.room_id, whatsnew.module_id, whatsnew.unique_id";
			} else {
				$sql_groupby = " GROUP BY whatsnew.module_id, whatsnew.unique_id";
			}
		} else
			$sql_groupby = "";
		//表示方法のタイプによりをORDERBY指定する。
		if ($whatsnew_obj["display_type"] == WHATSNEW_DEF_ROOM) {
			$sql_order = " ORDER BY whatsnew.room_id, whatsnew.child_update_time DESC, whatsnew.whatsnew_id DESC";
		} elseif ($whatsnew_obj["display_type"] == WHATSNEW_DEF_MODULE) {
			$sql_order = " ORDER BY whatsnew.module_id, whatsnew.child_update_time DESC, whatsnew.whatsnew_id DESC";
		} else {
			$sql_order = " ORDER BY whatsnew.child_update_time DESC, whatsnew.whatsnew_id DESC";
		}

		//モジュール毎表示 かつ 取得件数の指定がある場合
		if($whatsnew_obj['display_type'] == WHATSNEW_DEF_MODULE && ($limit !== null && $offset !== null)){
			$results = array();
			foreach($select_modules as $select_module){
				$sql_where = $this->_makeSqlModuleWhere($select_module, $whatsnew_obj['display_flag']);
				//日数表示の場合に条件を付け足す
				if($whatsnew_obj['display_flag'] == _OFF){
					$sql_where .= " AND whatsnew.child_update_time >= ? AND whatsnew.child_update_time < ? ";
				}
				$sql_where .= $this->_makeSqlRoomWhere($_user_id, $room_arr_flat, $chief_room, $moderator_room);
				$complete_sql = $sql.$sql_where.$sql_groupby.$sql_order;
				$sqlgetdata = $sql2.$sql_where.$sql_groupby.$sql_order;
				$result = $this->_getData($whatsnew_obj, $complete_sql, $params, $limit, $offset, $sqlgetdata, $reservation_flag);
				//モジュール毎のデータをセットする。
				foreach($result as $moduledata){
					$results[$select_module] = $moduledata;
				}
			}
		//ルーム毎表示 かつ 取得件数の指定がある場合
		} elseif ($whatsnew_obj['display_type'] == WHATSNEW_DEF_ROOM && ($limit !== null && $offset !== null)) {
			//room_id=0のデータを取得するため。
			if(!empty($_user_id) && isset($room_arr_flat[_SELF_TOPPUBLIC_ID])){
				$room_arr_flat[0]=null;
			}

			foreach ($room_arr_flat as $room_id=>$room_obj) {
				$sql_where = null;
				$chief_room = null;
				$moderator_room = null;
				$params = array();
				$auth_id = $room_obj['authority_id'];
				if ($auth_id == _AUTH_CHIEF) {
					$chief_room = $room_id;
				} elseif ($auth_id == _AUTH_MODERATE) {
					$moderator_room = $room_id;
				}

				$params["guest_authority_id"] = _AUTH_GUEST;
				if (!empty($_user_id)) {
					$params["user_id"] = $_user_id;
				}
				if (!empty($chief_room)) {
					$params["chief_user_id"] = $_user_id;
					$params["chief_authority_id"] = _AUTH_CHIEF;
				}
				if (!empty($moderator_room)) {
					$params["moderator_user_id"] = $_user_id;
					$params["moderator_authority_id"] = _AUTH_MODERATE;
				}

				$sql_where = $this->_makeSqlModuleWhere($whatsnew_obj["display_modules"], $whatsnew_obj['display_flag']);

				$sql_where .= $this->_makeSqlRoomWhere($_user_id, $room_id, $chief_room, $moderator_room);
				//日数表示の場合に条件を付け足す
				if($whatsnew_obj['display_flag'] == _OFF){
					$sql_where .= " AND (whatsnew.child_update_time >= ? AND whatsnew.child_update_time < ?) ";
					$params["fm_insert_time"] = timezone_date($target_date, true, "YmdHis");
					$params["to_insert_time"] = timezone_date($today, true, "YmdHis");
				}

				$sql3 = $sql.$sql_where.$sql_groupby.$sql_order;
				$sqlgetdata = $sql2.$sql_where.$sql_groupby.$sql_order;
				$result = $this->_getData($whatsnew_obj, $sql3, $params, $limit, $offset, $sqlgetdata, $reservation_flag);

				if ($result === false) {
					$this->_db->addError();
					return $result;
				}
				foreach($result as $data){
					$results[$room_id] = $data;
				}
			}
		// フラット表示 または 日数表示 かつ 取得件数の指定がある場合
		} else {
			$sql_where = $this->_makeSqlModuleWhere($whatsnew_obj["display_modules"], $whatsnew_obj['display_flag']);
			//日数表示の場合に条件を付け足す
			if($whatsnew_obj['display_flag'] == _OFF){
				$sql_where .= " AND whatsnew.child_update_time >= ? AND whatsnew.child_update_time < ?";
			}

			$sql_where .= $this->_makeSqlRoomWhere($_user_id, $room_arr_flat, $chief_room, $moderator_room);
			$sql .= $sql_where.$sql_groupby.$sql_order;
			$sql2 .= $sql_where.$sql_groupby.$sql_order;
			$results = $this->_getData($whatsnew_obj, $sql, $params, $limit, $offset, $sql2, $reservation_flag);
			if ($results === false) {
				$this->_db->addError();
				return $results;
			}
		}
		return $results;
	}

	/**
	 * 選択しているモジュールの条件作成
	 *
	 * @access	private
	 */
	function _makeSqlModuleWhere($select_module, $display_flag=_OFF){

		if(is_array($select_module)){
			$sql_where = " WHERE whatsnew.module_id IN (".implode(",",$select_module).")";
		}else{
			$sql_where = " WHERE whatsnew.module_id IN (".$select_module.")";
		}
		if ($display_flag == _ON) {
			$today = timezone_date(null, false, "YmdHi60");
			$sql_where .= " AND whatsnew.insert_time < '".timezone_date($today, true, "YmdHis")."'";
		}
    	return $sql_where;
    }

   	/**
	 * ルームの条件作成
	 *
	 * @access	private
	 */
	function _makeSqlRoomWhere($_user_id, $room_arr_flat, $chief_room, $moderator_room){

		if(is_array($room_arr_flat)){
    		$sql_where = " AND (" .
	    			"(" .
					"(whatsnew.authority_id <= ?" . (!empty($_user_id) ? " OR whatsnew.user_id = ?" : "") . ")" .
					" AND whatsnew.room_id IN (".implode(",",array_keys($room_arr_flat)).(!empty($_user_id) && isset($room_arr_flat[_SELF_TOPPUBLIC_ID]) ? ",0" : "").")" .
	    			")" .
					(!empty($chief_room) ? " OR (whatsnew.user_id <> ? AND whatsnew.authority_id >= ? AND whatsnew.room_id IN (".implode(",",$chief_room)."))" : "") .
					(!empty($moderator_room) ? " OR (whatsnew.user_id <> ? AND whatsnew.authority_id = ? AND whatsnew.room_id IN (".implode(",",$moderator_room)."))" : "") .
					")";
		    return $sql_where;
		}else{
			$sql_where = " AND (" .
	    			"(" .
					"(whatsnew.authority_id <= ?" . (!empty($_user_id) ? " OR whatsnew.user_id = ?" : "") . ")" .
					" AND whatsnew.room_id IN (".$room_arr_flat.")" .
	    			")" .
					(!empty($chief_room) ? " OR (whatsnew.user_id <> ? AND whatsnew.authority_id >= ? AND whatsnew.room_id IN (".$chief_room."))" : "") .
					(!empty($moderator_room) ? " OR (whatsnew.user_id <> ? AND whatsnew.authority_id = ? AND whatsnew.room_id IN (".$moderator_room."))" : "") .
					")";

			return $sql_where;
		}
    }
    /**
	 * データ取得
	 *
	 * @access	private
	 */
	function _getData($whatsnew_obj, $sql, $params, $limit, $offset, $sqlgetdata, $reservation_flag){

		if($reservation_flag) {
			$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackUniqId"), array($limit, $offset, $whatsnew_obj));
			if ($result === false) {
		   		$this->_db->addError();
		   		return $result;
			}

			$unique_list = $result[0];
			$limit = $result[1];
			$offset = $result[2];
			$this->_session->setParameter("whatsnew_total", $result[3]);
		} else {
			$unique_list = null;
		}
   		$result = $this->_db->execute($sqlgetdata, $params, $limit, $offset, true, array($this,"_callbackResult"), array($whatsnew_obj, $unique_list));
   		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		if(!$reservation_flag) {
			$count_result = $this->_db->execute("select count(*) from (" . $sqlgetdata . ") as t", $params, null,null,false);
			$this->_session->setParameter("whatsnew_total", $count_result[0][0]);
   		}
		return $result;
    }
	/**
	 * データ取得
	 *
	 * @access	private
	 */
	function _callbackUniqId(&$recordSet, &$params)
	{
		$limit = $params[0];
		$offset = $params[1];
		$whatsnew_obj = (!empty($params[2]) ? $params[2] : null);
		$index = 0;

		$ret_limit = isset($limit) ? 0 : null;
		$ret_offset = isset($offset) ? 0 : null;
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			if (!isset($ret[$row["module_id"]][$row["unique_id"]])) {
				$ret[$row["module_id"]][$row["unique_id"]] = array();
			}
			if (!empty($row["action_name"])) {
				$pathList = explode("_", $row["action_name"]);
				$row["dir_name"] = $pathList[0];
			} else {
				$row["dir_name"] = "";
			}

			if ($row["total"] > 1 && $row["dir_name"] == "reservation") {
				$ret[$row["module_id"]][$row["unique_id"]][] = "0";
			} else {
				$ret[$row["module_id"]][$row["unique_id"]][] = $row["room_id"];
			}
			if (!isset($limit) || !isset($offset)) { $index++; continue; }
			if ($row["dir_name"] != "reservation" || isset($whatsnew_obj) && $whatsnew_obj['display_type'] != WHATSNEW_DEF_ROOM) {
				$row["total"] = 1;
			}

			if ($index < $offset) {
				$ret_offset += $row["total"];
			} elseif ($index < $offset + $limit) {
				$ret_limit += $row["total"];
			}
			$index++;
		}
		return array($ret, $ret_limit, $ret_offset, $index);
	}
	/**
	 * データ取得
	 *
	 * @access	private
	 */
	function &_callbackResult(&$recordSet, &$params)
	{
		$whatsnew_obj = $params[0];
		$unique_list = $params[1];
		$result_unique = array();

		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			if (isset($result_unique[$row["module_id"]][$row["unique_id"]])) { continue; }
			$result_unique[$row["module_id"]][$row["unique_id"]] = true;
			if (isset($unique_list[$row["module_id"]][$row["unique_id"]]) && $unique_list[$row["module_id"]][$row["unique_id"]][0] == "0") {
				$row["room_id"] = "0";
			}

			$this->setWhatsnewAction($row);

			if (empty($row["title"])) {
				$row["title"] = _SEARCH_SUBJECT_NONEXISTS;
			}
			if (empty($row["description"])) {
				$row["description"] = defined("WHATSNEW_NO_DESCRIPTION") ? WHATSNEW_NO_DESCRIPTION : "";
			}
			if ($row["room_id"] == "0") {
				if (defined("WHATSNEW_".strtoupper($row["dir_name"])."_NO_PAGE")) {
					$row["page_name"] = constant("WHATSNEW_".strtoupper($row["dir_name"])."_NO_PAGE");
				} else {
					$row["page_name"] = WHATSNEW_NO_PAGE;
				}
			}

			if (!isset($whatsnew_obj)) {
				$ret = $row;
				break;
			} elseif ($whatsnew_obj["display_type"] == WHATSNEW_DEF_ROOM) {
	    		if (!isset($ret[$row["room_id"]])) { $ret[$row["room_id"]] = array(); }
	    		$ret[$row["room_id"]][] = $row;
	    	} elseif ($whatsnew_obj["display_type"] == WHATSNEW_DEF_MODULE) {
	    		if (!isset($ret[$row["module_id"]])) { $ret[$row["module_id"]] = array(); }
	    		$ret[$row["module_id"]][] = $row;
	    	} elseif ($whatsnew_obj["display_type"] == WHATSNEW_DEF_RSS) {
	    		if ($row["dir_name"] == "calendar" || $row["dir_name"] == "reservation" || $row["dir_name"] == "event") {
		    		$url = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
													"&".$row["action_name"].
													"&".$row["parameters"];

	    		} else {
		    		$url = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
													($row["action_name"] != DEFAULT_ACTION ? "&".$row["action_name"] : "").
													"&".$row["parameters"];
	    		}
	    		$item = array(
	    			"title" => $row["title"],
	    			"count_num" => $row["count_num"],
	    			"dir_name" => $row["dir_name"],
	    			"description" => $row["description"],
	    			"url" => $url,
	    			"pubDate" => $row["insert_time"]
	    		);
	    		$ret[] = $item;
	    	} else {
	    		$ret[] = $row;
	    	}
		}
		return $ret;
	}
	/**
	 * データを取得
	 *
	 * @access	public
	 */
	function &getResult($room_arr_flat, $module_id, $unique_id)
	{
    	$_user_id = $this->_session->getParameter("_user_id");

    	$chief_room = array();
    	$moderator_room = array();
    	foreach ($room_arr_flat as $room_id=>$room_obj) {
	    	$auth_id = $room_obj['authority_id'];
	    	if ($auth_id == _AUTH_CHIEF) {
	    		$chief_room[] = $room_id;
	    	} elseif ($auth_id == _AUTH_MODERATE) {
	    		$moderator_room[] = $room_id;
	    	}
    	}

     	$sql_where = " WHERE 1=1" .
    			" AND whatsnew.module_id = ?" .
    			" AND whatsnew.unique_id = ?" .
    			" AND (" .
	    			"(" .
						"(whatsnew.authority_id <= ?" . (!empty($_user_id) ? " OR whatsnew.user_id = ?" : "") . ")" .
						" AND whatsnew.room_id IN (".implode(",",array_keys($room_arr_flat)).(!empty($room_arr_flat) && !empty($_user_id) ? "," : "").(!empty($_user_id) ? "0" : "").")" .
	    			")" .
					(!empty($chief_room) ? " OR (whatsnew.user_id <> ? AND whatsnew.authority_id = ? AND whatsnew.room_id IN (".implode(",",$chief_room)."))" : "") .
					(!empty($moderator_room) ? " OR (whatsnew.user_id <> ? AND whatsnew.authority_id = ? AND whatsnew.room_id IN (".implode(",",$moderator_room)."))" : "") .
				")";
    	$params = array();
    	$params["module_id"] = $module_id;
    	$params["unique_id"] = $unique_id;
    	$params["guest_authority_id"] = _AUTH_GUEST;
    	if (!empty($_user_id)) {
    		$params["user_id"] = $_user_id;
    	}
    	if (!empty($chief_room)) {
    		$params["chief_user_id"] = $_user_id;
    		$params["chief_authority_id"] = _AUTH_CHIEF;
    	}
    	if (!empty($moderator_room)) {
    		$params["moderate_user_id"] = $_user_id;
    		$params["moderate_authority_id"] = _AUTH_MODERATE;
    	}

		$sql = "SELECT whatsnew.room_id, whatsnew.module_id, whatsnew.unique_id, whatsnew.action_name, COUNT(*) AS total" .
				" FROM {whatsnew} whatsnew" .
				$sql_where;

		$module_obj = $this->_modulesView->getModuleByDirname("reservation");
		if(isset($module_obj["module_id"]) && $module_obj["module_id"]== $module_id) {
			$sql .= " GROUP BY whatsnew.room_id, whatsnew.module_id, whatsnew.unique_id";
	        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackUniqId"), array(null, null));
			if ($result === false) {
		       	$this->_db->addError();
		       	return $result;
			}
			$unique_list = $result[0];
		} else {
			$unique_list = null;
		}

    	$sql = "SELECT whatsnew.*, module.action_name AS module_action_name, page.page_name" .
    			" FROM {whatsnew} whatsnew" .
    			" INNER JOIN {modules} module ON (whatsnew.module_id=module.module_id)" .
    			" LEFT JOIN {pages} page ON (whatsnew.room_id=page.page_id)" .
     			$sql_where;

        $result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackResult"), array(null, $unique_list));
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * データを取得
	 *
	 * @access	public
	 */
	function &getWhatsnew($whatsnews_id)
	{
    	$sql = "SELECT whatsnew.*, module.action_name AS module_action_name, page.page_name, page.private_flag, page.default_entry_flag" .
    			" FROM {whatsnew} whatsnew" .
    			" INNER JOIN {modules} module ON (whatsnew.module_id=module.module_id)" .
    			" LEFT JOIN {pages} page ON (whatsnew.room_id=page.page_id)" .
    			" WHERE whatsnew.whatsnew_id = ?";

		$params = array("whatsnew_id" => $whatsnews_id);
		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackWhatsnew"));
		if (empty($result)) {
			$result = false;
	       	return $result;
		}
		return $result;
	}
	/**
	 * データ取得
	 *
	 * @access	private
	 */
	function &_callbackWhatsnew(&$recordSet)
	{
		$row = false;
		if ($row = $recordSet->fetchRow()) {
			$this->setWhatsnewAction($row);

			if (empty($row["title"])) {
				$row["title"] = _SEARCH_SUBJECT_NONEXISTS;
			}
			if (empty($row["description"])) {
				$row["description"] = defined("WHATSNEW_NO_DESCRIPTION") ? WHATSNEW_NO_DESCRIPTION : "";
			}
			if ($row["room_id"] == "0") {
				if (defined("WHATSNEW_".strtoupper($row["dir_name"])."_NO_PAGE")) {
					$row["page_name"] = constant("WHATSNEW_".strtoupper($row["dir_name"])."_NO_PAGE");
				} else {
					$row["page_name"] = WHATSNEW_NO_PAGE;
				}
			}
		}
		return $row;
	}

	/**
	 * チャンネルデータを取得
	 *
	 * @access	public
	 */
	function &getChannel(&$whatsnew_obj)
	{
		$channel = array();
		$meta = $this->_session->getParameter("_meta");

    	$search = array("{X-SITE_NAME}");
    	$replace = array($meta["sitename"]);

		$channel["title"] = trim(str_replace($search, $replace, $whatsnew_obj["rss_title"]));
		$channel["url"] = BASE_URL.INDEX_FILE_NAME."?action=whatsnew_view_main_rss" .
													"&block_id=".$whatsnew_obj["block_id"].
													"&display_days=".$whatsnew_obj["display_days"];
		$channel["description"] = trim(str_replace($search, $replace, $whatsnew_obj["rss_description"]));
        return $channel;
	}

    /**
	 * 新着のアクションを取得
	 * 　
	 * @return array fetchRow
     * @access  public
	 */
	function setWhatsnewAction(&$fetchRow)
	{
		$mobile_flag = $this->_session->getParameter("_mobile_flag");

		$pathList = explode("_", $fetchRow["module_action_name"]);
		$fetchRow["dir_name"] = $pathList[0];
		$fetchRow["module_name"] = $this->_modulesView->loadModuleName($fetchRow["dir_name"]);

		if ($mobile_flag == _ON) {
			if ($fetchRow["dir_name"] == "journal") {
				$fetchRow["parameters"] = str_replace(array("&comment_flag=1"), array(""), $fetchRow["parameters"]);
			}
			return true;
		}

		if ($fetchRow["action_name"] == DEFAULT_ACTION) {
			$fetchRow["action_name"] = "";
		} elseif ($fetchRow["dir_name"] == "calendar" || $fetchRow["dir_name"] == "reservation" || $fetchRow["dir_name"] == "event") {
			$fetchRow["action_name"] = "active_center=".$fetchRow["action_name"];
			$fetchRow["parameters"] = preg_replace('/&block_id=(.*)?#(_[0-9]*)/u', '&active_block_id=$1&page_id='.$fetchRow["room_id"].'#_active_center$2', $fetchRow['parameters']);
			if ($fetchRow["dir_name"] == "calendar") {
				$fetchRow["parameters"] = preg_replace('/^(.*)(#_active_center_[0-9]*)$/u', '$1'."&display_type=".WHATSNEW_DEF_CALENDAR_VIEW.'$2', $fetchRow['parameters']);
			}
			if ($fetchRow["dir_name"] == "reservation") {
				$fetchRow["parameters"] = preg_replace('/^(.*)(#_active_center_[0-9]*)$/u', '$1'."&display_type=".WHATSNEW_DEF_RESERVATION_VIEW.'$2', $fetchRow['parameters']);
			}
		} else {
			$fetchRow["action_name"] = "active_action=".$fetchRow["action_name"];
		}

		return true;
	}

}
?>