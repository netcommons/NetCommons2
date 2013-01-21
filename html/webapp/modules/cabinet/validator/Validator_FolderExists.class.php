<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォルダ存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FolderExists extends Validator
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
		$request =& $container->getComponent("Request");
		if (empty($attributes["folder_id"])) {
        	$request->setParameter("folder_id", "0");
        	$request->setParameter("folder_parent_id", "0");
        	return;
		}

        if (!$cabinetView->fileExists($attributes["folder_id"])) {
			return $errStr;
		}
		
		$folder = $cabinetView->getFile($attributes["folder_id"]);
        if (!$folder) {
			return $errStr;
		}
		$request->setParameter("folder", $folder);
		$request->setParameter("folder_parent_id", $folder["parent_id"]);
        return;
    }
}
?>
