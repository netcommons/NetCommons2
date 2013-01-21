<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Validator_MobileBlock.class.php,v 1.1 2008/06/04 11:55:46 snakajima Exp $
 */

/**
 * ブロックに配置しているかどうかをチェック
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Bbs_Validator_MobileBlock extends Validator
{
    /**
     * ブロックに配置しているかどうかをチェック
     *
     * @param   mixed   $attributes block_id_arr(ブロックIDの配列)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     なし
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
		$container =& DIContainerFactory::getContainer();
		$modulesView =& $container->getComponent("modulesView");
		$module_name = $modulesView->loadModuleName("bbs");

		if (empty($attributes)) {
			return sprintf($errStr, $module_name);
		}
		$bbsView =& $container->getComponent("bbsView");
		$bbs_list = $bbsView->getBlocksForMobile($attributes);
		if (empty($bbs_list)) {
			return sprintf($errStr, $module_name);
		}

		$actionChain =& $container->getComponent("ActionChain");
  		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("bbs_list"=>$bbs_list));
    }
}
?>
