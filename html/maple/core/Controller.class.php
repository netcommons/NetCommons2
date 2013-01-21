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
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Controller.class.php,v 1.4 2006/12/26 09:18:59 fukuyama Exp $
 */

require_once MAPLE_DIR .'/core/LogFactory.class.php';
require_once MAPLE_DIR .'/core/DIContainerFactory.class.php';

/**
 * フレームワークの動作を統括するクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Controller
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Controller()
    {
    }

    /**
     * フレームワークを起動させる
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();

        //
        // DIContainerを生成する
        //
        $container =& $this->_createDIContainer();

        if (!is_object($container)) {
            $log->fatal("DIContainerの生成に失敗しました", "Controller#execute");
            return;
        }

        //
        // リクエストパラメータを取得し、実行するActionを決定
        //
        $request =& $container->getComponent("Request");
        $request->dispatchAction();
        $actionName = $request->getParameter(ACTION_KEY);

        //
        // 初期ActionをActionChainにセット
        //
        $actionChain =& $container->getComponent("ActionChain");
        $actionChain->add($actionName);

        //
        // 実行すべきActionがある限り繰り返す
        //
		$firstFlag = true;
        while ($actionChain->hasNext()) {
            //
            // 設定ファイルを読み込む
            //
            $configUtils =& $container->getComponent("ConfigUtils");
            if ($firstFlag) {
	            $configUtils->execute();
	            $firstFlag = false;
            } else {
	            $configUtils->execute(true);
            }

            //
            // 設定ファイルを元にFilterChainを組み立てて、実行
            //
            $filterChain =& $container->getComponent("FilterChain");
            $filterChain->build($configUtils);
            $filterChain->execute();
            $filterChain->clear();

            //
            // 後始末および次のActionへ
            //
            $configUtils->clear();

            $actionChain->next();
        }
    }

    /**
     * DIContainerを生成する
     *
     * @access  public
     * @since   3.0.0
     */
    function &_createDIContainer()
    {
        $log =& LogFactory::getLog();

        if (!@file_exists(WEBAPP_DIR . BASE_INI)) {
            $log->fatal("設定ファイルが存在しません", "Controller#_createDIContainer");
            return;
        }

        $config = parse_ini_file(WEBAPP_DIR . BASE_INI, TRUE);

        if (count($config) < 1) {
            $log->fatal("設定ファイルが不正です", "Controller#_createDIContainer");
            return;
        }

        $container =& DIContainerFactory::getContainer();

        foreach ($config as $key => $value) {
            if (isset($config[$key]["name"])) {
                $className = $config[$key]["name"];
            }
            if (isset($config[$key]["path"])) {
                $filename = $config[$key]["path"];
            }

            if (!$className || !$filename) {
                $log->fatal("設定ファイルが不正です", "Controller#_createDIContainer");
                return;
            }

            include_once($filename);

            $instance =& new $className();

            if (!is_object($instance)) {
                $log->fatal("インスタンスの生成に失敗しました($className)", "Controller#_createDIContainer");
                return;
            }

            $container->register($instance, $key);
        }

        return $container;
    }
}
?>
