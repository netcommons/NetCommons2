<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページテーマ変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Dialog_Pagestyle_Action_Edit_Init extends Action
{
	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $all_apply = null;
	var $page_name = null;
	
	var $permalink = null;
	var $titletag = null;
	var $meta_description = null;
	var $meta_keywords = null;
	
	// Validatorによりセット
	var $set_permalink = null;
	var $meta = null;
	
	
	// コンポーネントを使用するため
	var $getdata = null;
	var $session = null;
	var $pagesView = null;
	var $pagesAction = null;
	var $db = null;
	var $configView = null;
	
    /**
     * ページテーマ変更
     *
     * @access  public
     */
    function execute()
    {
    	//TODO:そのテーマが存在するかどうかのチェックは必要
		$pages =& $this->getdata->getParameter("pages");
		
		$page_name = $pages[$this->page_id]['page_name'];
		if($pages[$this->page_id]['space_type'] == _SPACE_TYPE_PUBLIC && 
			$pages[$this->page_id]['thread_num'] == 1 && 
			$pages[$this->page_id]['display_sequence'] == 1) {
			$this->permalink = "";
			$this->titletag = "";
		}
		
		$theme_name = $pages[$this->page_id]['theme_name'];
		$temp_name = $pages[$this->page_id]['temp_name'];
		$header_flag = $pages[$this->page_id]['header_flag'];
		$footer_flag = $pages[$this->page_id]['footer_flag'];
		$leftcolumn_flag = $pages[$this->page_id]['leftcolumn_flag'];
		$rightcolumn_flag = $pages[$this->page_id]['rightcolumn_flag'];
		$body_style = $pages[$this->page_id]['body_style'];
		$header_style = $pages[$this->page_id]['header_style'];
		$leftcolumn_style = $pages[$this->page_id]['leftcolumn_style'];
		$centercolumn_style = $pages[$this->page_id]['centercolumn_style'];
		$rightcolumn_style = $pages[$this->page_id]['rightcolumn_style'];
		$footer_style =  $pages[$this->page_id]['footer_style'];
		
		$align =  $pages[$this->page_id]['align'];
		$leftmargin =  intval($pages[$this->page_id]['leftmargin']);
		$rightmargin =  intval($pages[$this->page_id]['rightmargin']);
		$topmargin =  intval($pages[$this->page_id]['topmargin']);
		$bottommargin =  intval($pages[$this->page_id]['bottommargin']);
		
		$buf_page =& $this->pagesView->getPageById($this->page_id);
		
		if($align == "center") {
			//中央表示ならば、左右マージンは0に戻す
			$leftmargin = 0;
			$rightmargin = 0;
		}
		/*
		$pagestyle_list = $this->session->getParameter("pagestyle_list");
		foreach($pagestyle_list[$this->page_id] as $key => $pagetheme) {
			if($key == "theme_name") {
				$theme_name = $pagetheme;	
			} else if($key == "header_fla") {
				$header_fla = $pagetheme;
			} else if($key == "leftcolumn_flag") {
				$leftcolumn_flag = $pagetheme;
			} else if($key == "rightcolumn_flag") {
				$rightcolumn_flag = $pagetheme;
			} else if($key == "body_backgroundColor") {
				$body_style = $pagetheme;
			} else if(preg_match("/^headercolumn_/", $key)) {
				$header_style .= $pagetheme;
			} else if(preg_match("/^leftcolumn_/", $key)) {
				$leftcolumn_style .= $pagetheme;
			} else if(preg_match("/^centercolumn_/", $key)) {
				$centercolumn_style .= $pagetheme;
			} else if(preg_match("/^rightcolumn_/", $key)) {
				$rightcolumn_style .= $pagetheme;
			} else if(preg_match("/^footercolumn_/", $key)) {
				$footer_style .= $pagetheme;
			}
		}
		*/
		//ページ名称Update
    	if(isset($buf_page) && $buf_page['page_name'] != $this->page_name && $this->page_name != null) {
    		$this->pagesAction->updPagename($this->page_id, $this->page_name);
    	}
		
		if($this->all_apply == _ON && $this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
			//
			//管理者ならば、ページテーマすべてに適用も可能にしている
			//
			//スタイルシート書き換え
			$themeStrList = explode("_", $theme_name);
			$css_thread_num = count($themeStrList);
			/*
			if($css_thread_num == 1) {
				$css_dir_name = $themeStr;
			} else {
				$bufthemeStr = array_shift ( $themeStrList );
				$css_dir_name = $bufthemeStr."/".implode("/", $themeStrList);
			}
			*/
			
			//$pattern_str = "/\.\/themes\/images\/background\//i";
			//$replace_prefix = "../../";
			//for($i = 0; $i < $css_thread_num; $i++) {
			//	$replace_prefix .= "../";
			//}
			//$replace_str = $replace_prefix . "themes/images/background/";
			$css_str = ""; 
    		if($body_style != "") {
    			$css_str .= "body {\n".
    				$body_style.
    			"\n}\n";
    		}
    		if($header_style != "") {
    			$css_str .= ".headercolumn {\n".
    				$header_style.
    			"\n}\n";
    		}
    		if($leftcolumn_style != "") {
    			$css_str .= ".leftcolumn {\n".
    				$leftcolumn_style.
    			"\n}\n";
    		}
    		if($centercolumn_style != "") {
    			$css_str .= ".centercolumn {\n".
    				$centercolumn_style.
    			"\n}\n";
    		}
    		if($rightcolumn_style != "") {
    			$css_str .= ".rightcolumn {\n".
    				$rightcolumn_style.
    			"\n}\n";
    		}
    		if($footer_style != "") {
    			$css_str .= ".footercolumn {\n".
    				$footer_style.
    			"\n}\n";
    		}
    		
	    	if($css_str != "") {
	    		$result = $this->db->selectExecute("css_files", array("dir_name" => $theme_name, "type" => _CSS_TYPE_PAGE_CUSTOM), null, 1);
				if($result === false) {
					return 'error';
				}
				if(isset($result[0])) {
					if($css_str != $result[0]['data']) {
						// アップデート	
						$params=array(
									"data" => $css_str
								);
						$where_params=array("dir_name" => $theme_name, "type" => _CSS_TYPE_PAGE_CUSTOM);
						$result = $this->db->updateExecute("css_files", $params, $where_params, true);
					}
				} else {
					// インサート
					$params=array(
								"dir_name" => $theme_name,
								"type" => _CSS_TYPE_PAGE_CUSTOM,
								"block_id" => 0,					// 0固定
								"data" => $css_str
							);
					$result = $this->db->insertExecute("css_files", $params, true);
				}
	    	}
	    	$pagestyle_list = $this->session->getParameter("pagestyle_list");
	    	if(is_array($pagestyle_list)) {
		    	foreach($pagestyle_list as $key=>$pagetheme) {
		    		if($key != $this->page_id) {
		    			//クリア
		    			unset($pagestyle_list[$key]);	
		    		}
		    	}
	    	}
    		$body_style = '';
    		$header_style = '';
    		$leftcolumn_style = '';
    		$centercolumn_style = '';
    		$rightcolumn_style = '';
    		$footer_style = '';
		}
		$config =& $this->getdata->getParameter("config");
		if($buf_page['private_flag'] == _ON) {
			$default_theme_name = $config[_PAGESTYLE_CONF_CATID]['default_theme_private']['conf_value'];
    	} else if($buf_page['space_type'] == _SPACE_TYPE_PUBLIC) {
			$default_theme_name = $config[_PAGESTYLE_CONF_CATID]['default_theme_public']['conf_value'];
		} else {
			$default_theme_name = $config[_PAGESTYLE_CONF_CATID]['default_theme_group']['conf_value'];
		}
		$default_temp_name = $config[_PAGESTYLE_CONF_CATID]['default_temp']['conf_value'];
		
		if($header_style == '' && $leftcolumn_style == '' && $centercolumn_style == '' && $rightcolumn_style == '' && $footer_style == '') {
			
			$default_header_flag = $config[_PAGESTYLE_CONF_CATID]['header_flag']['conf_value'];
			$default_footer_flag = $config[_PAGESTYLE_CONF_CATID]['footer_flag']['conf_value'];
			$default_leftcolumn_flag = $config[_PAGESTYLE_CONF_CATID]['leftcolumn_flag']['conf_value'];
			$default_rightcolumn_flag = $config[_PAGESTYLE_CONF_CATID]['rightcolumn_flag']['conf_value'];
			
			$default_align = $config[_PAGESTYLE_CONF_CATID]['align']['conf_value'];
			$default_leftmargin = $config[_PAGESTYLE_CONF_CATID]['leftmargin']['conf_value'];
			$default_rightmargin = $config[_PAGESTYLE_CONF_CATID]['rightmargin']['conf_value'];
			$default_topmargin = $config[_PAGESTYLE_CONF_CATID]['topmargin']['conf_value'];
			$default_bottommargin = $config[_PAGESTYLE_CONF_CATID]['bottommargin']['conf_value'];
			
			if($default_theme_name == $theme_name &&
				$default_temp_name == $temp_name &&
				$default_header_flag == $header_flag &&
				$default_footer_flag == $footer_flag &&
				$default_leftcolumn_flag == $leftcolumn_flag &&
				$default_rightcolumn_flag == $rightcolumn_flag &&
				$default_align == $align &&
				$default_leftmargin == $leftmargin &&
				$default_rightmargin == $rightmargin &&
				$default_topmargin == $topmargin && 
				$default_bottommargin == $bottommargin) {
				//
				// デフォルト値と同じなので削除処理
				//
				$result = $this->pagesAction->delPageStyleById($this->page_id);
				if($result === false) return 'error';
				
		    	$result = $this->pagesAction->updPermaLink($pages[$this->page_id], $this->permalink);
		    	if ($result === false) return 'error';
		    	
		    	if(!$this->_setMetaInf()) return 'error';
    		
				return 'success';
			}
		}
		$pages_style = $this->pagesView->getPagesStyle(array("set_page_id" => $this->page_id));
		if($default_theme_name == $theme_name) $theme_name = "";
		if($default_temp_name == $temp_name) $temp_name = "";
		if(isset($pages_style[0])) {
			//アップデート
			$pages_style[0]['theme_name'] = $theme_name;
			$pages_style[0]['temp_name'] = $temp_name;
			$pages_style[0]['header_flag'] = $header_flag;
			$pages_style[0]['footer_flag'] = $footer_flag;
			$pages_style[0]['leftcolumn_flag'] = $leftcolumn_flag;
			$pages_style[0]['rightcolumn_flag'] = $rightcolumn_flag;
			$pages_style[0]['body_style'] = $body_style;
			$pages_style[0]['header_style'] = $header_style;
			$pages_style[0]['footer_style'] = $footer_style;
			$pages_style[0]['leftcolumn_style'] = $leftcolumn_style;
			$pages_style[0]['centercolumn_style'] = $centercolumn_style;
			$pages_style[0]['rightcolumn_style'] = $rightcolumn_style;
			$pages_style[0]['align'] = $align;
			$pages_style[0]['leftmargin'] = $leftmargin;
			$pages_style[0]['rightmargin'] = $rightmargin;
			$pages_style[0]['topmargin'] = $topmargin;
			$pages_style[0]['bottommargin'] = $bottommargin;
			
			$result = $this->pagesAction->updPageStyle($pages_style[0], array("set_page_id" => $this->page_id));
    	} else {
    		$params = array(
				"set_page_id" => $this->page_id,
				"theme_name" => $theme_name,
				"temp_name" =>$temp_name,
				"header_flag" => $header_flag,
				"footer_flag" =>$footer_flag,
				"leftcolumn_flag" => $leftcolumn_flag,
				"rightcolumn_flag" =>$rightcolumn_flag,
				"body_style" => $body_style,
				"header_style" =>$header_style,
				"footer_style" => $footer_style,
				"leftcolumn_style" =>$leftcolumn_style,
				"centercolumn_style" =>$centercolumn_style,
				"rightcolumn_style" =>$rightcolumn_style,
				"align" =>$align,
				"leftmargin" =>$leftmargin,
				"rightmargin" =>$rightmargin,
				"topmargin" =>$topmargin,
				"bottommargin" =>$bottommargin
			);
    		$result = $this->pagesAction->insPageStyle($params);	
    		
    	}
    	
		if(!$result) {
    		return 'error';
    	}
    	
    	$result = $this->pagesAction->updPermaLink($pages[$this->page_id], $this->permalink);
    	if ($result === false) return 'error';
    	if(!$this->_setMetaInf()) return 'error';
    		
    	return 'success';
    }
    
    function _setMetaInf() {
    	//
    	// pages_meta_inf
    	// すべてデフォルト値ならば、insertしない
    	//  
    	$pages =& $this->getdata->getParameter("pages");
    	$config_meta = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _META_CONF_CATID);
    	if($this->meta['title'] == $this->titletag &&
    		$config_meta['meta_description']['conf_value'] == $this->meta_description &&
    		$config_meta['meta_keywords']['conf_value'] == $this->meta_keywords) {
    		// 変更なし
    		$where_params = array(
    			"page_id" => $pages[$this->page_id]['page_id']
    		);
    		$result = $this->db->deleteExecute("pages_meta_inf", $where_params);
    		if($result === false) return false;
    		
    		return true;
    	}
    	$this->titletag = ($this->meta['title'] == $this->titletag) ? null : $this->titletag;
    	$this->meta_keywords = preg_replace(DIALOG_PAGESTYLE_PATTERN_SEPARATOR, DIALOG_PAGESTYLE_REPLACEMENT_SEPARATOR, $this->meta_keywords);
    	$this->meta_keywords = ($config_meta['meta_keywords']['conf_value'] == $this->meta_keywords) ? null : $this->meta_keywords;
    	$this->meta_description = ($config_meta['meta_description']['conf_value'] == $this->meta_description) ? null : $this->meta_description;
    	
    	$where_params = array(
    			"page_id" => $pages[$this->page_id]['page_id']
		);
		$pages_meta_inf = $this->db->selectExecute("pages_meta_inf", $where_params);
    	if(!isset($pages_meta_inf[0])) {
    		$params = array(
	    		"page_id" => $pages[$this->page_id]['page_id'],
	    		"title" => $this->titletag,
				"meta_keywords" => $this->meta_keywords,
				"meta_description" => $this->meta_description
			);
			$result = $this->db->insertExecute("pages_meta_inf", $params, true);
    	} else {
    		$update_params = array(
    			"title" => $this->titletag,
				"meta_keywords" => $this->meta_keywords,
				"meta_description" => $this->meta_description
    		);
    		$result = $this->db->updateExecute("pages_meta_inf", $update_params, array("page_id" => $pages[$this->page_id]['page_id']));
    	}
    	
		if($result === false) return false;
		return true;
    }
}
?>
