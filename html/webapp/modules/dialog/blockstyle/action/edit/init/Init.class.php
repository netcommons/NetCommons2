<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロックテーマ変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Dialog_Blockstyle_Action_Edit_Init extends Action
{
	
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $block_name = null;
	var $theme_kind = null;
	var $template_kind = null;
	var $minwidthsize = null;
	var $topmargin = null;
	var $rightmargin = null;
	var $bottommargin = null;
	var $leftmargin = null;
	
	var $color = null;
	
	var $blockstyle_parent_id_name = null;  // Top_id
	var $blockstyle_all_apply = null;		// 設定中のブロックテーマすべてに適用
	
	// コンポーネントを使用するため
	var $getData = null;
	var $session = null;
	var $blocksAction = null;
	var $db = null;
	
    /**
     * 
     *
     * @access  public
     */
    function execute()
    {
    	$time = timezone_date();
    	$user_id = $this->session->getParameter("_user_id");
        $user_name = $this->session->getParameter("_handle");
        
    	$blocks_obj =& $this->getData->getParameter("blocks");
    	$blocks_obj[$this->block_id]['update_time'] = $time;
    	$blocks_obj[$this->block_id]['update_user_id'] = $user_id;
    	$blocks_obj[$this->block_id]['update_user_name'] = $user_name;
    	$blocks_obj[$this->block_id]['block_name'] = $this->block_name;
    	if( $this->theme_kind == "_auto") {
    		 $this->theme_kind = "";	//任意	
    	}
    	$blocks_obj[$this->block_id]['theme_name'] = $this->theme_kind;
    	$blocks_obj[$this->block_id]['temp_name'] = $this->template_kind;
    	$blocks_obj[$this->block_id]['min_width_size'] = $this->minwidthsize;
    	$blocks_obj[$this->block_id]['topmargin'] = $this->topmargin;
    	$blocks_obj[$this->block_id]['rightmargin'] = $this->rightmargin;
    	$blocks_obj[$this->block_id]['bottommargin'] = $this->bottommargin;
    	$blocks_obj[$this->block_id]['leftmargin'] = $this->leftmargin;
    	
    	$result = $this->blocksAction->updBlock($blocks_obj[$this->block_id],array("block_id"=>$this->block_id), false);
    	
    	//スタイルシート書き換え
    	//if($this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
    		$append_class_name = "";
    		$type = _CSS_TYPE_BLOCK_CUSTOM;
    		$ins_block_id = 0;
    		if($this->blockstyle_all_apply != _ON || $this->session->getParameter("_user_auth_id") != _AUTH_ADMIN) {
    			$append_class_name = " .blockstyle_" . intval($this->block_id). " ";
    			$type = _CSS_TYPE_BLOCK_CUSTOM;
    			$ins_block_id = intval($this->block_id);
    		}
	    	if(is_array($this->color)) {
	    		if($this->theme_kind == null || $this->theme_kind == "") {
					//ページテーマにより自動的に選択中
					$themeList = $this->session->getParameter("_theme_list");
					$pages =& $this->getData->getParameter("pages");
					$theme_kind = $themeList[$pages[$blocks_obj[$this->block_id]['page_id']]['display_position']];
				} else {
					$theme_kind = $this->theme_kind;
				}
				$themeStrList = explode("_", $theme_kind);
				$css_thread_num = count($themeStrList);
				
				//$pattern_str = "/\.\/themes\/images\/background\//i";
				//$replace_prefix = "../../";
				//for($i = 0; $i < $css_thread_num; $i++) {
				//	$replace_prefix .= "../";
				//}
				//$replace_str = $replace_prefix . "themes/images/background/";
			
	    		$css_str = ""; 
		    	foreach($this->color as $class_name => $property_name_list) {
		    		// 設定中のブロックテーマすべてに適用は管理者のみ有効
		    		$class_name = preg_replace("/\s+/", " .", trim($class_name));
		    		//$class_name = preg_replace("/\s+/", " .", trim($class_name));
		    		$css_str .= $append_class_name."." . $class_name . " {";
		    		foreach($property_name_list as $property_name=>$color) {
		    			if($property_name == "background") {
		    				//$buf_name = preg_replace($pattern_str, $replace_str, $color);
		    				$buf_name = $color;
		    				if($buf_name == "") $buf_name = "none";
		    				$css_str .= "\n".$property_name.":".$buf_name.";";
		    			} else {
		    				$css_str .= "\n".$property_name.":".$color.";";
		    			}
		    		}
		    		$css_str .= "\n}\n";
		    		//
		    		// highlightならば、hover_highlight追加
		    		//
		    		$classList = explode(" ", $class_name);
		    		if(count($classList) == 2 && $classList[1] == ".highlight") {
		    			$css_str .= $append_class_name.".".$classList[0]." a.hover_highlight:hover"." {";
			    		foreach($property_name_list as $property_name=>$color) {
			    			$css_str .= "\n".$property_name.":".$color.";";
			    		}
			    		$css_str .= "\n}\n";
		    		}
		    	}
				if($css_str != "") {
		    		$result = $this->db->selectExecute("css_files", array("dir_name" => $theme_kind, "type" => $type, "block_id" => $ins_block_id), null, 1);
					if($result === false) {
						return 'error';
					}
					if(isset($result[0])) {
						if($css_str != $result[0]['data']) {
							// アップデート	
							$params=array(
										"data" => $css_str
									);
							$where_params=array(
								"dir_name" => $theme_kind,
								"type" => $type,
								"block_id" => $ins_block_id
							);
							$result = $this->db->updateExecute("css_files", $params, $where_params, true);
						}
					} else {
						// インサート
						$params=array(
									"dir_name" => $theme_kind,
									"type" => $type,
									"data" => $css_str,
									"block_id" => $ins_block_id
								);
						$result = $this->db->insertExecute("css_files", $params, true);
					}
					if($ins_block_id == 0) {
						// 削除処理
						$where_params = array(
							"type" => _CSS_TYPE_BLOCK_CUSTOM,
							"block_id" => intval($this->block_id)
						);
						$result = $this->db->deleteExecute("css_files", $where_params);
						if($result === false) {
							return 'error';	
						}
						
					}
		    	}
	    	}
    	//}
    	if($result)
    		return 'success';
    	else
    		return 'error';
    }
}
?>
