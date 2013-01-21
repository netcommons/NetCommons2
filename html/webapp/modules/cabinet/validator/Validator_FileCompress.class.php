<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルの圧縮チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FileCompress extends Validator
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
		$cabinet = $attributes["cabinet"];

		if ($cabinet["compress_download"] == _OFF && (empty($cabinet) || $cabinet["hasAddAuthority"] == _OFF)) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
        $cabinetView =& $container->getComponent("cabinetView");
		
		$file = $attributes["file"];
		if ($file["file_type"] == CABINET_FILETYPE_FOLDER) {
			$size = $cabinetView->getSize($file["file_id"]);
		} else {
			$size = $file["size"];
		}
		if ($size == 0) {
			return CABINET_ERROR_COMPRESS;
		}
    }
}
?>