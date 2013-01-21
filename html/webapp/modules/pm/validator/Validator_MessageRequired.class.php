<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 宛先、件名、内容チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_MessageRequired extends Validator
{
    /**
     *  宛先、件名、内容チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$receivers = $attributes[0];
		$subject = $attributes[1];
		$body = $attributes[2];
		$sendFlag = $attributes[3];
		$send_all_flag = $attributes[4];

		$body = str_replace("<br />","",$body);
		$body = str_replace("\n","",$body);

		$flag = false;
		if($send_all_flag == _ON){
			$flag = true;
		}else{			
			foreach($receivers as $receiver) {
				$receiver = trim($receiver);
				if (!empty($receiver)) {
					$flag = true;
					break;
				}
			}
		}
		
		// 下書き保存チェック
		if ($sendFlag == PM_STORE_MESSAGE) {
			if (!$flag && empty($subject) && empty($body)) {
					return PM_MESSAGE_ONE_REQUIRED.$errStr;
			}
		}
		
		// 送信チェック
		if ($sendFlag == PM_SEND_MESSAGE) {
			if (!$flag) {
				return PM_MESSAGE_ADDRESS.$errStr;
			}
			
			/*
			if (empty($subject)) {
				return PM_MESSAGE_SUBJECT.$errStr;
			}
			*/
			
			if (empty($body)) {
				return PM_MESSAGE_BODY.$errStr;
			}
		}
			
        return;
	}
}
?>