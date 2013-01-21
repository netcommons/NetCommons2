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
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Logger_Stderr.class.php,v 1.4 2006/10/19 02:56:55 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/logger/Logger_SimpleFile.class.php');

/**
 * STDERRに出力するLogger
 * run in CLI or CGI mode
 *
 * @package     Maple.logger
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Logger_Stderr extends Logger_SimpleFile
{
    /**
     * ログを出力する関数
     *
     * @param   integer $logLevel   ログレベル
     * @param   string  $message    エラーメッセージ
     * @param   mixed   $caller 呼び出し元
     * @access  public
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
			if(defined("STDERR"))
            	fputs(STDERR, $message);
            else {
            	$stderr = fopen('php://stderr', 'w');
            	fputs($stderr, $message);
            	fclose($stderr);	
            }
        }
    }
}
?>
