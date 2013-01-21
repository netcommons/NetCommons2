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
 * @version     CVS: $Id: DIContainerFactory.class.php,v 1.2 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/core/DIContainer.class.php';

/**
 * DIContainerを生成するFactoryクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class DIContainerFactory
{
    /**
     * @var Containerを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_container;

    /**
     * コンストラクター
     *
     * DIContainerFactoryクラスはSingletonとして使うので直接newしてはいけない
     *
     * @access  private
     * @since   3.0.0
     */
    function DIContainerFactory()
    {
        $this->_container = NULL;
    }

    /**
     * DIContainerFactoryクラスの唯一のインスタンスを返却
     *
     * @return  Object  DIContainerFactoryクラスのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getInstance()
    {
        static $instance;
        if ($instance === NULL) {
            $instance = new DIContainerFactory();
        }

        if (!is_object($instance->_container)) {
            //
            // DIContainerの生成
            //
            $container =& new DIContainer();
            $instance->_container =& $container;
        }

        return $instance;
    }

    /**
     * 設定ファイルを元にDIContainerのインスタンスを返却
     *
     * @param   string  $filename   設定ファイル名
     * @return  Object  Containerのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &create($filename)
    {
        $instance =& DIContainerFactory::getInstance();

        $container =& $instance->_container;

        if (!$container->create($filename)) {
            $container = null;
        }

        return $container;
    }

    /**
     * 保持しているContainerを返却
     *
     * @return  Object  Containerのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getContainer()
    {
        $instance =& DIContainerFactory::getInstance();

        return $instance->_container;
    }
}
?>
