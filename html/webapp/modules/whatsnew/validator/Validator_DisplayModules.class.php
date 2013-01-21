<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * デフォルトモジュールチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Validator_DisplayModules extends Validator
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
    	if (empty($attributes["display_modules"])) {
    		return $errStr;
    	}
		$req_display_modules = $attributes["display_modules"];
		$ret_display_modules = array();
		foreach ($req_display_modules as $i=>$value) {
			if (intval($value) == 0)  { continue; }
			$ret_display_modules[] = intval($value);
		}
		if (empty($ret_display_modules)) {
			return $errStr;
		}
    	$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
  		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("display_modules"=>$ret_display_modules));
    }
}
?>
