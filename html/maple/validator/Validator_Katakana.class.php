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
 * @version     CVS: $Id: Validator_Katakana.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * カタカナのみかどうかをチェック
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Validator_Katakana extends Validator
{
    /**
     * カタカナのみかどうかをチェック
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.1.0
     */
    function validate($attributes, $errStr, $params)
    {
        if (INTERNAL_CODE != 'EUC-JP') {
            $attributes = mb_convert_encoding($attributes, 'EUC-JP', INTERNAL_CODE);
        }

        if (preg_match("/^(\xA5[\xA1-\xF6])+$/", $attributes)) {
            return;
        } else {
            return $errStr;
        }
    }
}
?>
