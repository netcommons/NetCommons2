<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルの重複チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FileDuplication extends Validator
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

		$cabinet_id = $attributes["cabinet_id"];
		$folder_id = $attributes["folder_id"];
		$file_name = $attributes["file_name"];
		if (isset($attributes["file"])) {
			$file = $attributes["file"];
		}

		if (isset($file) && empty($folder_id)) {
			$request =& $container->getComponent("Request");
			$request->setParameter("folder_id", $file["parent_id"]);
		}

		if (isset($attributes["extension"])) {
			$extension = $attributes["extension"];
		} elseif (!empty($file)) {
	        $extension = !empty($file["extension"]) ? $file["extension"] : "";
		} else {
			$extension = "";
		}

		if (isset($params[0])) {
			if (defined($params[0])) {
				$rename_flag = intval(constant($params[0]));
			} else {
				$rename_flag = intval($params[0]);
			}
		} else {
			$rename_flag = _OFF;
		}

		if ($rename_flag == _ON) {
			$file_name = $cabinetView->renameFile($file_name, $extension);
		} else {
	    	$nameList = $cabinetView->getFileNameList();
	        if ($nameList === false) {
	        	return _INVALID_INPUT;
	        }
	        if ($extension != "") {
	        	$extension = "." . $extension;
	        }
			if (in_array($file_name.$extension, $nameList)) {
				return $errStr;
			}
		}
        $request =& $container->getComponent("Request");
		$request->setParameter("file_name", $file_name);

		return;
    }
}
?>