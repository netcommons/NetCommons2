<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フィルタ重複データバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_FilterDuplicated extends Validator
{
    /**
     * フィルタ重複データバリデータ
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
 		$request =& $container->getComponent("Request");
 		$pmView =& $container->getComponent("pmView");
		
		$senders = $attributes[0];
		$subject = $attributes[1];
		$keyword_list = $attributes[2];
		$filter_actions = $attributes["3"];
		$filter_actions_params = $attributes["4"];
		$filter_id = $attributes["5"];
		
		$filter = array (
			"senders" => $senders,
			"subject" => $subject,
			"keyword_list" => $keyword_list,
			"filter_actions" => $filter_actions,
			"filter_actions_params" => $filter_actions_params,
			"filter_id" => $filter_id				
		);
				
		if (!$pmView->checkFilterDuplicated($filter)) {
			return $errStr;
		}

        return;
    }
}
?>