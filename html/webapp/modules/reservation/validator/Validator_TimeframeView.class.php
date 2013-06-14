<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 時間枠の存在チェック
*
* @package     NetCommons.validator
* @author      Noriko Arai,Ryuji Masukawa
* @copyright   2006-2007 NetCommons Project
* @license     http://www.netcommons.org/license.txt  NetCommons License
* @project     NetCommons Project, supported by National Institute of Informatics
* @access      public
*/
class Reservation_Validator_TimeframeView extends Validator
{
	/**
	 * validate実行
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
		$reservationView =& $container->getComponent("reservationView");

		$request =& $container->getComponent("Request");

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if(!isset($attributes['timeframe_id']) || empty($attributes['timeframe_id'])) {
			if($actionName == 'reservation_action_edit_timeframe_delete') {
				return $errStr;
			}
			else {
				// 新規追加
				return;
			}
		}
		$timeframe = $reservationView->getTimeframe($attributes['timeframe_id']);
		if(!$timeframe) {
			return $errStr;
		}

		$request->setParameter('timeframe', $timeframe);

		return;
	}
}
?>
