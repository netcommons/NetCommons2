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
 * @version     CVS: $Id: Filter_Action.class.php,v 1.2 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/core/BeanUtils.class.php';

/**
 * Actionの実行準備および実行を行うFilter
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter_Action extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter_Action()
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
        $log =& LogFactory::getLog();
        $log->trace("Filter_Actionの前処理が実行されました", "Filter_Action#execute");

        //
        // カレントのActionを取得
        //
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $action =& $actionChain->getCurAction();

        //
        // 設定ファイルに入っていた値を設定
        //
        $request =& $container->getComponent("Request");
        $params = $request->getParameters();

        if ($this->getSize() > 0) {
            $attributes = $this->getAttributes();
            foreach ($attributes as $key => $value) {
                if(strncmp("ref:", $value, 4) == 0) {
                	$value = substr($value, 4, strlen($value) - 4);
                	//$value = preg_replace("/^ref:/", "", $value);
                    $component =& $container->getComponent($value);
                    if (is_object($component)) {
                        $attributes[$key] =& $component;
                    } else {
                        $log->error("不正なコンポーネントが設定ファイルで指定されています($value)", "Filter_Action#execute");
                    }
                }
                

                //
                // DIされるパラメータはリクエストパラメータで
                // 上書きされないようにする
                //
                if (isset($params[$key])) {
                    unset($params[$key]);
                }
            }
            BeanUtils::setAttributes($action, $attributes);
        }

        //
        // Requestの値をActionに移す
        //
        if (count($params) > 0) {
            BeanUtils::setAttributes($action, $params, true);
        }

        //
        // Filter_Actionにたどりつく前にエラーが発生していたら
        // Actionは実行しない（そのかわりエラータイプをViewの種類とする）
        //
        $errorList =& $actionChain->getCurErrorList();
        $type = $errorList->getType();

        if ($type == "") {
            $view = $actionChain->execute();
        } else {
            $view = $type;
        }

        if ($view != "") {
            $response =& $container->getComponent("Response");
            $response->setView($view);
        }

        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_Actionの後処理が実行されました", "Filter_Action#execute");
    }
}
?>
