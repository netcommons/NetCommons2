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
 * @version     CVS: $Id: ActionChain.class.php,v 1.6 2006/10/13 08:50:12 Ryuji.M Exp $
 */
// エラークラスは、継承クラスのErrorExtraListを使用するためコメント
//require_once MAPLE_DIR.'/core/ErrorList.class.php';
//
require_once MAPLE_DIR.'/nccore/ErrorExtraList.class.php';

/**
 * Actionを管理するクラス
 *
 * このクラスを使ってActionのForwardに対応する
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class ActionChain
{
    /**
     * @var Actionを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_list;

    /**
     * @var ErrorListを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_errorList;

    /**
     * @var Actionの位置を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_position;

    /**
     * @var 現在実行されているActionの位置を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_index;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function ActionChain()
    {
        $this->_list      = array();
        $this->_errorList = array();
        $this->_position  = array();
        $this->_index     = 0;
    }

    /**
     * Actionクラスをセット
     *
     * @param   string  $name   Actionのクラス名
     * @access  public
     * @since   3.0.0
     */
    function add($name)
    {
        $log =& LogFactory::getLog();

        //
        // 何も指定されていなかったらデフォルトのActionを切り替え
        //
        //
        if ($name == "") {
            $name = DEFAULT_ACTION;
        }

        //
        // Actionのクラス名が不正だったらデフォルトのActionを切り替え
        //
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
            $log->info("不正なActionが指定されています(${name})", "ActionChain#add");
            $name = DEFAULT_ACTION;
        }

        //
        // ファイルが存在していなければデフォルトのActionを切り替え
        //
        list ($className, $filename) = $this->makeNames($name, true);

        if (!$className) {
            $log->info("存在していないActionが指定されています(${name})", "ActionChain#add");
            $name = DEFAULT_ACTION;
            list ($className, $filename) = $this->makeNames($name, true);
        }

        //
        // 既に同名のActionが追加されていたら何もしない
        //
        if (isset($this->_list[$name]) && is_object($this->_list[$name])) {
            $log->info("このActionは既に登録されています(${name})", "ActionChain#add");
            return true;
        }

        //
        // オブジェクトの生成に失敗していたらエラー
        //
        include_once($filename);

        $action =& new $className();

        if (!is_object($action)) {
            $log->error("Actionの生成に失敗しました(${name})", "ActionChain#add");
            return false;
        }

        $this->_list[$name]      =& $action;
        ////$this->_errorList[$name] =& new ErrorList();
        $this->_errorList[$name] =& new ErrorExtraList();
        $this->_position[]       =  $name;

        return true;
    }

    /**
     * Actionのクラス名およびファイルパスを返却する
     *
     * @param   string  $name   Action名
     * @param   boolean $check  ファイルの存在チェックをするかどうか
     * @return  array   Actionのクラス名とファイルパス
     * @access  public
     * @since   3.0.0
     */
    function makeNames($name, $check = false)
    {
        $pathList   = explode("_", $name);
        $ucPathList = array_map('ucfirst', $pathList);

        $basename = ucfirst($pathList[count($pathList) - 1]);

        $actionPath = join("/", $pathList);
        $className  = join("_", $ucPathList);
        $filename   = MODULE_DIR . "/${actionPath}/${basename}.class.php";

        if (!$check) {
            return array($className, $filename);
        }

        if (!@file_exists($filename)) {
            $filename = MODULE_DIR . "/${actionPath}/${className}.class.php";
            if (!@file_exists($filename)) {
                $className = null;
                $filename  = null;
            }
        }

        return array($className, $filename);
    }

    /**
     * ActionChainをクリア
     *
     * @access  public
     * @since   3.0.0
     */
    function clear()
    {
        $this->_list      = array();
        $this->_errorList = array();
        $this->_position  = array();
        $this->_index     = 0;
    }

    /**
     * ActionChainの長さを返却
     *
     * @return  integer ActionChainの長さ
     * @access  public
     * @since   3.0.0
     */
    function getSize()
    {
        return count($this->_list);
    }

    /**
     * ActionChainを次に進めることができるかを返却
     *
     * @return  boolean 次に進めるかどうか？
     * @access  public
     * @since   3.0.0
     */
    function hasNext()
    {
        return ($this->_index < $this->getSize());
    }

    /**
     * ActionChainを次に進める
     *
     * @access  public
     * @since   3.0.0
     */
    function next()
    {
        if ($this->_index < $this->getSize()) {
            $this->_index++;
        }
    }

    /**
     * 現在のAction名を返却
     *
     * @return  string  Actionの名前
     * @access  public
     * @since   3.0.0
     */
    function getCurActionName()
    {
        if (isset($this->_position[$this->_index])) {
            return $this->_position[$this->_index];
        }
    }
    
    /**
     * 登録されているすべてのAction名を返却
     * 
     * @return array Actionの名前のリスト
     * @access  public
     * @since  3.1.0
     */
    function getAllActionName()
    {
        return array_values($this->_position);
    }

    /**
     * 指定された名前のActionを返却
     *
     * @param   string  $name   Action名
     * @return  Object  Actionのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getActionByName($name)
    {
        $result = false;
        $log =& LogFactory::getLog();

        if ($name == "") {
            $log->warn("引数が不正です", "ActionChain#getActionByName");
            return $result;
        }

        if (!isset($this->_list[$name]) || !is_object($this->_list[$name])) {
            $log->error("指定されたActionは登録されていません(${name})", "ActionChain#getActionByName");
            return $result;
        }

        $action =& $this->_list[$name];

        if (!is_object($action)) {
            $log->error("Actionの取得に失敗しました(${name})", "ActionChain#getCurAction");
            return $result;
        }

        return $action;
    }

    /**
     * リストの先頭のActionのインスタンスを返却
     *
     * @return  Object  Actionのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getCurAction()
    {
        $name = $this->getCurActionName();
        return $this->getActionByName($name);
    }

    /**
     * 指定された名前のActionに対するErrorListを返却
     *
     * @param   string  $name   Action名
     * @return  Object  ErrorListのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getErrorListByName($name)
    {
        $result = null;
        if (isset($this->_errorList[$name])) {
            $result =& $this->_errorList[$name];
        }
        return $result;
    }

    /**
     * リストの先頭のActionに対するErrorListのインスタンスを返却
     *
     * @return  Object  ErrorListのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getCurErrorList()
    {
        $name = $this->getCurActionName();
        return $this->getErrorListByName($name);
    }

    /**
     * Actionを実行
     *
     * @return  string  実行したActionの返却値
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();

        if ($this->getSize() < 1) {
            $log->error("Actionが追加されていません", "ActionChain#execute");
            return false;
        }

        $action =& $this->getCurAction();

        if (!is_object($action)) {
            $log->error("Actionの取得に失敗しました(${name})", "ActionChain#execute");
            return false;
        }

        return $action->execute();
    }

    /**
     * Actionを追加
     *
     * 使用しやすいようにクラスメソッドを準備
     *
     * @param   string  $name   Actionのクラス名
     * @access  public
     * @since   3.0.0
     */
    function forward($name)
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        return $actionChain->add($name);
    }
}
?>