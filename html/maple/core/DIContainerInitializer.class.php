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
 * @package     Maple.filter.DIContainer2
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

require_once MAPLE_DIR ."/core/ConfigParser.class.php";

/*
 interface MutableContainer extends DIContainer
 {
     function register($component, $key='');
 }
 */

/**
 * DIContainerの初期化を行うクラス
 * 
 * @package maple.dicontainer
 * @author  Hawk
 */
class DIContainerInitializer extends ConfigParser
{
    /**
     * dicon:// に対応するComponentLocator
     * 
     * @var ComponentLocator
     * @access private
     */
    var $_locator_dicon;

    /**
     * DIContainer のインスタンス
     * 
     * @var MutableContainer
     * @access private
     */
    var $_container;
    
    /**
     * represents arguments of component to instantiate
     * array(componentName1 => array(arg1 => value1, arg2 => value2 , ...), ...)
     * 
     * @var Array
     * @access private
     */
    var $_arguments;
    
    /**
     * represents URIs
     * array(componentName1 => URI1, componentName1 => URI2)
     * 
     * @var Array
     * @access private
     */
    var $_paths;
    
    var $_sections;

    var $_config_code;
    
    var $_internal_code;

    var $_errorCallback;
    
    var $_factory;
    
    var $_loadedFiles = array();

    /**
     * Constructor
     * 
     * @access public
     */
    function DIContainerInitializer(&$container, &$locatorFactory, $errHandler=null, $config_code="", $internal_code="")
    {
        $this->ConfigParser();

        $this->_container=& $container;
        $this->_factory  =& $locatorFactory;
        $this->_locator_dicon =& $locatorFactory->getLocator('dicon');

        $this->_errorCallback = $errHandler;

        $this->setConfigEncoding($config_code);
        $this->setInternalEncoding($internal_code);
        
        $this->clear();
    }

    /**
     * 全ての設定を削除する
     * 
     * @access public
     * 
     */
    function clear()
    {
        $this->_paths = array();
        $this->_arguments = array();
        $this->_sections = array();
    }
    
    /**
     * $filenameを「このインスタンスで読み込んだか」を返す
     * 
     * @access public
     * @param string $filename
     * @return bool
     */
    function isLoaded($filename)
    {
        return isset($this->_loadedFiles[$filename]);
    }

    /**
     * 設定ファイルの読み込み
     * 実体は単にパースの開始を通知するだけ
     * 
     * @access public
     * @param string 
     * @return bool
     */
    function loadConfig($filename)
    {
        $this->_loadedFiles[$filename] = true;
        return $this->start($filename);
    }

    /**
     * URIとパラメータを元にコンポーネントを取得し、コンテナに格納する
     * 
     * @access private
     * @param string  component's key
     * @return bool
     */
    function _register2Container($key)
    {
        $URI = $this->_fillUpScheme($this->_paths[$key]);
        $args= isset($this->_arguments[$key]) ? $this->_arguments[$key] : array();

        if(!is_object($locator =& $this->_factory->getLocator($URI))) {
            $this->_handleError("Locatorの取得に失敗しました : {$key} = {$URI}" , 'DIContainerInitializer');
            return false;
        }
        $obj =& $locator->getComponent($URI, $args);

        if(is_object($obj)) {
            $this->_container->register($obj, $key);

            /* avoid to register twice */
            unset($this->_paths[$key]);
            unset($this->_arguments[$key]);
            return true;
        } else {
            $this->_handleError('コンポーネントの初期化に失敗しました : '. $key, 'DIContainerInitializer');
        }
        return false;
    }

    /**
     * パラメータ無しかどうかを調べる
     * 
     * @param string
     * @return bool
     */
    function _isNoArgs($componentName)
    {
        return (!isset($this->_sections[$componentName]) || !is_array($this->_sections[$componentName]));
    }
    
    /**
     * 設定ツリーのルートを対象に文字エンコーディング変換を行う
     * 
     * @access public
     * @param string    ファイル名
     * @param Array        設定ツリーのルート
     */
    function doRootBeforeParse($filename, &$root)
    {
        //echo $filename, "<br>";
        if ($this->_config_code != $this->_internal_code) {
            mb_convert_encoding($root, $this->_internal_code, $this->_config_code);
            //mb_convert_variables($this->_internal_code, $this->_config_code, $root);
        }
        $this->_sections = $root;
    }

