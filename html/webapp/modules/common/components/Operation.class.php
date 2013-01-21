<?php
/**
 * モジュール操作共通コンポーネント
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Components_Operation {
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Common_Components_Operation() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	
	/**
	 * アップロードしたupload_idの一覧を取得
	 * WYSIWYG用
	 * @param  string $column_names　WYSIWYGが使用されているカラム名称
	 * 			「,」区切りで複数指定可能
	 * @param  array  $rec_set レコードセット
	 * @return array  $upload_id_arr 
	 * @access	public
	 */
	function &getWysiwygUploads($column_names, &$rec_set)
	{
		$upload_id_arr = array();
		$column_name_arr = explode(",", $column_names);
		$pattern = _REGEXP_UPLOAD_ID;
		if(is_array($column_name_arr)) {
			foreach($column_name_arr as $column_name) {
				foreach($rec_set as $rec) {
					$content = $rec[$column_name];
					$matches = null;
					if(preg_match_all ($pattern, $content, $matches)) {
						if(isset($matches[1])) {
							foreach($matches[1] as $upload_id) {
								$upload_id_arr[] = $upload_id;
							}
						}
					}
				}
			}
		}
		
		return $upload_id_arr;
	}
	
	
	/**
	 * アップロードしたupload_idの一覧を取得
	 * Text用(imageのパス等が書かれたカラムがある場合)
	 * @param  string $column_names　upload_idを含むカラムの名称
	 * 			「,」区切りで複数指定可能
	 * @param  array  $rec_set レコードセット
	 * @return array  $upload_id_arr 
	 * @access	public
	 */
	function &getTextUploads($column_names, &$rec_set)
	{
		$upload_id_arr = array();
		$column_name_arr = explode(",", $column_names);
		$pattern = "/upload_id=([0-9]+)/i";
		if(is_array($column_name_arr)) {
			foreach($column_name_arr as $column_name) {
				foreach($rec_set as $rec) {
					$content = $rec[$column_name];
					$matches = null;
					if(preg_match_all ($pattern, $content, $matches)) {
						if(isset($matches[1])) {
							foreach($matches[1] as $upload_id) {
								$upload_id_arr[] = $upload_id;
							}
						}
					}
				}
			}
		}
		return $upload_id_arr;
	}
	
	/**
	 * アップロードしたupload_idの更新
	 * WYSIWYG用
	 * @param  array  $upload_id_arr
	 * @param  int    $room_id 更新するルームID
	 * @param  array  $uploads_where_params アップロードテーブル追加条件指定
	 * 						room_id、module_idは必ずチェックし、そのWysiwyg上でuploadsされたものが正しい
	 * 						かどうかをチェックする。
	 * 						その他、条件を追加したければ、このパラメータより追加（マージ）
	 * 
	 * ※厳密にいえば、room_id、module_idだけのチェックでは、お知らせを同じルーム内に２つあり、
	 * 　1つ目のお知らせからアップロードし、2つ目にそのパスをコピーした段階で
	 * 　2つ目のお知らせを移動してしまうと、1つ目のお知らせから画像が消えてしまうため
	 * 　問題となる。しかし、現状、そのuploadしたファイルが、そのWYSIWYG内で本当に
	 * 　アップロードしたかどうかを知る術がないため、対応しない。
	 * @return boolean 
	 * @access	public
	 */
	function updWysiwygUploads(&$upload_id_arr, $room_id, $uploads_where_params = array())
	{
		$container =& DIContainerFactory::getContainer();
        $request =& $container->getComponent("Request");
        $session =& $container->getComponent("Session");
        
		if(count($upload_id_arr) > 0) {
			// TODO:アップロードされているファイルの数が1000000件？以上に拡大した場合、
			// SQL文の長さ制限（16M?)を越える可能性あり。
			// 現状、対処しない。
			$params = array(
				"room_id"=> intval($room_id),
				"update_user_id" => $session->getParameter("_user_id"),
				"update_user_name" => $session->getParameter("_handle")
			);
			$module_id = $request->getParameter("module_id");
			if(!isset($module_id)) {
				$actionChain =& $container->getComponent("ActionChain");
				$modulesView =& $container->getComponent("modulesView");
	        	$curAction = $actionChain->getCurActionName();
	        	$pathList = explode("_", $curAction);
	        	$module = $modulesView->getModuleByDirname($pathList[0]);
	        	if(isset($module['module_id'])) $module_id = $module['module_id'];
			}
			$where_params = array(
				"upload_id IN ('". implode("','", $upload_id_arr). "') " => null,
				"room_id" => $request->getParameter("room_id"),
				"module_id" => $module_id
			);
			if(count($uploads_where_params) > 0) {
				$where_params = array_merge($where_params, $uploads_where_params);
			}
			$result = $this->_db->updateExecute("uploads", $params, $where_params, false);
			if($result === false) {
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * アップロードしたupload_idのコピー
	 * WYSIWYG用
	 * @param  array  $upload_id_arr
	 * @param  int    $room_id コピーするルームID
	 * @param  array  $uploads_where_params アップロードテーブル追加条件指定
	 * 						room_id、module_idは必ずチェックし、そのWysiwyg上でuploadsされたものが正しい
	 * 						かどうかをチェックする。
	 * 						その他、条件を追加したければ、このパラメータより追加（マージ）
	 * 
	 * ※厳密にいえば、room_id、module_idだけのチェックでは、お知らせを同じルーム内に２つあり、
	 * 　1つ目のお知らせからアップロードし、2つ目にそのパスをコピーした段階で
	 * 　2つ目のお知らせを移動してしまうと、1つ目のお知らせから画像が消えてしまうため
	 * 　問題となる。しかし、現状、そのuploadしたファイルが、そのWYSIWYG内で本当に
	 * 　アップロードしたかどうかを知る術がないため、対応しない。
	 * 
	 * @return array $new_upload_id_arr 
	 * @access	public
	 */
	function copyWysiwygUploads(&$upload_id_arr, $room_id, $uploads_where_params = array())
	{
		$container =& DIContainerFactory::getContainer();
        $request =& $container->getComponent("Request");
        $session =& $container->getComponent("Session");
        
		if(count($upload_id_arr) > 0) {
			// TODO:アップロードされているファイルの数が1000000件？以上に拡大した場合、
			// SQL文の長さ制限（16M?)を越える可能性あり。
			// 現状、対処しない。
			$module_id = $request->getParameter("module_id");
			if(!isset($module_id)) {
				$actionChain =& $container->getComponent("ActionChain");
				$modulesView =& $container->getComponent("modulesView");
	        	$curAction = $actionChain->getCurActionName();
	        	$pathList = explode("_", $curAction);
	        	$module = $modulesView->getModuleByDirname($pathList[0]);
	        	if(isset($module['module_id'])) $module_id = $module['module_id'];
			}
			$where_params = array(
				"upload_id IN ('". implode("','", $upload_id_arr). "') " => null,
				"room_id" => $request->getParameter("room_id"),
				"module_id" => $module_id
			);
			if(count($uploads_where_params) > 0) {
				$where_params = array_merge($where_params, $uploads_where_params);
			}
			
			$uploads = $this->_db->selectExecute("uploads", $where_params);
			if($uploads === false) {
				return false;
			}
			if(!isset($uploads[0])) {
				return array();
			}
			$uploadsAction =& $this->_container->getComponent("uploadsAction");
			$fileAction =& $this->_container->getComponent("fileAction");
			$buf_new_upload_id_arr = array();
			foreach($uploads as $upload) {
				$upload_id = $upload["upload_id"];
				$upload['room_id'] = intval($room_id);
				$old_name = $upload['physical_file_name'];
				$upload['physical_file_name'] = '';
				$upload['sess_id'] = '';
				$upload['update_user_id'] = $session->getParameter("_user_id");
				$upload['update_user_name'] = $session->getParameter("_handle");
				$result = $uploadsAction->insUploads($upload);
				if($result === false) {
					return false;
				}
				$new_name = $result.".".$upload['extension'];
				if(file_exists(FILEUPLOADS_DIR. $upload['file_path'] .$old_name)) {
	        		$fileAction->copyFile(FILEUPLOADS_DIR. $upload['file_path'] .$old_name, FILEUPLOADS_DIR. $upload['file_path'] .$new_name);
	        	}
	        	$buf_new_upload_id_arr[$upload_id] = $result;
			}
			// 同じupload_idを考慮するため、$upload_id_arrに再セット
			$count = 0;
			$new_upload_id_arr = array();
			$buf_upload_id_arr = array();
			foreach($upload_id_arr as $upload_id) {
				if(!isset($buf_upload_id_arr[$upload_id])) {
					$buf_upload_id_arr[$upload_id] = $buf_new_upload_id_arr[$upload_id];
				}
				$new_upload_id_arr[$count] = $buf_upload_id_arr[$upload_id];
				$count++;
			}
		}
		return $new_upload_id_arr;
	}
}
?>
