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
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_CheckCLI.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * CLI環境で動作しているかをチェックするFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Filter_CheckCLI extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.1.0
     */
    function Filter_CheckCLI()
    {
        parent::Filter();
    }

    /**
     * Actionを実行
     *
     * @access  public
     * @since   3.1.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_CheckCLIの前処理が実行されました", "Filter_CheckCLI#execute");

        $sapiName = php_sapi_name();
        if ($sapiName != 'cli') {
            $message = 'CLIではない環境で実行されました';
            $log->error($message, "Filter_CheckCLI#execute");

            if (isset($_SERVER['REQUEST_METHOD']) &&
                $_SERVER['REQUEST_METHOD']) {
                header("HTTP/1.0 403 Forbidden");
            } else {
                if (OUTPUT_CODE != SCRIPT_CODE) {
                    $message = mb_convert_encoding($message, OUTPUT_CODE, SCRIPT_CODE);
                }
                print $message;
            }

            exit;
        }

        $container =& DIContainerFactory::getContainer();
        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_CheckCLIの後処理が実行されました", "Filter_CheckCLI#execute");
    }
}
?>
