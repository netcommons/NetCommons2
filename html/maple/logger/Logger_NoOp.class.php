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
 * @package     Maple.logger
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Logger_NoOp.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * 何も出力しないLogger
 *
 * @package     Maple.logger
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Logger_NoOp extends Logger
{
    /**
     * コンストラクター
     *
     * @access  private
     * @since   3.0.0
     */
    function Logger_NoOp()
    {
    }

    /**
     * fatalレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function fatal($message, $caller = null)
    {
    }

    /**
     * errorレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function error($message, $caller = null)
    {
    }

    /**
     * warnレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function warn($message, $caller = null)
    {
    }

    /**
     * infoレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function info($message, $caller = null)
    {
    }

    /**
     * debugレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function debug($message, $caller = null)
    {
    }

    /**
     * traceレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function trace($message, $caller = null)
    {
    }
}
?>
