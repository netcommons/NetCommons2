<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Validator_Time.class.php,v 1.1 2006/11/17 04:31:27 snakajima Exp $
 */

/**
 * 日付が妥当かどうかをチェック
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Validator_Time extends Validator
{
    /**
     * 時間が妥当かどうかをチェック
     *
     * @param   mixed   $attributes 時、分の配列 or 時分(hhnn) or 
     *                               時、分、秒の配列 or 時分秒(hhnnss) or 
     *                               年、月、日、時、分の配列 or 年月日時分(yyyymmddhhnn)
     *                               年、月、日、時、分、秒の配列 or 年月日時分秒(yyyymmddhhnnss)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     0:年の最小値 1:年の最大値(オプション)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
    	$checkarr = array();
    	if (is_array($attributes)) {
			$checkarr = $attributes;
    	} else {
    		if (strlen($attributes) == 4) {
	    		$checkarr[0] = substr($attributes,0,2);
	    		$checkarr[1] = substr($attributes,2,2);
    		} elseif (strlen($attributes) == 6) {
	    		$checkarr[0] = substr($attributes,0,2);
	    		$checkarr[1] = substr($attributes,2,2);
	    		$checkarr[2] = substr($attributes,4,2);
    		} elseif (strlen($attributes) == 12) {
	    		$checkarr[0] = substr($attributes,8,2);
	    		$checkarr[1] = substr($attributes,10,2);
    		} elseif (strlen($attributes) == 14) {
	    		$checkarr[0] = substr($attributes,8,2);
	    		$checkarr[1] = substr($attributes,10,2);
	    		$checkarr[2] = substr($attributes,12,2);
    		} else {
    			return $errStr;
    		}
    	}

        assert(is_array($checkarr));

        $hour = isset($checkarr[0]) ? $checkarr[0] : null;
        $minute = isset($checkarr[1]) ? $checkarr[1] : null;
        $second = isset($checkarr[2]) ? $checkarr[2] : null;

        if (($hour == "") || ($minute == "") || (isset($second) && $second == "")) {
            return $errStr;
        } elseif (!is_numeric($hour) || !is_numeric($minute) || (isset($second) && !is_numeric($second))) {
            return $errStr;
        } elseif (!($hour>=0 && $hour<24) || !($minute>=0 && $minute<59) || (isset($second) && !($second>=0 && $second<59))) {
            return $errStr;
        } else {
            return;
        }
    }
}
?>
