<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *　日付が妥当かどうかをチェック
 * @param   mixed   $attributes チェックする値
 * @param   string  $errStr     エラー文字列
　* @param   array   $params
 * @return  string  エラー文字列(エラーの場合)
 * @access  public
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Validator_Date extends Validator
{
    /**
     * 日付が妥当かどうかをチェック
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     */
    function validate($attributes, $errStr, $params)
    {
    	if (strlen($attributes) == 0) {
    		return;
    	}
    	
    	switch (_INPUT_DATE_FORMAT) {
    		case "Y/m/d":
		    	$pattern = "/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/"; 
    			break;
    		case "m/d/Y":
    		case "d/m/Y":
    			$pattern = "/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/";
    			break;
    		default:
    			return $errStr;
    	}

		if (!preg_match($pattern, $attributes, $matches)) {
			return $errStr;
		}
		
		switch (_INPUT_DATE_FORMAT) {
    		case "Y/m/d":
		    	$check = checkdate($matches[2], $matches[3], $matches[1]);
		    	$dateString = $matches[1]. $matches[2]. $matches[3];
    			break;
    		case "m/d/Y":
		    	$check = checkdate($matches[1], $matches[2], $matches[3]);
		    	$dateString = $matches[3]. $matches[1]. $matches[2];
    			break;
    		case "d/m/Y":
    			$check = checkdate($matches[2], $matches[1], $matches[3]);
    			$dateString = $matches[3]. $matches[2]. $matches[1];
    			break;
    	}
    	if (!$check) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$key = $this->getKeys(0);
		$request->setParameter($key, $dateString);
		
		return;
    }
}
?>
