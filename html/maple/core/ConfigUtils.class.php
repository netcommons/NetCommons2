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
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: ConfigUtils.class.php,v 1.5 2006/11/24 00:59:21 Ryuji.M Exp $
 */

/**
 * 設定ファイルの内容を保持する
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class ConfigUtils
{
    /**
     * @var 各セクションの値を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_config;

    /**
     * 設定情報を一時的に保存する
     * 
     * @var  String  $_configPool
     * @since 3.2.0
     */
    var $_configPool;

    /**
     * Actionフィルタの名称
     * 
     * @var  String  $_actionKey  
     * @since 3.2.0
     */
    var $_actionKey;

    /**
     * DEBUGフィルタを登録するか
     * 
     * @var  boolean  $_debugMode  
     */
    var $_debugMode;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function ConfigUtils()
    {
        $this->clear();
    }

    /**
     * 設定情報をクリア
     *
     * @access  public
     * @since   3.0.0
     */
    function clear()
    {
        $this->_config     = array();
        $this->_configPool = array();
        $this->_actionKey  = "Action";
        $this->_debugMode  = DEBUG_MODE;
    }

    /**
     * 一時保存された情報を取得する
     * 存在しない場合は空の配列を返す
     * 
     * @since  3.2.0
     * @param  String    $key
     * @return array
     */
    function _getPreserved($key)
    {
        return isset($this->_configPool[$key]) ? $this->_configPool[$key] : array();
    }

    /**
     * 設定情報を一時的に保存する
     * 
     * @since  3.2.0
     * @param  String    $key
     * @param  array     $values
     */
    function _preserve($key, $values)
    {
        //To keep the order of keys, can't use array operator '+'.
        foreach($values as $k => $v) {
            $this->_configPool[$key][$k] = $v;
        }
    }

    /**
     * 既に追加されている場合はマージ、
     * そうでなければ一時保存
     * 
     * @since  3.2.0
     * @param  String    $key
     * @param  array     $values
     */
    function _mergeOrPreserve($key, $values)
    {
        if(isset($this->_config[$key])) {
            $this->_mergeOrAdd($key, $values);
        } else {
            $this->_preserve($key, $values);
        }
    }

    /**
     * 既に追加されていたらマージ、
     * そうでなければ新規追加
     * 
     * @since  3.2.0
     * @param  String    $key
     * @param  array     $values
     */
    function _mergeOrAdd($key, $values)
    {
        if(!isset($this->_config[$key])) {
            $this->_config[$key] = $this->_getPreserved($key);
        }
        //To keep the order of keys, can't use array operator '+'.
        foreach($values as $k => $v) {
            $this->_config[$key][$k] = $v;
        }
    }

    /**
     * シンプルに設定を読み込む
     * オプションで読み込むキーを指定することができる
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  array   $config
     * @param  array   $keys [optional]  keys to be read
     */
    function readSimpleConfig($config, $keys=null)
    {
        if(!is_array($keys)) {
            $keys = array_keys($config);
        }

        foreach($keys as $key) {
            if(!isset($config[$key])) {
                continue;
            }
            
            if($this->_isActionFilter($key)) {
                $this->_preserve($key, $config[$key]);
                $this->_actionKey = $key;
            } else {
                $this->_mergeOrAdd($key, $config[$key]);
            }
        }
    }

    /**
     * GlobalFilterの処理も含め、
     * 最下層かどうかも加味して、設定を読み込む
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  array      $config
     * @param  boolean    $isDeepest
     */
    function readConfig($config, $isDeepest)
    {
        $globalFilter = null;
        if(isset($config['GlobalFilter'])) {
            $globalFilter = $config['GlobalFilter'];
            unset($config['GlobalFilter']);
        }

        if($globalFilter === null || $isDeepest) {
            //globalfilterが無い、もしくは最下層
            $this->readSimpleConfig($config);
            return;
        }

        //globalFilter処理
        foreach($config as $key => $values) {
            //ここではActionフィルタかどうかは調べなくて良い
            if(!isset($globalFilter[$key])) {
                $this->_mergeOrPreserve($key, $values);
            }
        }
        $this->readSimpleConfig($config, array_keys($globalFilter));
    }

    /**
     * 設定ファイルを読み込む
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  String    $filename
     * @param  boolean    $isDeepest
     */
    function readConfigFile($filename, $isDeepest)
    {
        if(file_exists($filename) &&
           ($config = parse_ini_file($filename, true))) {
            
            if (CONFIG_CODE != INTERNAL_CODE) {
            	$config = mb_convert_encoding($config, INTERNAL_CODE, CONFIG_CODE);
                //mb_convert_variables(INTERNAL_CODE, CONFIG_CODE, $config);
            }
            $this->readConfig($config, $isDeepest);
        }
    }

    /**
     * アクションに対する全ての設定ファイルを読み込む
     * Debugフィルタは最初に、
     * Actionフィルタは最後に登録する
     * 
     * $readerFuncはテスタビリティのための存在
     * 
     * @since  3.2.0
     * @param  String    $actionName
     * @param  array or string     $readerFunc
     */
    function readConfigFiles($actionName, $readerFunc='readConfigFile')
    {
        $obj =& $this;
        $method = $readerFunc;
        if(is_array($readerFunc) && is_callable($readerFunc)) {
            $obj =& $readerFunc[0];
            $method =& $readerFunc[1];
        }

        $paths    = array_merge(array(""), explode('_', $actionName));
        $crrPath  = MODULE_DIR;
        $depth    = 0;
        $maxDepth = count($paths) - 1;

        if($this->_debugMode) {
            $this->_mergeOrAdd('Debug', array());
        }
        
        foreach($paths as $p) {
            $crrPath .= "{$p}/";
            $configPath = "{$crrPath}". CONFIG_FILE;
            $obj->$method($configPath, ($maxDepth == $depth++));
        }

        $this->_mergeOrAdd($this->_actionKey, array());
    }
    
    /**
     * 設定ファイルを読み込む
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        
        $this->readConfigFiles($actionChain->getCurActionName());
    }

    /**
     * Actionフィルタの一種か調べる
     * 
     * @since  3.2.0
     * @param  String    $key
     * @return boolean
     */
    function _isActionFilter($key)
    {
        return preg_match('/Action$/', $key);
    }
    
    /**
     * セクションの設定情報を返却
     *
     * @return  array   セクションの設定情報の配列
     * @access  public
     * @since   3.0.0
     */
    function &getConfig()
    {
        return $this->_config;
    }

    /**
     * 指定されたセクションの設定情報を返却
     *
     * @param   string  $section    セクション名
     * @return  array   設定情報の配列
     * @access  public
     * @since   3.0.0
     */
    function &getSectionConfig($section)
    {
        if (isset($this->_config[$section])) {
            return $this->_config[$section];
        }
    }
}
?>
