<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * レポートデータ存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_ReportExists extends Validator
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
		$container =& DIContainerFactory::getContainer();

        $assignmentView =& $container->getComponent("assignmentView");

		if (empty($attributes["report_id"])) {
			return;
		}

        if (!$assignmentView->reportExists()) {
			return $errStr;
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("submitterExists", true);

		$report_id = $assignmentView->getNewestReportID();
		if ($report_id === false) {
			return $errStr;
		}
       	$request->setParameter("newest_report_id", $report_id);

        return;
    }
}
?>