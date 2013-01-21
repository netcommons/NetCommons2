<?php
/**
 * ページスタイル表示
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Dialog_Pagestyle_View_Edit_Init extends Action
{	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $theme_name = null;
	var $change_flag = null;
	var $active_tab = null;
	var $header_flag = null;
	var $leftcolumn_flag = null;
	var $rightcolumn_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $pagestyleCompmain = null;
	var $getdata = null;
	var $session = null;
	var $fileView = null;
	var $pagesView = null;
	var $db = null;
	
	// 値をセットするため
	var $pages = null;
	
	var $category_list = null;
	var $pagetheme_list = null;
	////var $pagetheme_customlist = null;
	var $image_path = null;
	var $act_category = null;
	
	//var $dir_list = null;
	//var $pagestyle_list = null;
	var $type = null;
	var $change_blocktheme = "";
	var $background_color = "";
	var $background_image = "";
	var $background_image_lang = "";
	
	var $all_apply = false;
	//var $allow_layout_flag = null;
	
	var $headercolumn = "";
	var $leftcolumn = "";
	var $rightcolumn = "";
	var $centercolumn = "";
	var $footercolumn = "";
	
	var $perma_parent_permalink = "";
	var $pages_meta_inf = array();
	
	function execute()
	{
		$pages =& $this->getdata->getParameter("pages");
		
		$this->pages =& $pages[$this->page_id];
		
		if(!isset($this->theme_name)) {
			$this->theme_name = $pages[$this->page_id]['theme_name'];
		}
		$lang = $this->session->getParameter("_lang");
		
		if($this->header_flag != null) {
			$pages[$this->page_id]['header_flag'] = $this->header_flag;
		}
		
		if($this->leftcolumn_flag != null) {
			$pages[$this->page_id]['leftcolumn_flag'] = $this->leftcolumn_flag;
		}
		
		if($this->rightcolumn_flag != null) {
			$pages[$this->page_id]['rightcolumn_flag'] = $this->rightcolumn_flag;
		}
		
		if($pages[$this->page_id]['header_flag']) {
			//B C D E
			if($pages[$this->page_id]['leftcolumn_flag']) {
				//C E
				if($pages[$this->page_id]['rightcolumn_flag']) {
					$this->type = "E";
				} else {
					$this->type = "C";	
				}
			} else {
				//B D
				if($pages[$this->page_id]['rightcolumn_flag']) {
					$this->type = "D";
				} else {
					$this->type = "B";	
				}
			}
		} else {
			
			//A F G H
			if($pages[$this->page_id]['leftcolumn_flag']) {
				//F H
				if($pages[$this->page_id]['rightcolumn_flag']) {
					$this->type = "H";
				} else {
					$this->type = "F";	
				}
			} else {
				//A G
				if($pages[$this->page_id]['rightcolumn_flag']) {
					$this->type = "G";
				} else {
					$this->type = "A";	
				}
			}
		}
		
		list($this->category_list, $background_list, $this->pagetheme_list, $this->image_path, $this->act_category) = 
				$this->pagestyleCompmain->getThemeList($this->theme_name, "page");
		
		// 配色-カスタム
		//ページテーマに対応したブロックテーマ読み込み
		//$this->change_blocktheme = "";
		//$this->background_color = "";
		
		//$this->headercolumn = "";
		//$this->leftcolumn = "";
		//$this->rightcolumn = "";
		//$this->centercolumn = "";
		//$this->footercolumn = "";
		
		$page_theme_name = $this->theme_name; //$pages[$this->page_id]['theme_name'];
		
		$theme_list = null;
		$themeStrList = explode("_", $page_theme_name);
    	if(count($themeStrList) == 1) {
			$themeCssPath = "/themes/".$page_theme_name."/config";
			if(file_exists(STYLE_DIR. $themeCssPath."/"._PAGETHEME_CUSTOM_INIFILE)) {
				$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/"._PAGETHEME_CUSTOM_INIFILE,true);
			}
		} else {
			$bufthemeStr = array_shift ( $themeStrList );
			$themeCssPath = "/themes/".$bufthemeStr."/config/";
			if(file_exists(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/"._PAGETHEME_CUSTOM_INIFILE)) {
				$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/"._PAGETHEME_CUSTOM_INIFILE,true);
			} else if(file_exists(STYLE_DIR. $themeCssPath."/"._PAGETHEME_CUSTOM_INIFILE)) {
				$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/"._PAGETHEME_CUSTOM_INIFILE,true);
			} 
		}
		
		if($theme_list != null) {
			$theme_name_arr = explode("_",$this->theme_name);
			$theme_first_name = $theme_name_arr[0];
			
			if(is_array($theme_list)) {
				$border_array = array("border-top-color"=>"border_top_color","border-right-color"=>"border_right_color","border-bottom-color"=>"border_bottom_color","border-left-color"=>"border_left_color");
				foreach($theme_list as $key => $value) {
					if(isset($value)) {
						//if(isset($theme_list[$key]['change_blocktheme']) && $theme_list[$key]['change_blocktheme'] == 1) {
						//	$this->change_blocktheme[$key] = 1;
						//} else {
						//	$this->change_blocktheme[$key] = 0;
						//}
						
						if(isset($theme_list[$key]['background-color'])) {
							$background_arr = explode(",",$theme_list[$key]['background-color']);
							$background_count = 0;
							foreach($background_arr as $background_value) {
								if(preg_match("/^selection_image\(([^:]+)\):(.+)/", $background_value,$background_matches)) {
									$background_matches_arr = explode(":",$background_matches[2]);
									$this->background_color[$key][$background_count] = "selection_image";
									foreach($background_matches_arr as $background_matche) {
										$background_file_list = null;
										$background_prefix_str = $background_matches[1];
										if(preg_match("/^(.+)\((.+)\)/", $background_matche,$buf_background_matche)) {
											$background_matche = $buf_background_matche[1];
											$background_prefix_str = $buf_background_matche[2];
										}
										if(CORE_BASE_URL == BASE_URL) {
											$background_prefix = "./themes/images/background/".$background_matche."/";
										} else {
											// 別サーバのソースから読み込んでいて、別サーバの設置URLが変更した場合は、手動でcss_filesのCORE_BASE_URL
											// の変換処理を施す必要あり
											$background_prefix = CORE_BASE_URL."/themes/images/background/".$background_matche."/";	
										}
										if(file_exists(STYLE_DIR."/themes/".$theme_first_name."/images/background/". $background_matche ."/")) {
											// 各テーマの下(images/background/(name))にフォルダが存在する
											$background_file_list = $this->fileView->getCurrentFiles(STYLE_DIR."/themes/".$theme_first_name."/images/background/". $background_matche ."/");
										} else if(file_exists(STYLE_DIR."/images/background/". $background_matche ."/")) {
											$background_file_list = $this->fileView->getCurrentFiles(STYLE_DIR."/images/background/". $background_matche ."/");
										}
										if($background_file_list != null) {
											foreach($background_file_list as $background_file) {
												$this->background_image[$key][$background_count][] = $background_prefix_str . " url('" . $background_prefix. $background_file . "')";
												if(isset($background_list[$background_matche][$background_file])) {
													$this->background_image_lang[$key][$background_count][] = $background_list[$background_matche][$background_file];
												} else {
													$this->background_image_lang[$key][$background_count][] = $background_file;
												}
											}
										}
									}
									
								} else {
									$this->background_color[$key][$background_count] = $background_value;
								}
								$background_count++;
							}
							
							//$this->background_color[$key] = explode(",",$theme_list[$key]['background-color']);
						} else {
							$this->background_color[$key] = "";
						}
						
						foreach($border_array as $border => $border_value) {
							if(isset($theme_list[$key][$border])) {
								if($key == "general") {
									$this->headercolumn[$border_value] = explode(",",$theme_list[$key][$border]);
									$this->leftcolumn[$border_value] = explode(",",$theme_list[$key][$border]);
									$this->rightcolumn[$border_value] = explode(",",$theme_list[$key][$border]);
									$this->centercolumn[$border_value] = explode(",",$theme_list[$key][$border]);
									$this->footercolumn[$border_value] = explode(",",$theme_list[$key][$border]);
								} else {
									$this->$key[$border_value] = explode(",",$theme_list[$key][$border]);
								}
							} else {
								$this->$key[$border_value] = "";
							}
						}
						
					}
				}
			}
		}
		//設定中のページテーマすべてに適用
		if($this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
			//$this->all_apply = false;
			//設定中のページテーマすべてに適用していなければ、checkをはずしておく
			if($pages[$this->page_id]['body_style'] == '' &&
				$pages[$this->page_id]['header_style'] == '' &&
				$pages[$this->page_id]['footer_style'] == '' &&
				$pages[$this->page_id]['leftcolumn_style'] == '' &&
				$pages[$this->page_id]['centercolumn_style'] == '' &&
				$pages[$this->page_id]['rightcolumn_style'] == '') {
				$this->all_apply = true;
			}	
		}
		
		//
    	//　固定リンクの取得
    	//
    	if(!$this->_setParamLink()) {
    		return 'error';
    	}
    
		//
    	// Title Meta情報の取得
    	//
    	if(!$this->_setMetaInf()) {
    		return 'error';
    	}
		return 'success';
	}
	
	/**
     * 固定リンクの値セット
     *  $this->permalink
     *  $this->perm_parent_pages
     *  をセット
     * @access  private
     */
    function _setParamLink() {
    	//$this->permalink = $this->page['permalink'];
    	$permalink_arr = explode('/', $this->pages['permalink']);
    	$all_count = count($permalink_arr);
    	$count = 1;
    	foreach($permalink_arr as $permalink) {
    		if($all_count == $count) {
    			$this->permalink = $permalink;
    		} else {
    			if($this->perma_parent_permalink != "") {
    				$this->perma_parent_permalink .= '/';
    			}
    			$this->perma_parent_permalink .= $permalink;
    		}
    		$count++;	
    	}

    	return true;
    }
	
	/**
     * title,meta情報セット
     *
     * @access  private
     */
    function _setMetaInf() {
    	$where_params = array(
    		"page_id" => $this->pages['page_id']
    	);
    	$pages_meta_inf = $this->db->selectExecute("pages_meta_inf", $where_params, null, 1, 0, array($this,"_fetchcallbackMetaInf"));
		if($pages_meta_inf === false) {
			return false;
		}
		$this->pages_meta_inf = $pages_meta_inf;
		return true;
    }
    
	 /**
	 * fetch時コールバックメソッド
	 *  値がセットされていなければ、configのmeta情報からセット
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallbackMetaInf($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret = $row;
		}
		$meta = $this->pagesView->getDafaultMeta($this->pages);
		$ret['title'] =!isset($ret['title']) ? $meta['title'] : $ret['title'];
		$ret['sitename'] = $meta['sitename'];
		$ret['meta_keywords'] = !isset($ret['meta_keywords']) ? $meta['meta_keywords'] : $ret['meta_keywords'];
		$ret['meta_description'] = !isset($ret['meta_description']) ? $meta['meta_description'] : $ret['meta_description'];
		
		return $ret;
	}
}
?>
