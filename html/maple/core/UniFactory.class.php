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

/**
 * クラス名も引数も初期化法も動的に定まる場合に、
 * インスタンスを取得する統一的なインターフェイスを提供する
 * 
 * 初期化のプロセスを変更したい場合、UniFactoryのサブクラスを作って
 * UniFactory::loadFactory(new SubClass);
 * とする
 * 
 * @author Hawk
 * @package maple.core
 * @acecss public
 */
class UniFactory
{
    /**
     * サブクラスで置換可能にするため
     * インスタンス変数として保持しておく
     * 
     * @var UniFactory
     * @access private
     */
    var $_factory;
    
    /**
     * publicなプロパティに対するInjectionを行うか
     * デフォルトで On
     * 
     * @var bool
     * @access private
     */
    var $_propertyInjection = true;
    
    /**
     * Constructor
     * 
     * 
     */
    function UniFactory()
    {
        $this->_factory =& $this;
    }
    
    /**
     * UniFactoryの唯一のインスタンスを取得する
     * 
     * @static
     * @access private
     * @return UniFactory
     */
    function &_singleton()
    {
        static $singleton = null;
        if($singleton == null) {
            $singleton = new UniFactory();
        }
        return $singleton;
    }
    
    /**
     * 使用するサブクラスのインスタンスを登録する
     * 
     * @static
     * @access public
     * @param $factory  UniFactory
     */
    function loadFactory(&$factory)
    {
        $singleton =& UniFactory::_singleton();
        $singleton->_factory =& $factory;
    }
    
    /**
     * 登録されているサブクラスのインスタンスを返却する
     * 
     * @static
     * @access public
     * @return UniFactory
     */
    function &getFactory()
    {
        $singleton =& UniFactory::_singleton();
        return $singleton->_factory;
    }
    

    function usePropertyInjection($bool = null)
    {
        if($bool !== null) {
            $this->_propertyInjection = $bool;
        }
        return $this->_propertyInjection;
    }

    /**
     * $className のインスタンスを取得する
     * 
     * @static
     * @access public
     * @param string    setter|constructor|factory
     * @param Array        [Optional] arguments
     * @param mixed        [Optional] optional parameter (e.g factory-method's name)
     * @return Object or null
     */
    function &createInstance($className, $initType, $args=array(), $initOption=null)
    {
        $instance = null;
        if(isset($this) && is_a($this,'UniFactory')) {
            /*
                インスタンスメソッドとして呼び出した場合、
                ”自分自身”の_createInstanceを起動する
            */
            $instance =& $this->_createInstance($className, $initType, $args, $initOption);
        } else {
            /*
                静的メソッドとして呼び出した場合、
                登録されているサブクラスの_createInstanceを起動する
            */
            $factory =& UniFactory::getFactory();
            $instance =& $factory->_createInstance($className, $initType, $args, $initOption);
        }
        return $instance;
    }
    
    /**
     * $className のインスタンスを取得する
     * サブクラスでオーバーライド可
     * 
     * @access protected
     * @param string    setter|constructor|factory
     * @param Array        [Optional] arguments
     * @param mixed        [Optional] optional parameter (e.g factory-method's name)
     * @return Object or null
     */
     function &_createInstance($className, $initType, $args=array(), $initOption=null)
     {
        $obj = null;
        if(!class_exists($className)) {
            return $obj;
        }
        
        if($initType=='setter') {
            /*
                セッター・インジェクションによる初期化
                maple.core.BeanUtils::setAttributes と異なり
                セッターメソッドの名称のみで判断
            */
            $obj =& new $className();
            $classVars = get_class_vars($className);

            foreach($args as $argName => $value) {
                $setterName = "set". ucfirst($argName);
                
                if(method_exists($obj, $setterName)) {
                    $obj->$setterName($args[$argName]);
                    
                } elseif($this->_propertyInjection && array_key_exists($argName, $classVars)) {
                    if(is_object($value)) {
                        $obj->$argName =& $args[$argName];
                    } else {
                        $obj->$argName = $value;
                    }
                }
            }

        } elseif($initType=='constructor') {
            /*
                コンストラクタ・インジェクションによる初期化
                やむを得ずevalを使用したが、serialize と call_user_func_array を駆使して
                後からコンストラクタを呼び出すという魔術的な方法もあるらしい
                http://jp2.php.net/manual/ja/language.oop.php
            */
            $len = count($args);
            if($len==0) {
                $obj =& new $className();
            } else {
                $args = array_values($args);
                $argList = $this->_makeArgList('args', $len);
                $script = '$obj =& new '.$className.'('. $argList .');';
                eval($script);
            }

        } elseif($initType=='factory' && $initOption != null) {
            /*
                ファクトリメソッドを使った初期化
                今のところinitOptionはこのためだけに存在
            */
            $cb = array($className, $initOption);

            if(is_callable($cb)) {
                $args = array_values($args);
                $len  = count($args);
                $argList = $this->_makeArgList('args', $len);
                $script = '$obj =& '.$className.'::'. $initOption .'('. $argList .');';
                eval($script);
            } else {
                return $obj;
            }

        }
        return $obj;
    }
    
    
    /**
     * パラメータ用の文字列を生成する
     * 
     * @param string
     * @param string 
     * @return string
     */
    function _makeArgList($name, $len)
    {
        $argList = array();
        for($i=0; $i<$len; ++$i) {
            $argList[] = '$'. $name .'['. $i .']';
        }
        return join(',', $argList);

    }
}

?>
