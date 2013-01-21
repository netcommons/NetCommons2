<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 小テスト期限切れチェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_PeriodOver extends Validator
{
    /**
     * 小テスト期限切れチェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["quiz"]["period"])) {
			return;
		}
		
		$gmt = timezone_date();
		
		if ($gmt < $attributes["quiz"]["period"]) {
			return;
		}

		$container =& DIContainerFactory::getContainer();
		$quizAction =& $container->getComponent("quizAction");
		
		$params = array(
			"quiz_id" => $attributes["quiz"]["quiz_id"],
			"status" => QUIZ_STATUS_END_VALUE
		);
		if (!$quizAction->updateQuiz($params)) {
			return $errStr;
		}
		$attributes["quiz"]["status"] = QUIZ_STATUS_END_VALUE;

		$request =& $container->getComponent("Request");
		$request->setParameter("quiz", $attributes["quiz"]);
		
        return;
    }
}
?>