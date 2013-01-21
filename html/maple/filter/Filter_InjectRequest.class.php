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
 * @version     CVS: $Id: Filter_InjectRequest.class.php,v 1.1 2006/04/11 04:30:08 Ryuji.M Exp $
 */

/**
 * RequestパラメーターをDIContainer内のコンポーネントにInjectionするFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter_InjectRequest extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_InjectRequest()
    {
        parent::Filter();
    }

    /**
     * Injectionを行う
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_InjectRequestの前処理が実行されました", "Filter_InjectRequest#execute");

        //
        // 設定ファイルに入っていた値を元に設定
        //
        $container =& DIContainerFactory::getContainer();

        if ($this->getSize() > 0) {
            $request =& $container->getComponent("Request");
            $params = $request->getParameters();
            if (count($params) > 0) {
                foreach ($this->getAttributes() as $key => $value) {
                    $component =& $container->getComponent($key);
                    if (is_object($component)) {
                        BeanUtils::setAttributes($component, $params, true);
                    } else {
                        $log->error("不正なコンポーネントが設定ファイルで指定されています($key)", "Filter_InjectRequest#execute");
                    }
                }
            }
        }

        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_InjectRequestの後処理が実行されました", "Filter_InjectRequest#execute");
    }
}
?>