    /**
     * dicon://～ をコンテナ内のコンポーネントに置換
     * $value を直接置換することは不可能なので $values[$key] を対象とする
     * 
     * Constructor Injection および factoryメソッドによる初期化を行う都合上、
     * この処理は設定を読み込むたびに実行される
     * つまり依存するコンポーネントの設定は設定ファイル中”先”にある必要がある
     * 
     * @access public
     * @param string 
     * @param Array   values
     * @param string  key
     * @param string  value (Don't use this)
     */
    function doValueBeforeParse($sectionName, &$values, &$key, &$value)
    {
        if($sectionName != 'DIContainer') {
            if(preg_match('|^dicon://|', $value)) {
                if(($obj =& $this->_locator_dicon->getComponent($value)) !== null) {
                    $values[$key] =& $obj;
                } else {
                    $values[$key] = null;
                    $this->_handleError('存在しないコンポーネントが指定されました : '.$value, 'DIContainerInitializer#doValueBeforeParse');
                }
            }
        }
    }

    /**
     * 
     * 
     */
    function doSectionBeforeParse(&$sectionName, &$values)
    {
        if($sectionName != 'DIContainer' && isset($this->_paths[$sectionName])) {
            $this->_registerBefore($sectionName);
        }
    }

    /**
     * セクション DIContainer が読み込まれた場合、値の配列は Name => URI
     * _pathsに保存する
     * 
     * それ以外は セクション名==Name で、値の配列は初期化時の引数
     * _arguments に引数を保存するとともに初期化・登録を試みる
     * 
     * @access public
     * @param string 
     * @param Array
     */
    function doSectionAfterParse($sectionName, $values)
    {
        //echo __FUNCTION__, "<br>";

        if($sectionName=='DIContainer') {
            foreach($values as $c_name => $URI) {
                $this->_paths[$c_name] = $URI;
            }
        //var_dump($this->_paths);

            return;
        } else {
            $this->_arguments[$sectionName] = $values;
            
            if(isset($this->_paths[$sectionName])) {
                $this->_register2Container($sectionName);
            }
        //var_dump($this->_paths);

        }
    }

    /**
     * パース終了時、残っている情報をパラメータ無しと見なして
     * 全て初期化する
     * 
     */
    function doEnd($filename)
    {
        //echo __FUNCTION__, "<br>";
        //var_dump($this->_paths);

        $this->_registerBefore("");
        $this->clear();
    }

    /**
     * $componentKeyより前に登録された、
     * パラメータ無しのコンポーネントを全て登録する
     * 
     * @access private
     * @param string
     */
    function _registerBefore($componentKey)
    {
        foreach($this->_paths as $cKey => $URI) {
            if($componentKey == $cKey) {
                break;
            }
            if($this->_isNoArgs($cKey)) {
                $this->_register2Container($cKey);
            }
        }
    }
    
    /**
     * shemeの省略を補完する
     * 
     * @access protected
     * @param string
     * @return string
     */
    function _fillUpScheme($uri)
    {
        return $uri;
    }

    function _handleError($msg, $caller)
    {
        if(is_callable($this->_errorCallback)) {
            call_user_func($this->_errorCallback, $msg, $caller);
        }
    }
    
    /**
     * エラー発生時、とりあえずログ
     * 現在のConfigParserにはパースを停止するなどの機構はない
     * 
     * @access public
     * @param string 
     */
    function doError($filename)
    {
        $this->_handleError('設定ファイルのパースに失敗しました : '. $filename, 'DIContainerInitializer#loadConfig');
    }
    
    function setConfigEncoding($config_code="")
    {
        $this->_config_code  = defined('CONFIG_CODE') && $config_code =="" ? CONFIG_CODE : $config_code;
    }
    
    function setInternalEncoding($internal_code="")
    {
        $this->_internal_code= defined('INTERNAL_CODE') && $internal_code =="" ? INTERNAL_CODE : $internal_code;
    }

}

?>
