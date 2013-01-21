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
 * @version     CVS: $Id: Logger_SimpleFile.class.php,v 1.2 2006/09/29 06:16:27 Ryuji.M Exp $
 */

/**
 * 使用するログファイル名
 *
 * @type    string
 * @since    3.0.0
 **/
define("LOG_FILENAME", "/maple.log");

/**
 * ファイルに出力するLogger
 *
 * @package     Maple.logger
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Logger_SimpleFile extends Logger
{
    /**
     * コンストラクター
     *
     * @access  private
     * @since   3.0.0
     */
    function Logger_SimpleFile()
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
        $this->output(LEVEL_FATAL, $message, $caller);
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
        $this->output(LEVEL_ERROR, $message, $caller);
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
        $this->output(LEVEL_WARN, $message, $caller);
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
        $this->output(LEVEL_INFO, $message, $caller);
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
        $this->output(LEVEL_DEBUG, $message, $caller);
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
        $this->output(LEVEL_TRACE, $message, $caller);
    }

    /**
     * ログを出力する関数
     *
     * @param   integer $logLevel   ログレベル
     * @param   string  $message    エラーメッセージ
     * @param   mixed   $caller 呼び出し元
     * @access  public
     * @since   3.0.0
     */
    function output($logLevel, $message, $caller)
    {
        if (LOG_LEVEL <= $logLevel) {
            $now = date("Y/m/d H:i:s");

            $levels = array(
                LEVEL_FATAL => 'fatal',
                LEVEL_ERROR => 'error',
                LEVEL_WARN  => 'warn',
                LEVEL_INFO  => 'info',
                LEVEL_DEBUG => 'debug',
                LEVEL_TRACE => 'trace',
            );

            $message = sprintf("[%s] [%s] %s - %s\n", $now, $levels[$logLevel], $message, $caller);

            @error_log($message, 3, LOG_DIR . LOG_FILENAME);
        }
    }
}
?>
