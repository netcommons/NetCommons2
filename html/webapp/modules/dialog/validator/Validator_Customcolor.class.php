<?php
/**
 * ブロックテーマのカスタム設定が正しいかどうかのチェック
 *
 * @author      Ryuji Masukawa
 * @copyright   2006
 * @license      
 * @access      public
 */
class Dialog_Validator_Customcolor extends Validator
{
    /**
     * ブロックテーマのカスタム設定が正しいかどうかのチェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	if(is_array($attributes)) {
	    	$theme_name = $attributes[0];
	    	$color_list = $attributes[1];
    	} else {
    		$theme_name = $attributes;
    		$color_list = null;
    	}
    	
    	$blocktheme_list = null;
    	$themeStrList = explode("_", $theme_name);
		if(count($themeStrList) == 1) {
			$themeCssPath = "/themes/".$theme_name."/config";
			if(file_exists(STYLE_DIR. $themeCssPath."/block_custom.ini")) {
				$blocktheme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/block_custom.ini",true);
			}
		} else {
			$bufthemeStr = array_shift ( $themeStrList );
			$themeCssPath = "/themes/".$bufthemeStr."/config/";
			if(file_exists(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/block_custom.ini")) {
				$blocktheme_list = parse_ini_file(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/block_custom.ini",true);
			} else if(file_exists(STYLE_DIR. $themeCssPath."/block_custom.ini")) {
				$blocktheme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/block_custom.ini",true);
			} 
		}
		
    	//$themeini_path = MODULE_DIR."/blocks/config/".$theme_name."/block_custom.ini";
		if($blocktheme_list == null || !is_array($blocktheme_list)) {
			//ファイルが存在しない
			return $errStr;
		}
		if($color_list == null) {
			return;	
		}
		
		//$blocktheme_list = parse_ini_file($themeini_path, true);
		foreach($blocktheme_list as $key=>$value) {
			//if(preg_match("/^same:/",$key)) {
			//	//unset($blocktheme_list[$key]);
			//	continue;
			//}
			foreach($blocktheme_list[$key] as $sub_key=>$sub_value) {
				$property_name = explode(",",$sub_value);
				foreach($property_name as $property_key=>$property_value) {
					if(preg_match("/^selection_auto:/",$property_value)) {
						$property_name[$property_key] = "selection_auto";
					}
				}
				$blocktheme_list[$key][$sub_key] = $property_name;
			}
		}
		foreach($color_list as $class_name=>$value) {
			foreach($color_list[$class_name] as $property_name=>$color) {
				if(isset($blocktheme_list[$class_name][$property_name])) {
					if(!in_array($color,$blocktheme_list[$class_name][$property_name]) && 
						!in_array("selection_auto",$blocktheme_list[$class_name][$property_name]) &&
						!in_array("selection",$blocktheme_list[$class_name][$property_name])) {
						return $errStr;
					}
				} else {
					return $errStr;
				}
			}
		}
    }
}
?>
