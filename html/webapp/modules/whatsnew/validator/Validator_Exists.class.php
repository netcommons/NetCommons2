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
class Whatsnew_Validator_Exists extends Validator
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
		$attributes["whatsnew_id"] = intval($attributes["whatsnew_id"]);

    	$container =& DIContainerFactory::getContainer();
 		$request =& $container->getComponent("Request");
 		$whatsnewView =& $container->getComponent("whatsnewView");
 		$session =& $container->getComponent("Session");

		$whatsnew = $whatsnewView->getWhatsnew($attributes["whatsnew_id"]);
		if ($whatsnew == false) {
			return $errStr;
		}

		$room_arr_flat = $request->getParameter("room_arr_flat");
		if (isset($room_arr_flat[$whatsnew["room_id"]]) || $session->getParameter("_user_id") != "0" && ($whatsnew["room_id"] === "0" || $whatsnew["room_id"] === 0)) {
			$request->setParameter("whatsnew", $whatsnew);
			return;
		}
		if ($whatsnew["private_flag"] == _ON && $whatsnew["default_entry_flag"] == _ON) {
			$request->setParameter("whatsnew", $whatsnew);
			return;
		}
		return $errStr;
    }
}
?>
