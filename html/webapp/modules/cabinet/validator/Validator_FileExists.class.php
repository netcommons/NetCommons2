<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイル存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_FileExists extends Validator
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
		if (empty($attributes["file_id"])) {
        	return $errStr;
		}

        if (!$cabinetView->fileExists($attributes["file_id"])) {
			return $errStr;
		}
		
		$file = $cabinetView->getFile($attributes["file_id"]);
        if (!$file) {
			return $errStr;
		}
		$request->setParameter("file", $file);
        return;
    }
}
?>
