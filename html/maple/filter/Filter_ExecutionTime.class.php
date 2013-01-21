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
 * @version     CVS: $Id: Filter_ExecutionTime.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * 実行時間を計測するFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter_ExecutionTime extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_ExecutionTime()
    {
        parent::Filter();
    }

    /**
     * Actionを実行
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $start = explode(' ', microtime());
        $start = $start[1] + $start[0];

        //------------------------------------------------------------
        // ↑↑↑ ここまでがFilterの前処理
        //------------------------------------------------------------

        $container =& DIContainerFactory::getContainer();
        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        //------------------------------------------------------------
        // ↓↓↓ ここからがFilterの後処理
        //------------------------------------------------------------

        $end = explode(' ', microtime());
        $end = $end[1] + $end[0];
        $time = round(($end - $start), 4);

        //
        // maple.iniでセットした引数を取得できる
        //
        $name = $this->getAttribute("name");

        $log =& LogFactory::getLog();
        $log->debug("[$name] ${time} sec" , "Filter_ExecutionTime#execute");
    }
}
?>
