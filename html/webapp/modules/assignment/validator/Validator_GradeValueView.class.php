<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 評価データ取得バリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Validator_GradeValueView extends Validator
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
    	if (empty($attributes["room_id"])) {
    		return _INVALID_INPUT;
    	}

		$container =& DIContainerFactory::getContainer();

		$assignmentView =& $container->getComponent("assignmentView");
		$gradeValues = $assignmentView->getGradeValues();
		if ($gradeValues === false) {
        	return $errStr;
        }

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		
		if (empty($gradeValues) && $actionName == "assignment_view_edit_setGrade") {
			$assignmentCount = $assignmentView->getAssignmentCount();
	        if ($assignmentCount == 0) {
	        	$gradeValues = $assignmentView->getDefaultGradeValues();
	        }
		}

		$request =& $container->getComponent("Request");
		$request->setParameter("gradeValues", $gradeValues);

        return;
    }
}
?>
