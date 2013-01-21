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
class Assignment_Validator_ReportsView extends Validator
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
		if (empty($attributes["submitterExists"])) {
			return;
		}

		$container =& DIContainerFactory::getContainer();

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if (empty($attributes["submit_user_id"]) && $actionName == "assignment_view_main_init") {
			return;
		}

		$assignmentView =& $container->getComponent("assignmentView");

		if (!empty($attributes["submit_user_id"])) {
			$reports = $assignmentView->getReports($attributes["submit_user_id"]);
		} else {
			return $errStr;
		}
		if (empty($reports)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("reports", $reports);

		if (empty($attributes["report_id"])) {
			foreach ($reports as $key=>$report) {
				$request->setParameter("report_id", $reports[$key]["report_id"]);
				$request->setParameter("report", $reports[$key]);
				break;
			}
		} elseif (isset($reports[$attributes["report_id"]])) {
			$request->setParameter("report", $reports[$attributes["report_id"]]);
		} else {
			return $errStr;
		}

		$hasAnswerAuthority = $assignmentView->hasAnswerAuthority(true);
		$request->setParameter("hasAnswerAuthority", $hasAnswerAuthority);

        return;
    }
}
?>
