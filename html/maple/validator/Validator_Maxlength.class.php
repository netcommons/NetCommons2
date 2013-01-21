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
 * @version     CVS: $Id: Validator_Maxlength.class.php,v 1.2 2008/06/11 07:58:44 Ryuji.M Exp $
 */

/**
 * 最大文字数を超えていないかのチェック
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Validator_Maxlength extends Validator
{
    /**
     * 最大文字数を超えていないかのチェック
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     0:最大文字数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
        $length = $params[0];
		// 環境によっては strlen は ms_strlen にオーバーロードされるため修正 by Ryuji.M
		if (strlen(bin2hex($attributes)) / 2 <= $length) {
		//if (strlen($attributes) <= $length) {
            return;
        } else {
            return $errStr;
        }
    }
}
?>
