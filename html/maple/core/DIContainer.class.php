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
 * @version     CVS: $Id: DIContainer.class.php,v 1.2 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/core/BeanUtils.class.php';

/**
 * Dependency Injectionを実現するクラス
 *
 * ただし、対応するのはSetter Injectionのみ
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class DIContainer
{
    /**
     * @var Containerとして管理するインスタンスを格納
     *
     * @access  private
     * @since   3.0.0
     */
    var $_components;

    /**
     * @var 設定内容を保持する配列
     *
     * @access  private
     * @since   3.0.0
     */
    var $_config;

    /**
     * @var 各クラスの属性の配列
     *
     * @access  private
     * @since   3.0.0
     */
    var $_attributes;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function DIContainer()
    {
        $this->_components = array();
        $this->_config     = array();
        $this->_attributes = array();
    }

    /**
     * 設定ファイルを元にDIContainerを組み立てる
     *
     * @param   string  $filename   設定ファイル名
     * @return  boolean Containerの組み立てに成功したかどうか
     * @access  public
     * @since   3.0.0
     */
    function create($filename)
    {
        $log =& LogFactory::getLog();

        //
        // 設定ファイルの読込み
        //
        if (!$this->_parseConfig($filename)) {
            $log->error("設定ファイルの読み込みに失敗しました($filename)", "DIContainer#create");
            return false;
        }

        //
         // 設定を元にComponentを生成
        //
        if (!$this->_buildComponents()) {
            $log->error("Componentの生成に失敗しました", "DIContainer#create");
            return false;
        }

        //
        // 各Componentに属性をInjection
        //
        if (!$this->_injectAttributes()) {
            $log->error("属性のInjectに失敗しました", "DIContainer#create");
            return false;
        }

        return true;
    }

    /**
     * 設定ファイルを読み込む
     *
     * @param   string  $filename   設定ファイル名
     * @return  boolean 設定ファイルが読み込めたか？(true/false)
     * @access  private
     * @since   3.0.0
     */
    function _parseConfig($filename)
    {
        if (!@file_exists($filename)) {
            return false;
        }

        $config = parse_ini_file($filename, TRUE);

        if (!isset($config) || (count($config) < 1)) {
            return false;
        }

        $this->_config = $config;

        return true;
    }

    /**
     * 設定を元にComponentを生成
     *
     * @return  boolean 全てのComponentが生成できたか？(true/false)
     * @access  private
     * @since   3.0.0
     */
    function _buildComponents()
    {
        $log =& LogFactory::getLog();

        //
        // 自分自身を'container'という名前で登録しておく
        //
        $this->register($this, 'container');

        foreach ($this->_config as $name => $body) {
            list ($alias, $class) = preg_split("/:/", $name);

            $this->_attributes[$alias] = array();

            foreach ($body as $key => $values) {
                $this->_attributes[$alias][$key] = preg_split("/,/", $values);
            }
    
            //
            // 指定されているクラス名が不正だったらエラー
            //
            if (!preg_match("/^[0-9a-zA-Z.]+$/", $class)) {
                $log->error("クラス名が不正です($class)", "DIContainer#_buildComponents");
                return false;
            }

            list ($className, $filename) = $this->makeNames($class, true);

            if (!$className) {
                return false;
            }

            //
            // オブジェクトの生成に失敗していたらエラー
            //
            //include_once($filename);

            $component =& new $className();

            if (!is_object($component)) {
                $log->error("オブジェクトの生成に失敗しました($className)", "DIContainer#_buildComponents");
                return false;
            }

            $this->register($component, $alias);
        }

        return true;
    }

    /**
     * 設定を元にComponentに値をInjection
     *
     * @return  boolean Injectionに成功したか？(true/false)
     * @access  private
     * @since   3.0.0
     */
    function _injectAttributes()
    {
        foreach ($this->_config as $name => $body) {
            list ($alias, $class) = explode(":", $name);

            $attributes = array();

            foreach ($this->_attributes[$alias] as $key => $values) {
                foreach ($values as $value) {
                    $value = trim($value);
                    if (preg_match("/^ref:/", $value)) {
                        $value = preg_replace("/^ref:/", "", $value);
                        if (!isset($this->_components[$value])) {
                            return false;
                        }
                        $attributes[$key][] =& $this->_components[$value];
                    } else {
                        $attributes[$key][] = $value;
                    }
                }

                if (isset($attributes[$key]) &&
                    (count($attributes[$key]) == 1)) {
                    $work =& $attributes[$key][0];
                    unset($attributes[$key]);
                    $attributes[$key] =& $work;
                }
            }

            $this->setAttributes($this->_components[$alias], $attributes);
        }

        return true;
    }

    /**
     * Componentに属性をInjection
     *
     * @param   Object  $component  Componentのインスタンス
     * @param   array   $attributes 値が入った配列
     * @access  public
     * @since   3.0.0
     */
    function setAttributes(&$component, $attributes)
    {
        BeanUtils::setAttributes($component, $attributes);
    }

    /**
     * ContainerにComponentのインスタンスをセット
     *
     * @param   Object  $component  Componentのインスタンス
     * @param   string  $name   Component名
     * @access  public
     * @since   3.0.0
     */
    function register(&$component, $name = '')
    {
        if (!is_object($component)) {
            return;
        }

        if ($name == '') {
            $name = get_class($component);
        }

        $this->_components[$name] =& $component;
    }

    /**
     * 設定ファイルを元にComponentのインスタンスを返却
     *
     * @param   string  $name   Component名
     * @return  Object  Componentのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getComponent($name)
    {
        $component = NULL;

        if (isset($this->_components[$name])) {
            $component =& $this->_components[$name];
        }

        return $component;
    }

    /**
     * Componentのクラス名およびファイルパスを返却する
     *
     * @param   string  $class  Component名
     * @param   boolean $check  ファイルの存在チェックをするかどうか
     * @return  array   Componentのクラス名とファイルパス
     * @access  public
     * @since   3.0.0
     */
    function makeNames($class, $check = false)
    {
        $pathList   = explode(".", $class);
        $ucPathList = array_map('ucfirst', $pathList);

        $basename = ucfirst(array_pop($pathList));

        $classPath = join("/", $pathList);
        $className = join("_", $ucPathList);
        if($classPath != "") {
            $filename = "${classPath}/${basename}.class.php";
        } else {
            $filename = "${basename}.class.php";
        }
        //$filename = COMPONENT_DIR . "/${classPath}/${basename}.class.php";

        if (!$check) {
            return array($className, $filename);
        }
		if (!(@include_once $filename)) {
            $filename = "${classPath}/${className}.class.php";
            if (!(@include_once $filename)) {
        //if (!@file_exists($filename)) {
        //    $filename = COMPONENT_DIR . "/${classPath}/${className}.class.php";
        //    if (!@file_exists($filename)) {
                $log =& LogFactory::getLog();
                $log->error("クラスファイルがありません($filename)", "DIContainer#makeNames");
                $className = null;
                $filename  = null;
            }
        }

        return array($className, $filename);
    }
}
?>
