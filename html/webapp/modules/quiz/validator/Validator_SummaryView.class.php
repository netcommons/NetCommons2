<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 集計データ参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_SummaryView extends Validator
{
    /**
     * 集計データ参照権限チェックバリデータ
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
		$quizView =& $container->getComponent("quizView");

		$summary = $quizView->getAnswerSummary();
		if (empty($summary)) {
			return $errStr;
		}

		$session =& $container->getComponent("Session");
		if ($session->getParameter("_user_id") != $summary["insert_user_id"] &&
				$session->getParameter("_auth_id") < _AUTH_CHIEF) {
			return $errStr;
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("summary", $summary);

        return;
    }
}
?>