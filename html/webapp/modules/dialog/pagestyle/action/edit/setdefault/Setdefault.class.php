<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 規定値に戻す
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Dialog_Pagestyle_Action_Edit_SetDefault extends Action
{
	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $all_apply = null;
	var $sesson_only = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $getdata = null;
	var $pagesView = null;
	var $pagesAction = null;
	var $db = null;
	
    /**
     * 規定値に戻す
     * @access  public
     */
    function execute()
    {
    	$pages = $this->getdata->getParameter("pages");
    	$config = $this->getdata->getParameter("config");
    	$page_theme_name = $pages[$this->page_id]['theme_name'];
    	/*
    	$themeStrList = explode("_", $page_theme_name);
		if(count($themeStrList) == 1) {
			$css_dir_name = $themeStr;
			//$themeCssPath = "/themes/".$page_theme_name."/css";
		} else {
			$bufthemeStr = array_shift ( $themeStrList );
			$css_dir_name = $bufthemeStr."/".implode("/", $themeStrList);
			//$themeCssPath = "/themes/".$bufthemeStr."/css/".implode("/", $themeStrList);
		}
		*/
		if($this->sesson_only == null || $this->sesson_only == _OFF) {
			if($this->all_apply == _ON && $this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
	    		$where_params = array(
					"dir_name" => $page_theme_name,
					"type" => _CSS_TYPE_PAGE_CUSTOM
				);
				$result = $this->db->deleteExecute("css_files", $where_params);
				if($result === false) {
					return 'error';	
				}
	    	}
	    	$pages_style = $this->pagesView->getPagesStyle(array("set_page_id" => $this->page_id));
	    	if(isset($pages_style[0])) {
	    		if($pages[$this->page_id]['space_type'] == _SPACE_TYPE_GROUP && $pages[$this->page_id]['private_flag'] == _ON)	{
					// プライベートスペース
					$theme_post_fix = "_private";
				} else if($pages[$this->page_id]['space_type'] == _SPACE_TYPE_GROUP) {
					// グループスペース
					$theme_post_fix = "_group";
				} else {
					// パブリックスペース
					$theme_post_fix = "_public";
				}
	    		
	    		if($pages_style[0]['temp_name'] == $config[_PAGESTYLE_CONF_CATID]['default_temp']['conf_value'] && 
	    			$pages_style[0]['theme_name'] == $config[_PAGESTYLE_CONF_CATID]['default_theme' . $theme_post_fix]['conf_value'] && 
	    			$pages_style[0]['leftcolumn_flag'] == $config[_PAGESTYLE_CONF_CATID]['leftcolumn_flag']['conf_value'] && 
	    			$pages_style[0]['rightcolumn_flag'] == $config[_PAGESTYLE_CONF_CATID]['rightcolumn_flag']['conf_value'] && 
	    			$pages_style[0]['header_flag'] == $config[_PAGESTYLE_CONF_CATID]['header_flag']['conf_value'] && 
	    			$pages_style[0]['footer_flag'] == $config[_PAGESTYLE_CONF_CATID]['footer_flag']['conf_value']) {
	    			//削除
	    			$this->pagesAction->delPageStyleById($this->page_id);
	    		} else {
	    			//アップデート
	    			$pages_style[0]['body_style'] = '';
	    			$pages_style[0]['header_style'] = '';
	    			$pages_style[0]['footer_style'] = '';
	    			$pages_style[0]['leftcolumn_style'] = '';
	    			$pages_style[0]['centercolumn_style'] = '';
	    			$pages_style[0]['rightcolumn_style'] = '';
	    			$this->pagesAction->updPageStyle($pages_style[0], array("set_page_id" => $this->page_id));
	    		}
	    	}
		}
    	
    	$pagestyle_list = null;
		$pagestyle_list[$this->page_id]['theme_name'] = $pages[$this->page_id]['theme_name'];
		$pagestyle_list[$this->page_id]['header_flag'] = $pages[$this->page_id]['header_flag'];
		$pagestyle_list[$this->page_id]['leftcolumn_flag'] = $pages[$this->page_id]['leftcolumn_flag'];
		$pagestyle_list[$this->page_id]['rightcolumn_flag'] = $pages[$this->page_id]['rightcolumn_flag'];
		$this->session->setParameter("pagestyle_list", $pagestyle_list);
    	
		return 'success';
    }
}
?>
