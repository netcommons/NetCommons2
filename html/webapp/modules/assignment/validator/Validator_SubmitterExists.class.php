<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答者データ存在バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_SubmitterExists extends Validator
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

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		if (empty($attributes["submit_user_id"])) {
        	$request->setParameter("submitterExists", false);
			return;
		}

        $assignmentView =& $container->getComponent("assignmentView");
        $submitterExists = $assignmentView->submitterExists();

        $request->setParameter("submitterExists", $submitterExists);

		if ($submitterExists) {
			$report_id = $assignmentView->getNewestReportID();
        	$request->setParameter("newest_report_id", $report_id);
		}

        return;
    }
}
?>
