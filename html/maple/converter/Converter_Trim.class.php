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
 * @package     Maple.converter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Converter_Trim.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * 前後の空白を除去
 *
 * @package     Maple.converter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Converter_Trim extends Converter
{
    /**
     * 前後の空白を除去
     *
     * @param   string  $attributes 変換する文字列
     * @return  string  変換後の文字列
     * @access  public
     * @since   3.0.0
     */
    function convert($attributes)
    {
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attributes[$key] = trim($value);
            }
        } else {
            $attributes = trim($attributes);
        }
        return $attributes;
    }
}
?>
