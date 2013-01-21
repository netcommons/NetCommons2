<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 集計データ存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Quiz_Validator_SummaryExists extends Validator
{
    /**
     * 集計データ存在チェックバリデータ
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
		$currentSequence = $session->getParameter("quiz_current_sequence". $attributes["block_id"]);

		if ($attributes["quiz"]["quiz_type"] == QUIZ_TYPE_LIST_VALUE ||
				$currentSequence == 1) {
	        $quizAction =& $container->getComponent("quizAction");
			$summaryID = $quizAction->insertSummary();
		} else {
			$summaryID = $session->getParameter("quiz_current_summary_id". $attributes["block_id"]);
		}
		if (empty($summaryID)) {
			return $errStr;
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("summary_id", $summaryID);
		$session->setParameter("quiz_current_summary_id". $attributes["block_id"], $summaryID);

        return;
    }
}
?>