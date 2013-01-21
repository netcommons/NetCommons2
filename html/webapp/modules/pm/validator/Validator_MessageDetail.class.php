<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メッセージデータバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Validator_MessageDetail extends Validator
{
    /**
     * メッセージデータバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {	
		$receiver_id = null;
		$message_id = null;
		
		if(isset($attributes["receiver_id"])){
			$receiver_id = $attributes["receiver_id"];
		}
		
		if(isset($attributes["message_id"])){
			$message_id = $attributes["message_id"];
		}
		
		if(empty($receiver_id) && empty($message_id)){
			return $errStr;
		}

        return;
    }
}
?>
