<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 評価バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_GradeReport extends Validator
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
    	if (empty($attributes["assignment"]) ) {
			return $errStr;
		}

		if (empty($attributes["submit_id"])) {
			return $errStr;
		}

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$assignmentView =& $container->getComponent("assignmentView");

		$report_id = $assignmentView->getNewestReportID();
    	$request->setParameter("newest_report_id", $report_id);

		$submitter = $assignmentView->getSubmitter();
		if (empty($submitter)) {
        	return $errStr;
        }

        if (!$submitter["hasGradeAuthority"]) {
			return $errStr;
        }

        $request->setParameter("submitter", $submitter);

        return;
    }
}
?>
