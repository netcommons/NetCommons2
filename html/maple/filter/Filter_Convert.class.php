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
 * @version     CVS: $Id: Filter_Convert.class.php,v 1.2 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/core/ConverterManager.class.php';

/**
 * Converterの実行準備および実行を行うFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter_Convert extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_Convert()
    {
        parent::Filter();
    }

    /**
     * Convert処理を実行
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_Convertの前処理が実行されました", "Filter_Convert#execute");

        $attributes = $this->getAttributes();

        if (is_array($attributes) && (count($attributes) > 0)) {
            $converterManager =& new ConverterManager();
            $converterManager->execute($attributes);
        }

        $container =& DIContainerFactory::getContainer();
        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_Convertの後処理が実行されました", "Filter_Convert#execute");
    }
}
?>
