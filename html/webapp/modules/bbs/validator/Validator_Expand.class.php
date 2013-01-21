<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 展開方法の値チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Validator_Expand extends Validator
{
    /**
     * 展開方法の値チェックバリデータ
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
		$session =& $container->getComponent("Session");

		if (!isset($attributes["expand"])) {
			$attributes["expand"] = $session->getParameter("bbs_expand". $attributes["block_id"]);
		}
		
		if (!isset($attributes["expand"])) {
			$attributes["expand"] = $attributes["bbs"]["expand"];
		}

		$attributes["expand"] = intval($attributes["expand"]);
		if ($attributes["expand"] != BBS_EXPAND_THREAD_VALUE &&
				$attributes["expand"] != BBS_EXPAND_FLAT_VALUE) {
			return $errStr;
		}

		$session->setParameter("bbs_expand". $attributes["block_id"], $attributes["expand"]);

		$request =& $container->getComponent("Request");
		$request->setParameter("expand", $attributes["expand"]);
 
        return;
    }
}
?>
