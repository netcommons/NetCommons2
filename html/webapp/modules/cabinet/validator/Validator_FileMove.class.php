<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルの移動チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FileMove extends Validator
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
    	$container =& DIContainerFactory::getContainer();
		$cabinetView =& $container->getComponent("cabinetView");

		$file = $attributes["file"];
		$folder_id = $attributes["folder_id"];

    	$err_address = $cabinetView->getMoveErrFolder($file["file_id"]);
		if ($err_address === false) {
			return _INVALID_INPUT;
		}
		$err_address[] = $file["parent_id"];
		$err_address[] = $file["file_id"];

       if ($file["file_type"] == CABINET_FILETYPE_FOLDER) {
			$file_type_str = CABINET_LABEL_DIR;
			$file_name = $file["org_file_name"];
        } else {
			$file_type_str = CABINET_LABEL_FILE;
			$file_name = $file["org_file_name"].".".$file["extension"];
        }

		if (in_array($folder_id, $err_address)) {
			 return sprintf($errStr, $file_name, $file_type_str);
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("file_name", $file["org_file_name"]);
		$request->setParameter("extension", $file["extension"]);
    }
}
?>