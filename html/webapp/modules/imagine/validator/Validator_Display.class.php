<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Imagine_Validator_Display extends Validator
{
    /**
     * 表示方法チェックバリデータ
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
		$db =& $container->getComponent("DbObject");

		$sql = "SELECT display ".
				"FROM {imagine_block} ".
				"WHERE block_id = ?";
		$blocks = $db->execute($sql, $attributes["block_id"], 1, null, false);
		if ($blocks === false) {
			$db->addError();
			return $errStr;
		}
		
		$request =& $container->getComponent("Request");
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "imagine_action_edit_style") {
			$exists = !empty($blocks);
			$request->setParameter("exists", $exists);
			return;
		}

		if (empty($blocks)) {
			$request->setParameter("display", IMAGINE_DISPLAY_COMPACT);
		} else {
			$request->setParameter("display", $blocks[0][0]);
		}
 
        return;
    }
}
?>
