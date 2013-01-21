<?php
/**
 * ブロックスタイル表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Dialog_Blockstyle_View_Edit_Init extends Action
{
	//リクエストパラメータ
	var $block_id = null;
	var $parent_id_name = null;
	var $inside_flag = null;
	var $pre_blocktheme_name = null;
	var $blocktheme_name = null;
	var $active_tab = null;
	var $prefix_id_name = null;
	
	//使用コンポーネント
	var $blockstyleCompmain = null;
	var $getData = null;
	//var $token = null;
	var $session = null;
	var $fileView = null;
	var $db = null;
	
	//テンプレートで使用するため
	var $block_obj = null;
	var $temp_arr = null;
	var $category_list = null;
	var $blocktheme_list = null;
	var $blocktheme_customlist = array();
	var $image_path = null;
	//var $token_name = null;
	var $act_category = null;
	
	var $current_theme_name = null;
	var $custom_color_list = null;
	var $custom_property_list = null;
	var $custom_theme_lang = null;
	
	var $background_image = "";
	var $background_image_lang = "";
	
	// ブロックテーマすべての適用
	var $all_apply = false;
	
	function execute()
	{
		// アクティブタブ
		if($this->active_tab == null) {
			$this->active_tab =0;
		}
		
		// 内部表示か、ダイアログ表示か
		//グループ化したブロックは、ダイアログ表示となる
		$this->inside_flag = $this->inside_flag ? 1 : 0;
		
		$blocks =& $this->getData->getParameter("blocks");
		if(isset($blocks[$this->block_id])) {
			$this->pre_blocktheme_name = $blocks[$this->block_id]['theme_name'];
			if($this->blocktheme_name != null) {
				if($this->blocktheme_name == "_auto") {
					$this->blocktheme_name = "";
				}
				$blocks[$this->block_id]['theme_name'] = $this->blocktheme_name;
			}
			
			$block_obj = $blocks[$this->block_id];
			$this->block_obj =& $block_obj;
			
			//
			// テンプレート関連
			//
			$pathList = explode("_", $block_obj['action_name']);
			$temp_path = MODULE_DIR  . "/" . $pathList[0]. "/templates/";
			$this->temp_arr = null;
			if($block_obj['action_name'] != "pages_view_grouping") {
				//グルーピングブロックではなければ
				$temp_arr = $this->fileView->getCurrentDir($temp_path);
				if($temp_arr) {
					sort($temp_arr);
					$this->temp_arr = $temp_arr;
				}
			}
			//テーマ
			list($this->category_list, $background_list, $this->blocktheme_list, $this->image_path, $this->act_category) = 
				$this->blockstyleCompmain->getThemeList($block_obj['theme_name']);
			
			//配色-カスタム
			$this->current_theme_name = null;
			$this->custom_color_list=null;
			$this->custom_property_list=null;
			$this->custom_theme_lang = null;
			//if($this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
				if($block_obj['theme_name'] == null || $block_obj['theme_name'] == "") {
					//ページテーマにより自動的に選択中
					$themeList = $this->session->getParameter("_theme_list");
					$pages =& $this->getData->getParameter("pages");
					$this->current_theme_name = $themeList[$pages[$block_obj['page_id']]['display_position']];
				} else {
					$themeStrList = explode("_", $block_obj['theme_name']);
					if(count($themeStrList) == 1) {
						$themeDir = "/themes/".$block_obj['theme_name']."/templates/block.html";
					} else {
						$bufthemeStr = array_shift ( $themeStrList );
						$themeDir = "/themes/".$bufthemeStr."/templates/".implode("/", $themeStrList) . "/block.html";
					}
					if(!file_exists(STYLE_DIR .$themeDir)) {
						$themeList = $this->session->getParameter("_theme_list");
						$pages =& $this->getData->getParameter("pages");
						$this->current_theme_name = $themeList[$pages[$block_obj['page_id']]['display_position']];
						$block_obj['theme_name'] = "";
					} else {
						$this->current_theme_name = $block_obj['theme_name'];
					}
				}
				
				list($this->custom_color_list, $this->background_image, $this->background_image_lang, $this->custom_property_list, $this->custom_theme_lang) =
					$this->blockstyleCompmain->getCustomColorList($this->current_theme_name, $background_list);
			//}
			if($this->session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
				// 管理者ならば、「設定中のブロックテーマすべてに適用」中かどうかを取得
				$result = $this->db->selectExecute("css_files", array("dir_name" => $this->current_theme_name, "type" => _CSS_TYPE_BLOCK_CUSTOM, "block_id" => 0), null, 1);
				if($result === false) {
					return 'error';
				}
				if(isset($result[0])) {
					$this->all_apply = true;
					$result = $this->db->selectExecute("css_files", array("dir_name" => $this->current_theme_name, "type" => _CSS_TYPE_BLOCK_CUSTOM, "block_id" => intval($this->block_id)), null, 1);
					if($result === false) {
						return 'error';
					}
					if(isset($result[0])) {
						$this->all_apply = false;
					}
				}
			}
		}
		////$this->token_name = $this->token->getValue();
		return 'success';
	}
}
?>
