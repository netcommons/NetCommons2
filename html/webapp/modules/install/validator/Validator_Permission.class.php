<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * install.inc.phpが書き込み不可ならばエラー
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Validator_Permission extends Validator
{
    /**
     * install.inc.phpが書き込み不可ならばエラー
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$install_ini_path = INSTALL_INC_DIR . "/". "install.inc.php";
    	if(@!file_exists($install_ini_path) || !is_writeable($install_ini_path)) {
    		echo $errStr;
    		exit;
    		//return $errStr;
    	}
    }
}
?>