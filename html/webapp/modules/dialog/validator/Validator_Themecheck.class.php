<?php


/**
 * ブロックテーマがあるかどうかチェック
 *
 * @author      Ryuji Masukawa
 * @copyright   2006
 * @license      
 * @access      public
 */
class Dialog_Validator_Themecheck extends Validator
{
    /**
     * ブロックテーマがあるかどうかチェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
    	$main_theme_name = $attributes;
    	//任意（ページテーマにより自動的に選択の場合)
    	if($main_theme_name != "_auto") {
	    	$themeStrList = explode("_", $main_theme_name);
			if(count($themeStrList) == 1) {
				$themeDir = "/themes/".$main_theme_name."/templates/block.html";
			} else {
				$bufthemeStr = array_shift ( $themeStrList );
				$themeDir = "/themes/".$bufthemeStr."/templates/".implode("/", $themeStrList) . "/block.html";
			}
			if(!file_exists(STYLE_DIR .$themeDir)) {
				return $errStr;
			}
			
			if($main_theme_name == "system") {
				//systemテーマはエラーとする
				return $errStr;
			}
    	}
    }
}
?>
