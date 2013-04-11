<?php
/**
 *  ブロック登録用クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Blocks_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	var $_container = null;
	
	var $databaseSqlutility = null;
	var $preexecuteMain = null;
	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $block_id = null;
	var $col_num = null;
	var $row_num = null;
	var $row_len = null;
	//var $thread_num = null;
	var $parent_id = null;
	var $pre_col_num = null;
	var $pre_row_num = null;
	var $pre_row_len = null;
	//var $pre_thread_num = null;
	var $pre_parent_id = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Blocks_Action() {
		$this->_container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * ブロックInsert
	 * @param array(page_id-min_width_size or block_obj)
	 * @return int new block_id
	 * @access	public
	 */
	function insBlock($params=array(), $footer_flag = true)
	{
		$block_id = $this->_db->insertExecute("blocks", $params, $footer_flag, "block_id", 2);
		if($block_id === false) {
			return false;
		}
		return $block_id;
	}
	
	/**
	 * ブロックUpdate
	 * @param array(page_id-min_width_size or block_obj)
	 * @return boolean true or false
	 * @access	public
	 */
	function updBlock($params=array(), $where_params=array(), $footer_flag = true)
	{
		$sql = $this->_db->getUpdateSQL("blocks", $params, $where_params, $footer_flag);
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			return false;
		}
		return true;
	}
	
	/**
	 * 名称変更処理
	 * @param int block_id 
	 * @param string block_name
	 * @return boolean true or false
	 * @access	public
	 */
	function updBlockname($block_id , $block_name)
	{
		$params = array(
			"block_name" => $block_name,
			"block_id" => $block_id
		);
		$result = $this->_db->execute("UPDATE {blocks} SET block_name=?" .
									" WHERE block_id=?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		return true;
	}
	
	/**
	 * アクション名称変更処理
	 * @param string old_action_name 
	 * @param string action_name
	 * @return boolean true or false
	 * @access	public
	 */
	function updNewActionname($old_action_name , $action_name)
	{
		$params = array(
			"action_name" => $action_name,
			"old_action_name" => $old_action_name
		);
		$result = $this->_db->execute("UPDATE {blocks} SET action_name=?" .
									" WHERE action_name=?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		return true;
	}
	
	/**
	 * 列移動・追加処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function InsertCell($page_id)
	{
		$this->page_id = $page_id;
		
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        //$site_id = $session->getParameter("_site_id");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        
		//前詰め処理(移動元)
		$result = $this->decrementRowNum();
		if(!$result) {
			return false;
		}
		if($this->pre_row_len == 1) {
			//移動前の列が１つしかなかったので
			//列--
			$result = $this->decrementColNum();
			if(!$result) {
				return false;
			}
		}
										
		//移動先より大きな列+1
		$params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"page_id" => $this->page_id,
			"block_id" => $this->block_id,
			"parent_id" => $this->parent_id,
			"col_num" => $this->col_num
		);
		
		$result = $this->incrementColNum($params);
		if(!$result) {
			$this->_db->addError($this->_db->ErrorNo(), $this->_db->ErrorMsg());
			return false;
		}
		
    	//オブジェクト取得
    	$block =& $this->_container->getComponent("blocksView");
    	$main_block_obj =& $block->getBlockById($this->block_id);
    	
		//UpdateCol
		if($this->parent_id == 0) {
			$root_id = 0;
			$thread_num = 0;
		} else {
			$block_obj =& $block->getBlockById($this->parent_id);
			if($block_obj['root_id'] != 0) {
				$root_id = $block_obj['root_id'];
			} else {
				$root_id = $block_obj['block_id'];
			}
			$thread_num = $block_obj['thread_num'] + 1;
		}
		$params = array(
			"col_num" => $this->col_num,
			"row_num" => $this->row_num,
			"thread_num" => $thread_num,
			"parent_id" => $this->parent_id,
			"root_id" => $root_id,
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"block_id" => $this->block_id
		);
		$result = $this->_db->execute("UPDATE {blocks} SET col_num=?," .
									" row_num=?,thread_num=?,parent_id=?,root_id=?,update_time=?, update_user_id=?,update_user_name=?" .
									" WHERE block_id=?",$params);
		
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		
		//更新したブロックの子供のroot_id更新処理
		if($root_id==0)
			$root_id = $this->block_id;
		
		if($main_block_obj['action_name'] == "pages_view_grouping")
			$this->_updRootIdByParentId($this->block_id,$root_id,$thread_num+1);
		
		//グループ化した空ブロック削除処理
		if($this->pre_row_len == 1) {
			$this->delGroupingBlock($this->pre_parent_id);
		}
		return true;
	}
	
	/**
	 * 行移動・追加処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function InsertRow($page_id)
	{
		$this->page_id = $page_id;
		
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
			
		//前詰め処理(移動元)
		$result = $this->decrementRowNum();
		if(!$result) {
			return false;
		}
		if($this->pre_row_len == 1) {
			//移動前の列が１つしかなかったので
			//列--
			$result = $this->decrementColNum();
			if(!$result) {
				return false;
			}
		}
										
		//前詰め処理（移動先)
		$params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"page_id" => $this->page_id,
			"block_id" => $this->block_id,
			"parent_id" => $this->parent_id,
			"col_num" => $this->col_num,
			"row_num" => $this->row_num - 1
		);
		
		$result = $this->incrementRowNum($params);
		
		if(!$result) {
			$this->_db->addError($this->_db->ErrorNo(), $this->_db->ErrorMsg());
			return false;
		}
		
		//オブジェクト取得
    	$block =& $this->_container->getComponent("blocksView");
		
		//オブジェクト取得
    	$block =& $this->_container->getComponent("blocksView");
    	$main_block_obj =& $block->getBlockById($this->block_id);
    	
		//UpdateRow
		if($this->parent_id == 0) {
			$root_id = 0;
			$thread_num = 0;
		} else {
			$block_obj = $block->getBlockById($this->parent_id);
			if($block_obj['root_id'] != 0) {
				$root_id = $block_obj['root_id'];
			} else {
				$root_id = $block_obj['block_id'];
			}
			$thread_num = $block_obj['thread_num'] + 1;
		}
		$params = array(
			"col_num" => $this->col_num,
			"row_num" => $this->row_num,
			"thread_num" => $thread_num,
			"parent_id" => $this->parent_id,
			"root_id" => $root_id,
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"block_id" => $this->block_id
		);
		$result = $this->_db->execute("UPDATE {blocks} SET col_num=?," .
									" row_num=?,thread_num=?,parent_id=?,root_id=?,update_time=?, update_user_id=?,update_user_name=?" .
									" WHERE block_id=?",$params);
		
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		
		//更新したブロックの子供のroot_id更新処理
		if($root_id==0)
			$root_id = $this->block_id;
		
		if($main_block_obj['action_name'] == "pages_view_grouping")
			$this->_updRootIdByParentId($this->block_id,$root_id,$thread_num+1);
		
		
		//グループ化した空ブロック削除処理
		if($this->pre_row_len == 1) {
			$this->delGroupingBlock($this->pre_parent_id);
		}
		
		return true;
	}
	
	/**
	 * parent_idからroot_id更新処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function _updRootIdByParentId($parent_id,$root_id,$thread_num=0)
	{
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        
        //オブジェクト取得
    	$block =& $this->_container->getComponent("blocksView");
    	
    	
    	$blocks_obj =& $block->getBlockByParentId($parent_id);
    	foreach($blocks_obj as $block_obj) {
    		if($block_obj['action_name'] == "pages_view_grouping"){
    			$this->_updRootIdByParentId($block_obj['block_id'],$root_id,$thread_num+1);
    		}
    	}
        
		$params = array(
			"root_id" => $root_id,
			"thread_num" => $thread_num,
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"parent_id" => $parent_id
		);
		$result = $this->_db->execute("UPDATE {blocks} SET root_id=?, thread_num=?, update_time=?,update_user_id=?,update_user_name=?" .
									" WHERE parent_id=?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		return true;
	}
	
	/**
	 * グループ化した空ブロック削除処理
	 *　
	 * @return boolean true or false
	 * @access	private
	 */
	function delGroupingBlock($parent_id)
	{
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        
		//ブロックオブジェクト取得
		$block =& $this->_container->getComponent("blocksView");
		if(is_object($block)) {
			$count = $block->getCountByParentid($parent_id);
			if($count == 0) {	
			    $block_obj =& $block->getBlockById($parent_id);
			    //削除処理
			    $this->delBlockById($block_obj['block_id']);
			    //前詰め処理(移動元)
			    $params = array(
					"update_time" =>$time,
					"update_user_id" => $user_id,
					"update_user_name" => $user_name,
					"page_id" => $this->page_id,
					"block_id" => $block_obj['block_id'],
					"parent_id" => $block_obj['parent_id'],
					"col_num" => $block_obj['col_num'],
					"row_num" => $block_obj['row_num']
				);
				
				$result = $this->decrementRowNum($params);
				if(!$result) {
					return false;
				}
				$params_row_count = array( 
					"page_id" => $this->page_id,
					"parent_id" => $block_obj['parent_id'],
					"col_num" => $block_obj['col_num']
				);
				$count_row_num = $block->getCountRownumByColnum($params_row_count);			
				if($count_row_num == 0) {
					//削除列が１つもなくなったので
					//列--
					$params = array(
						"update_time" =>$time,
						"update_user_id" => $user_id,
						"update_user_name" => $user_name,
						"page_id" => $this->page_id,
						"block_id" => $block_obj['block_id'],
						"parent_id" => $block_obj['parent_id'],
						"col_num" => $block_obj['col_num']
					);
					$result = $this->decrementColNum($params);
					if(!$result) {
						return false;
					}
				}
			    //再帰処理
			    $result = $this->delGroupingBlock($block_obj['parent_id']);
			    if(!$result) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 行前詰め処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function decrementRowNum($params = null,$row_num = 1) {
	 	$row_num = -1*$row_num;
	 	return $this->_operationRowNum($params, $row_num);
	}
	function incrementRowNum($params = null,$row_num = 1) {
	 	return $this->_operationRowNum($params, $row_num);
	}
	function _operationRowNum($params = null,$row_num = 1) {
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        
        //前詰め処理(移動元)
		if($params == null) {
			$params = array(
				"update_time" =>$time,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name,
				"page_id" => $this->page_id,
				"block_id" => $this->block_id,
				"parent_id" => $this->pre_parent_id,
				"col_num" => $this->pre_col_num,
				"row_num" => $this->pre_row_num
			);
		}
		$result = $this->_db->execute("UPDATE {blocks} SET row_num=row_num + (". $row_num ."),update_time=?, update_user_id=?,update_user_name=?" .
									" WHERE page_id=?" .
									" AND block_id!=?" .
									" AND parent_id=?" .
									" AND col_num=?".
									" AND row_num>?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		return true;
	}
	/**
	 * 列前詰め処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function decrementColNum($params = null,$col_num = 1) {
	 	$col_num = -1*$col_num;
	 	return $this->_operationColNum($params, $col_num);
	}
	function incrementColNum($params = null,$col_num = 1) {
	 	return $this->_operationColNum($params, $col_num);
	}
	function _operationColNum($params = null,$col_num = 1) {
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
		
		if($params == null) {
			$params = array(
					"update_time" =>$time,
					"update_user_id" => $user_id,
					"update_user_name" => $user_name,
					"page_id" => $this->page_id,
					"block_id" => $this->block_id,
					"parent_id" => $this->pre_parent_id,
					"col_num" => $this->pre_col_num
			);
		}
		$result = $this->_db->execute("UPDATE {blocks} SET col_num=col_num  + (". $col_num ."),update_time=?, update_user_id=?,update_user_name=?" .
									" WHERE page_id=?" .
									" AND block_id!=?" .
									" AND parent_id=?" .
									" AND col_num>=?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		return true;
	 }
	
	
	/**
	 * block_idによるブロック削除処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function delBlockById($block_id)
	{
		$params = array( 
			"block_id" => $block_id
		);
		
		$result = $this->_db->execute("DELETE FROM {blocks} WHERE block_id=?" .
										" ",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		
		return true;
	}
	
	/**
	 * block_idによるブロック削除処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function delBlockByPageId($page_id)
	{
		$params = array( 
			"page_id" => $page_id
		);
		
		$result = $this->_db->execute("DELETE FROM {blocks} WHERE page_id=?" .
										" ",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		
		return true;
	}
	
	/**
	 * action_nameによるブロック削除処理
	 * @parem string action_name　
	 * @return boolean true or false
	 * @access	public
	 */
	function delBlockByActionname($action_name)
	{
		$params = array( 
			"action_name" => $action_name
		);
		
		$result = $this->_db->execute("DELETE FROM {blocks} WHERE action_name=?" .
										" ",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return false;
		}
		
		return true;
	}
	
	/**
	 * 削除関数呼び出し
	 * @param  int     block_id
	 * @param  array   block
	 * @param  string  delete_action
	 * @return boolean true or false
	 * @access	public
	 */
	function delFuncExec($block_id, $block_obj = null, $delete_action = null)
	{
		$block =& $this->_container->getComponent("blocksView");
		if($block_obj == null) $block_obj =& $block->getBlockById($block_id);
		if(isset($block_obj)) {
			$action_name =$block_obj['action_name'];
			if($action_name != "pages_view_grouping") {
				$pathList = explode("_", $action_name);
				$dirname = $pathList[0];
				if($delete_action == null) {
					$delete_action = "";
					$modulesView =& $this->_container->getComponent("modulesView");
					$modules =& $modulesView->getModuleByDirname($dirname);
					if($modules && isset($modules['block_delete_action']) && $modules['system_flag'] == _OFF) {
						$delete_action = $modules['block_delete_action'];
					}
				}
				//テーブルリスト取得
				$table_list = $this->databaseSqlutility->getTableList($dirname);			
				if($delete_action == "auto") {
					if(!is_array($table_list)) {
						// 配列でなければエラーとする
						return false;	
					}

					//自動的に削除
					$where_params = array( 
						"block_id" => $block_id
					);
					$error = false;

					foreach ($table_list as $table_name) {
						//ブロックIDより削除処理。ブロックIDがなければ、処理しない
						$result = $this->_db->execute("DESCRIBE ".$table_name." block_id");
						if(is_array($result) && isset($result[0])) {
							$result = $this->_db->execute("DELETE FROM ".$table_name." WHERE block_id=?" .
												" ",$where_params);
							if(!$result) {
								$error = true;
							}
						}
					}
					
					if($error) return false;
				} elseif($delete_action == "") {
					//なにもしない
				} else {
					//
					//削除関数呼び出し
					//
					$pathList   = explode("_", $delete_action);
					$ucPathList = array_map('ucfirst', $pathList);

					$basename = ucfirst($pathList[count($pathList) - 1]);

					$actionPath = join("/", $pathList);
					$className  = join("_", $ucPathList);
					$filename   = MODULE_DIR . "/${actionPath}/${basename}.class.php";
					if (@file_exists($filename)) {
						//$params = array("action" =>$delete_action, "block_id" =>$block_id, "page_id" =>$block_obj['page_id'], "table_name" =>$table_list);
						$pagesView =& $this->_container->getComponent("pagesView");
						$page = $pagesView->getPageById($block_obj['page_id']);
						if($page === false) {
							return false;
						}

						$params = array(
							'action' => $delete_action,
							'mode' => 'block_delete',
							'block_id' => $block_id,
							'page_id' => $block_obj['page_id'],
							'room_id' => $page['room_id'],
							'module_id' => $block_obj['module_id']
						);
						$result = $this->preexecuteMain->preExecute($delete_action, $params);
						if($result === false || $result === "false") {
							//エラーが発生した場合、エラーリストに追加
							$this->_db->addError("delFuncExec", sprintf(_INVALID_ACTION, $delete_action));
						}
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * プライベートスペースのデフォルトモジュール配置処理
	 * (TODOと新着を配置)
	 * TODO:現状、汎用的には作成していない。
	 *
	 * @param   int      $page_id       ページID
	 * @param   string   $user_id       会員ID
	 * @param   string   $handle        ハンドル
	 * 
	 * @return boolean true or false
	 * @access	public
	 */
	function defaultPrivateRoomInsert($page_id,$user_id,$handle)
	{
		$modulesView =& $this->_container->getComponent("modulesView");
		$session =& $this->_container->getComponent("Session");
		$col_num = 1;
		$row_num = 1;
		$module = $modulesView->getModuleByDirname("todo");
		$time = timezone_date();
		$site_id = $session->getParameter("_site_id");
		
		if(isset($module['module_id'])) {
			$block_obj = $this->_getBlockByDefaultPrivateRoom($page_id, $col_num, $row_num, $module);
			$block_id = $this->insBlock($block_obj);
			if(!$block_id) {
				return false;
			}
			$row_num++;
			
			$todo = array(
				"room_id" => $page_id,
				"todo_name" => "TODO",
				"task_authority" => _AUTH_CHIEF,
				"insert_time" =>$time,
				"insert_site_id" => $site_id,
				"insert_user_id" => $user_id,
				"insert_user_name" => $handle,
				"update_time" =>$time,
				"update_site_id" => $site_id,
				"update_user_id" => $user_id,
				"update_user_name" => $handle
			);
			
			$todo_id = $this->_db->insertExecute("todo", $todo, false, "todo_id");
			if($todo_id === false) {
				return false;
			}
			
			$todo_block = array(
				"block_id" => $block_id,
				"todo_id" => $todo_id,
				"default_sort" => 0,
				"room_id" => $page_id,
				"insert_time" =>$time,
				"insert_site_id" => $site_id,
				"insert_user_id" => $user_id,
				"insert_user_name" => $handle,
				"update_time" =>$time,
				"update_site_id" => $site_id,
				"update_user_id" => $user_id,
				"update_user_name" => $handle
			);
			
			$result = $this->_db->insertExecute("todo_block", $todo_block, true);
			if($result === false) {
				return false;
			}
		}
		
		$module = $modulesView->getModuleByDirname("whatsnew");
		if(isset($module['module_id'])) {
			$block_obj = $this->_getBlockByDefaultPrivateRoom($page_id, $col_num, $row_num, $module);
			$block_id = $this->insBlock($block_obj);
			if(!$block_id) {
				return false;
			}
			$row_num++;
			
			// 掲示板、日誌、カレンダーをチェック
			$display_modules_arr = array();
			$sub_module = $modulesView->getModuleByDirname("bbs");
			if(isset($sub_module['module_id'])) {
				$display_modules_arr[] = $sub_module['module_id'];
			}
			$sub_module = $modulesView->getModuleByDirname("journal");
			if(isset($sub_module['module_id'])) {
				$display_modules_arr[] = $sub_module['module_id'];
			}
			$sub_module = $modulesView->getModuleByDirname("calendar");
			if(isset($sub_module['module_id'])) {
				$display_modules_arr[] = $sub_module['module_id'];
			}
			$display_modules = implode(",", $display_modules_arr);
			$whatsnew_block = array(
				"block_id" => $block_id,
				"display_type" => 2,
				"display_days" => 5,
				"display_modules" => $display_modules,
				"display_title" => _ON,
				"display_room_name" => _OFF,
				"display_module_name" => _OFF,
				"display_user_name" => _OFF,
				"display_insert_time" => _ON,
				"display_description" => _OFF,
				"allow_rss_feed" => _OFF,
				"select_room" => _OFF,
				"rss_title" => "WHATSNEW_RSS_TITLE",
				"rss_description" => "WHATSNEW_RSS_DESCRIPTION",
				"room_id" => $page_id,
				"insert_time" =>$time,
				"insert_site_id" => $site_id,
				"insert_user_id" => $user_id,
				"insert_user_name" => $handle,
				"update_time" =>$time,
				"update_site_id" => $site_id,
				"update_user_id" => $user_id,
				"update_user_name" => $handle
			);
			$result = $this->_db->insertExecute("whatsnew_block", $whatsnew_block, false);
			if($result === false) {
				return false;
			}
		}
		
		$row_num++;
		return true;
	}
	
	function _getBlockByDefaultPrivateRoom($page_id, $col_num, $row_num, &$module) {
		$session =& $this->_container->getComponent("Session");
		$block_obj = array(
			"page_id" => $page_id,
			"module_id" => $module['module_id'],
			"site_id" => $session->getParameter("_site_id"),
			"root_id" => 0,
			"parent_id" => 0,
			"thread_num" => 0,
			"col_num" => $col_num,
			"row_num" => $row_num,
			"url" => "",
			"action_name" => $module['action_name'],
			"parameters" => "",
			"block_name" => $module['module_name'],
			"theme_name" => $module['theme_name'],
			"temp_name" => $module['temp_name'],
			"leftmargin" => 8,				// 固定
			"rightmargin" => 8,				// 固定
			"topmargin" => 8,				// 固定
			"bottommargin" => 8,			// 固定
			"min_width_size" => $module['min_width_size'],
			"shortcut_flag" => 0,
			"copyprotect_flag" => 0,
			"display_scope" => _DISPLAY_SCOPE_NONE
		);
		return $block_obj;
	}
}
?>
