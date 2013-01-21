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
 * @version     CVS: $Id: LogFactory.class.php,v 1.3 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once LOGGER_DIR . '/Logger.interface.php';

/**
 * Loggerを取得するためのクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class LogFactory
{
    /**
     * @var Loggerを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_list;

    /**
     * コンストラクター
     *
     * LogFactoryクラスはSingletonとして使うので直接newしてはいけない
     *
     * @access  private
     * @since   3.0.0
     */
    function LogFactory()
    {
        $this->_list = array();
    }

    /**
     * Requestクラスの唯一のインスタンスを返却
     *
     * @return  Object  Requestクラスのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getInstance()
    {
        static $instance;
        if ($instance === NULL) {
            $instance = new LogFactory();
        }
        return $instance;
    }

    /**
     * Loggerを返却
     *
     * @param   string  $name   Loggerのクラス名
     * @return  Object  Loggerのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getLog($name = DEFAULT_LOGGER)
    {
        //
        // Loggerのクラス名が不正だったらデフォルトのLoggerを切り替え
        //
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
            $name = DEFAULT_LOGGER;
        }

        //
        // 既にセットされているLoggerだったらそれをそのまま返却
        //
        $logFactory =& LogFactory::getInstance();

        if (isset($logFactory->_list[$name]) &&
            is_object($logFactory->_list[$name])) {
            return $logFactory->_list[$name];
        }

        //
        // ファイルが存在していなければエラーを表示
        //
        $className = "Logger_" . ucfirst($name);
        $filename = LOGGER_DIR . "/${className}.class.php";

        if (!(@include_once $filename) or !class_exists($className)) {
            $error = "Loggerのファイル名が不正です($filename)";
            trigger_error($error, E_USER_ERROR);
            exit;
        }

        //
        // オブジェクトの生成に失敗していたらエラー
        //
        $logger =& new $className();

        if (!is_object($logger)) {
            return false;
        }

        $logFactory->_list[$name] =& $logger;

        return $logger;
    }

    /**
     * LogFactoryにセットされているLoggerを開放する
     *
     * @access  public
     * @since   3.0.0
     */
    function clear()
    {
        $this->_list = array();
    }

    /**
     * 指定されたLoggerを開放する
     *
     * @param   string  $name   Loggerの名前
     * @access  public
     * @since   3.0.0
     */
    function delete($name)
    {
        unset($this->_list[$name]);
    }
}
?>
