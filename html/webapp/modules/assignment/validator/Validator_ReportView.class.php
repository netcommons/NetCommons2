<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * レポートデータ取得バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_ReportView extends Validator
{
    /**
     * validate処理
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (empty($attributes["assignment"])) {
			return $errStr;
		}
		if (empty($attributes["report_id"])) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
		
		$assignmentView =& $container->getComponent("assignmentView");
		$report = $assignmentView->getReport($attributes["report_id"]);
		if (empty($report)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("report", $report);

		$hasAnswerAuthority = $assignmentView->hasAnswerAuthority(true);
		$request->setParameter("hasAnswerAuthority", $hasAnswerAuthority);

        return;
    }
}
?>
