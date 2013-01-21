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
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: ErrorList.class.php,v 1.3 2006/07/03 01:19:23 Ryuji.M Exp $
 */

/**
 * 各入力フィールドのエラーを保持するクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class ErrorList
{
    /**
     * @var エラーの種類を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_type;

    /**
     * @var エラー文字列を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_list;

    /**
     * コンストラクタ
     *
     * @access  public
     * @since   3.0.0
     */
    function ErrorList()
    {
        $this->_type = NULL;
        $this->_list = array();
    }

    /**
     * エラー文字列を追加
     *
     * @param   string  $key    エラーが発生した項目
     * @param   string  $str    エラー文字列
     * @access  public
     * @since   3.0.0
     */
    function add($key, $value)
    {
        if (!isset($this->_list[$key])) {
            $this->_list[$key] = array();
        }
        $this->_list[$key][] = $value;
    }

    /**
     * ErrorListをクリア
     *
     * @access  public
     * @since   3.0.0
     */
    function clear()
    {
        $this->_list = array();
    }

    /**
     * 現在エラーがあるかどうかを返却
     *
     * @return  boolean エラーがあるかどうかの真偽値(true/false)
     * @access  public
     * @since   3.0.0
     */
    function isExists()
    {
        return (count($this->_list) > 0);
    }

    /**
     * エラーの種類を返却
     *
     * @return  string  エラーの種類
     * @access  public
     * @since   3.0.0
     */
    function getType()
    {
        return $this->_type;
    }

    /**
     * エラーの種類をセット
     *
     * @param   string  $type   エラーの種類
     * @access  public
     * @since   3.0.0
     */
    function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * 指定された項目のエラーを返却
     *
     * Smartyの属性は連想配列で渡されるため $params["key"] で受け取る
     *
     * @param   string  $params エラーが発生した項目
     * @return  array   エラー文字列の配列
     * @access  public
     * @since   3.0.0
     */
    function getMessage($params)
    {
        $key = $params["key"];

        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $errorList =& $actionChain->getCurErrorList();

        if (isset($errorList->_list[$key])) {
            return $this->_list[$key];
        } else {
            return array();
        }
    }

    /**
     * 登録されているエラー文字列の配列を返却
     *
     * @return  array   エラー文字列の配列
     * @access  public
     * @since   3.0.0
     */
    function getMessages()
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $errorList =& $actionChain->getCurErrorList();

        $messages = $errorList->getSelfMessages();
        return $messages;
    }
    
    /**
     * 自身に登録されているエラー文字列の配列を返却
     * 
     * 本来はこちらが"getMessages"である気がするが既に利用しているので
     * 下位互換のためメソッド名を変えて追加しておく
     *
     * @return  array   エラー文字列の配列
     * @access  public
     * @since   3.0.0
     */
    function getSelfMessages()
    {
        $messages = array();
        foreach ($this->_list as $k => $v) {
            $messages = array_merge($messages, $v);
        }
        return $messages;
    }
    
    /**
     * 登録されているすべてのエラー文字列の配列を返却
     * 
     * @return  array   エラー文字列の配列
     * @access  public
     * @since   3.1.0
     */
    function getAllMessages()
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $actions = $actionChain->getAllActionName();
        
        $messages = array();
        foreach ($actions as $action) {
            $errorList = $actionChain->getErrorListByName($action);
            $mes = $errorList->getSelfMessages();
            $messages = array_merge($messages, $errorList->getSelfMessages());
        }
        return $messages;
    }
}

?>