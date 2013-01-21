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

require_once MAPLE_DIR .'/core/ComponentLocator.class.php';

/**
 * ComponentLocatorのサブクラスを初期化
 * インスタンスのキャッシュを行うためのクラス
 * 
 * @author Hawk
 * @package maple.dicontainer
 * @access public
 */
class ComponentLocatorFactory
{
    var $_locatorDir = '.';

    var $_locators = array();

    var $_locatorInitArgs = array();
    
    /**
     * Constructor
     * 
     * 
     */
    function ComponentLocatorFactory()
    {
        
    }
    
    /**
     * ComponentLocatorのインスタンスを取得する
     * - 既にインスタンスが登録されていたらそれを返す
     * - 登録されていない場合は_locatorDirからクラスファイルをインクルードして初期化
     *  - 初期化の際には_locatorInitArgsを用いる
     * 
     * @access public
     * @param string  schema or URI
     * @return ComponentLocator
     */
    function &getLocator($scheme)
    {
        $loc = null;
 
        //cut out after ':', not check whether URL is valid or not
        $scheme = preg_replace('/:(.+)$/', '', $scheme); 
        if(isset($this->_locators[$scheme])) {
            $loc =& $this->_locators[$scheme]; 
            return $loc;
        }

        $className = $this->_getClassName($scheme);
        $classPath = $this->_getClassPath($scheme);

        if(!(@include_once $classPath) || !class_exists($className)) {
            return $loc;
        }
  
        $this->_locators[$scheme] =& new $className(
            $scheme,
            isset($this->_locatorInitArgs[$scheme]) ? $this->_locatorInitArgs[$scheme] : array()
            );
        $loc =& $this->_locators[$scheme];
        return $loc;
    }
    
    /**
     * ComponentLocatorのコンストラクタに渡す引数
     * 
     * @access public
     * @param name
     * @param mixed
     */
    function setInitArgs($name, $args)
    {
        $this->_locatorInitArgs[$name] = $args;
    }
    
    /**
     * 直接インスタンスを登録する
     * 
     * @param string
     * @param ComponentLocator
     * @return bool
     */
    function registerLocator($name, &$locator)
    {
        if(isset($this->_locators[$name])) {
            //conflict
            return false;
        } else {
            $this->_locators[$name] =& $locator;
            return true;
        }
    }

    /**
     * ComponentLocator_$scheme のクラス名を返す
     * 
     * @access private
     * @param string   scheme
     * @return string   class name
     */
    function _getClassName ($scheme)
    {
        return "ComponentLocator_". ucfirst($scheme);
    }

    /**
     * ComponentLocator_$scheme のファイルパスを返す
     * 
     * @access private
     * @param string   scheme
     * @return string   class path
     */
    function _getClassPath ($scheme)
    {
        return ($this->_locatorDir == "" ? "" : ($this->_locatorDir ."/")) .
        $this->_getClassName($scheme) .".class.php";
    }
    
    function setLocatorDir($d)
    {
        $this->_locatorDir = $d;
    }

}

?>
