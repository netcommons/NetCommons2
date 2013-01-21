<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイル名チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FileName extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$file_name = $attributes["file_name"];

    	if (isset($attributes["file_type"])) {
    	   	$file_type = $attributes["file_type"];
    	} elseif (isset($params[0])) {
			if (defined($params[0])) {
				$file_type = intval(constant($params[0]));
			} else {
				$file_type = intval($params[0]);
			}
    	} else {
    		$file_type = CABINET_FILETYPE_FOLDER;
    	}

		if ($file_type == CABINET_FILETYPE_FOLDER && preg_match("/[\/\?\|:<>*\'\\\"\.\\\]/", $file_name)) {
			return $errStr;
		} elseif (preg_match("/[\/\?\|:<>*\'\\\"\\\]/", $file_name)) {
			return $errStr;
		} else {
	        return;
		}
    }
}
?>