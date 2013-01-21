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
 * @version     CVS: $Id: Logger.interface.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * ログ処理のインタフェースを規定するクラス
 *
 * @package     Maple.logger
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Logger
{
    /**
     * fatalレベル以上のログを出力
     *
     * @param   string  $message    エラーメッセージ
     * @access  public
     * @since   3.0.0
     */
    function fatal($message, $caller = null)
    {
        $error = 'Loggerでfatal関数が作成されていません。';
        trigger_error($error, E_USER_ERROR);
        exit;
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
        $error = 'Loggerでerror関数が作成されていません。';
        trigger_error($error, E_USER_ERROR);
        exit;
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
        $error = 'Loggerでwarn関数が作成されていません。';
        trigger_error($error, E_USER_ERROR);
        exit;
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
        $error = 'Loggerでinfo関数が作成されていません。';
        trigger_error($error, E_USER_ERROR);
        exit;
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
        $error = 'Loggerでdebug関数が作成されていません。';
        trigger_error($error, E_USER_ERROR);
        exit;
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
        $error = 'Loggerでtrace関数が作成されていません。';
        trigger_error($error, E_USER_ERROR);
        exit;
    }
}
?>
