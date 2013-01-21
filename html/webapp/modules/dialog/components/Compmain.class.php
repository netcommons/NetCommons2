<?php
/**
 *  ブロックスタイルコモンコンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Dialog_Components_Compmain {
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Dialog_Blockstyle_Components_Compmain() {
	}

	/**
	 * カスタムできる配色一覧を取得する
     * @param string $current_theme_name
     * @param array  $background_list
	 * @param string $theme_kind block or page
	 * @return array
	 * @access	public
	 */
	function getCustomColorList($current_theme_name, $background_list, $theme_kind = 'block') {
		//TODO:現状、ブロックの配色カスタマイズのみ実装（theme_kind=blockのみ）
		//     ページスタイルの配色も統一しようとしたが、「設定中のページテーマすべてに適用しない」場合に対応が難しくなるので未実装
		//     bodyのbackground-imageのアップロード等にも対応できていない
		//     page_custom.ini、 block_custom.iniの記述方法の書き方も統一されていないのも問題あり
		// allow_layout

		$container =& DIContainerFactory::getContainer();
        $commonMain =& $container->getComponent("commonMain");
        $fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");

		$custom_color_list = null;
		$custom_property_list = null;
		$custom_theme_lang = null;
		$background_image = "";
		$background_image_lang = "";

		if($theme_kind == 'block')
			$ini_file = _BLOCKTHEME_CUSTOM_INIFILE;
		else
			$ini_file = _PAGETHEME_CUSTOM_INIFILE;

		$theme_list = null;
		$themeStrList = explode("_", $current_theme_name);
		$lang = $this->_session->getParameter("_lang");
    	if(count($themeStrList) == 1) {
    		$bufthemeStr = $current_theme_name;
			$themeCssPath = "/themes/".$current_theme_name."/config";
			if(file_exists(STYLE_DIR. $themeCssPath."/".$ini_file)) {
				$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/".$ini_file,true);
			}
			$themeLangPath = "/themes/".$current_theme_name."/language/".$lang. "/";
			if(file_exists(STYLE_DIR. $themeLangPath."/".$ini_file)) {
				$custom_theme_lang = parse_ini_file(STYLE_DIR. $themeLangPath."/".$ini_file,true);
			}
		} else {
			$bufthemeStr = array_shift ( $themeStrList );
			$themeCssPath = "/themes/".$bufthemeStr."/config/";
			if(file_exists(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/".$ini_file)) {
				$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/".$ini_file,true);
			} else if(file_exists(STYLE_DIR. $themeCssPath."/".$ini_file)) {
				$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/".$ini_file,true);
			}
			$themeLangPath = "/themes/".$bufthemeStr."/language/".$lang. "/";
			if(file_exists(STYLE_DIR. $themeLangPath.implode("/", $themeStrList)."/".$ini_file)) {
				$custom_theme_lang = parse_ini_file(STYLE_DIR. $themeLangPath.implode("/", $themeStrList)."/".$ini_file,true);
			} else if(file_exists(STYLE_DIR. $themeLangPath."/".$ini_file)) {
				$custom_theme_lang = parse_ini_file(STYLE_DIR. $themeLangPath."/".$ini_file,true);
			}
		}

		if($theme_list != null) {
			if(is_array($theme_list)) {
				foreach($theme_list as $class_name => $value) {
					foreach($theme_list[$class_name] as $sub_key=>$sub_value) {
						$property_name = explode(",",$sub_value);
						foreach($property_name as $property_key=>$property_value) {
							if(preg_match("/^selection_image\(([^:]+)\):(.+)/", $property_value,$background_matches)) {
								$background_matches_arr = explode(":",$background_matches[2]);
								//$custom_color_list[$class_name][$sub_key] = "selection_image";
								$property_name[$property_key] = "selection_image";
								foreach($background_matches_arr as $background_matche) {
									$background_file_list = null;
									$background_prefix_str = $background_matches[1];
									if(preg_match("/^(.+)\((.+)\)/", $background_matche,$buf_background_matche)) {
										$background_matche = $buf_background_matche[1];
										$background_prefix_str = $buf_background_matche[2];
									}
									$background_prefix = get_image_url()."/themes/images/background/".$background_matche."/";
									if(file_exists(STYLE_DIR."/themes/".$bufthemeStr."/images/background/". $background_matche ."/")) {
										// 各テーマの下(images/background/(name))にフォルダが存在する
										$background_file_list = $fileView->getCurrentFiles(STYLE_DIR."/themes/".$bufthemeStr."/images/background/". $background_matche ."/");
									} else if(file_exists(STYLE_DIR."/images/background/". $background_matche ."/")) {
										$background_file_list = $fileView->getCurrentFiles(STYLE_DIR."/images/background/". $background_matche ."/");
									}
									if($background_file_list != null) {
										foreach($background_file_list as $background_file) {
											$background_image[$class_name][$sub_key][] = $background_prefix_str . " url('" . $background_prefix. $background_file . "')";
											if(isset($background_list[$background_matche][$background_file])) {
												$background_image_lang[$class_name][$sub_key][] = $background_list[$background_matche][$background_file];
											} else {
												$background_image_lang[$class_name][$sub_key][] = $background_file;
											}
										}
									}
								}
							} else if(preg_match("/^selection_auto:/",$property_value)) {
								$custom_property_list[$class_name][$sub_key] = explode(" ", preg_replace("/^selection_auto:/", "", $property_value));
								$property_name[$property_key] = "selection_auto";
							}
						}
						$custom_color_list[$class_name][$sub_key] = $property_name;
					}
				}
			}
		}

		return array(
			$custom_color_list,
			$background_image,
			$background_image_lang,
			$custom_property_list,
			$custom_theme_lang
		);
	}

	/**
	 * テーマの一覧を取得する
	 * @param string current_theme_name
	 * @param string theme_kind block or page
	 * @return array
	 * @access	public
	 */
	function getThemeList($current_theme_name, $theme_kind = 'block') {
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
        $fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");

		// ブロックカテゴリ一覧取得
		$categories_list = parse_ini_file(STYLE_DIR."/config/"._CATEGORY_INIFILE, true);
		$category_list = $categories_list[$theme_kind];
		$lang = $this->_session->getParameter("_lang");
		$theme_list = array();
		//$theme_customlist = array();
		if(file_exists(STYLE_DIR."/language/".$lang."/"._CATEGORY_INIFILE)) {
			//カテゴリ言語定義ファイルがあるならば、上書き
			$categories_list = parse_ini_file(STYLE_DIR."/language/".$lang."/"._CATEGORY_INIFILE, true);
			$lang_category_list = $categories_list[$theme_kind];
			foreach($lang_category_list as $key=>$category_name) {
				if(isset($category_list[$key])) {
					$category_list[$key] = $category_name;
					$theme_list[$key] = Array();
				}
			}
		}
		$background_list = "";
		if(file_exists(STYLE_DIR."/language/".$lang."/"._BACKGROUND_INIFILE)) {
			//背景言語定義ファイル
			$background_list = parse_ini_file(STYLE_DIR."/language/".$lang."/"._BACKGROUND_INIFILE, true);
		}

		$act_category = "";
		$themes_arr = $fileView->getCurrentDir(STYLE_DIR."/themes/");
		foreach($themes_arr as $theme_name) {
			//参加カテゴリiniファイル読み込み
			$themeconf_list = null;
			$themeStrList = explode("_", $theme_name);
			if(count($themeStrList) == 1) {
				$themeCssPath = "/themes/".$theme_name."/config";
				if(file_exists(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE)) {
					$themeconf_list = parse_ini_file(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE,true);
				}
			} else {
				$bufthemeStr = array_shift ( $themeStrList );
				$themeCssPath = "/themes/".$bufthemeStr."/config/";
				if(file_exists(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/"._THEME_INIFILE)) {
					$themeconf_list = parse_ini_file(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/"._THEME_INIFILE,true);
				} else if(file_exists(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE)) {
					$themeconf_list = parse_ini_file(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE,true);
				}
			}
			if(file_exists(STYLE_DIR."/themes/".$themeStrList[0]."/language/".$lang."/"._BACKGROUND_INIFILE)) {
				//背景言語定義ファイル
				$background_list = array_merge($background_list, parse_ini_file(STYLE_DIR."/themes/".$themeStrList[0]."/language/".$lang."/"._BACKGROUND_INIFILE, true));
			}
			if($themeconf_list != null) {
				//参加カテゴリ
				if(isset($themeconf_list['category'][$theme_kind])) {
					$category_name = $themeconf_list['category'][$theme_kind];

					$theme_lang_path = STYLE_DIR."/themes/".$theme_name."/language/".$lang."/"._THEME_INIFILE;
					$theme_lang = "";
					if(file_exists($theme_lang_path)) {
						$theme_lang_list = parse_ini_file($theme_lang_path, true);
						if(isset($theme_lang_list[$theme_kind])) {
							$theme_lang = $theme_lang_list[$theme_kind];
						}
					}

					$theme_templates_path = STYLE_DIR."/themes/".$theme_name."/templates/";
					$child_themes_arr = $fileView->getCurrentDir($theme_templates_path);
					if(!isset($child_themes_arr[0])) {
						if(file_exists($theme_templates_path."block.html")) {
							//templates直下のblock.html
							if(isset($theme_lang['default'])) {
								$theme_list[$category_name][$theme_name] = $theme_lang['default'];
							} else {
								$theme_list[$category_name][$theme_name] = $theme_name;
							}
							//if(file_exists(STYLE_DIR."/themes/".$theme_name."/config/".$theme_kind."_custom.ini")) {
							//	$theme_customlist[$theme_name] = _ON;
							//}
							if(file_exists(STYLE_DIR."/themes/".$theme_name."/images/".ucfirst ($theme_kind)."Thumbnail.gif")) {
								$image_path[$theme_name] = get_image_url()."/themes/".$theme_name."/images/".ucfirst ($theme_kind)."Thumbnail.gif";
							}else if(file_exists(STYLE_DIR."/themes/".$theme_name."/images/Thumbnail.gif")) {
								$image_path[$theme_name] = get_image_url()."/themes/".$theme_name."/images/Thumbnail.gif";
							} else {
								$image_path[$theme_name] = get_image_url()."/themes/images/NoThumbnail.gif";
							}
							if(isset($current_theme_name)) {
								if($current_theme_name == $theme_name) {
									$act_category = $category_name;
								}
							}
						}
					} else {
						if(in_array("default", $child_themes_arr)) {
							//defaultがあれば、先に表示
							$theme_list[$category_name][$theme_name."_default"] = null;
						}
						foreach($child_themes_arr as $sub_name) {
							if(isset($theme_lang[$sub_name])) {
								$theme_list[$category_name][$theme_name."_".$sub_name] = $theme_lang[$sub_name];
							} else {
								$theme_list[$category_name][$theme_name."_".$sub_name] = $theme_name."_".$sub_name;
							}
							//if(file_exists(STYLE_DIR."/themes/".$theme_name."/config/".$sub_name."/".$theme_kind."_custom.ini")) {
							//	$theme_customlist[$theme_name."_".$sub_name] = _ON;
							//}
							if(file_exists(STYLE_DIR."/themes/".$theme_name."/images/".$sub_name."/".ucfirst ($theme_kind)."Thumbnail.gif")) {
								$image_path[$theme_name."_".$sub_name] = get_image_url()."/themes/".$theme_name."/images/".$sub_name."/".ucfirst ($theme_kind)."Thumbnail.gif";
							} else if(file_exists(STYLE_DIR."/themes/".$theme_name."/images/".$sub_name."/Thumbnail.gif")) {
								$image_path[$theme_name."_".$sub_name] = get_image_url()."/themes/".$theme_name."/images/".$sub_name."/Thumbnail.gif";
							} else if(file_exists(STYLE_DIR."/themes/".$theme_name."/images/".ucfirst ($theme_kind)."Thumbnail.gif")) {
								$image_path[$theme_name."_".$sub_name] = get_image_url()."/themes/".$theme_name."/images/".ucfirst ($theme_kind)."Thumbnail.gif";
							} else if(file_exists(STYLE_DIR."/themes/".$theme_name."/images/Thumbnail.gif")) {
								$image_path[$theme_name."_".$sub_name] = get_image_url()."/themes/".$theme_name."/images/Thumbnail.gif";
							} else {
								$image_path[$theme_name."_".$sub_name] = get_image_url()."/themes/images/NoThumbnail.gif";
							}
							if(isset($current_theme_name)) {
								if($current_theme_name == $theme_name."_".$sub_name) {
									$act_category = $category_name;
								}
							}
						}
					}
				}
			}
		}
		return array(
			$category_list,
			$background_list,
			$theme_list,
			$image_path,
			$act_category
		);
	}
}
?>
