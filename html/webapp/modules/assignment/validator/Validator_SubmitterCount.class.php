<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 解答者総件数取得バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_SubmitterCount extends Validator
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
        $session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		
		if (!empty($attributes["submit_user_id"]) || empty($attributes["submit_user_id"]) && $authID == _AUTH_GUEST) {
			return;
		}

		if (empty($attributes["hasSubmitListView"])) {
			return $attributes;
		}
		
		if (!empty($attributes["scroll"])) {
			return;
		}

        $assignmentView =& $container->getComponent("assignmentView");
        if (!empty($attributes["yet_submitter"]) && $attributes["yet_submitter"] == _ON) {
			$submitterCount = $assignmentView->getSubmitterCount(true);
        } else {
        	$submitterCount = $assignmentView->getSubmitterCount();
        }
		if ($submitterCount === false) {
			return $errStr;
		}

		$request =& $container->getComponent("Request");
        $request->setParameter("submitterCount", $submitterCount);

        return;
    }
}
?>
