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
 * @version     CVS: $Id: Validator_Match.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * 指定された正規表現にマッチするかをチェック
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Validator_Match extends Validator
{
    /**
     * 指定された正規表現にマッチするかをチェック
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     正規表現を表す文字列
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
        //
        // 正規表現内に「,」が含まれていて分断されている可能性も
        // あるのでつないでおく
        //
        $reg = join(",", $params);

        if (preg_match("/${reg}/", $attributes)) {
            return;
        } else {
            return $errStr;
        }
    }
}
?>
