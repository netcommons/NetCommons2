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
 * _baseDirに配置された任意のコンポーネントを指定するURI
 * maple://[InjectType[:InitOption]@]ClassName/path/to/[ClassName.class.php]
 * 末尾が / で終わる場合、 ClassName.class.php が付け加えられる
 * 
 * @author Hawk
 * @package maple.componentlocator
 * @access public
 */
class ComponentLocator_Maple extends ComponentLocator
{
    var $_baseDir = '.';
    
    /**
     * Constructor
     * 
     * @param string
     * @param Array
     */
    function ComponentLocator_Maple($name, $args=array())
    {
        parent::ComponentLocator($name);
        if(isset($args['baseDir'])) {
            $this->_baseDir = $args['baseDir'];
        }
    }
    
    /**
     * クラス名とファイルパスを取得、読み込み、UniFactoryへの委譲
     * 
     * @override
     * @access public
     * @param Array        URIをパースした配列 classNameとclassPathが必須
     * @param Array        [Optional] 初期化に用いる引数
     * @return Object or null
     */
    function &_initComponent($parts, $args=array())
    {
        $component = null;
        //className and classPath are required
        if($parts['className']=="" || $parts['classPath']=="") {
            return $component;
        }

        $className = $parts['className'];
        $classPath = $this->_makeClassPath($parts, $className);

        //default init type is setter
        $initType  = ($parts['initType']!="") ? $parts['initType'] : "setter";

        if(!(@include_once $classPath)) {
            return $component;
        }

        $component =& UniFactory::createInstance($className, $initType, $args, $parts['initOption']);
        return $component;
    }


    /**
     * 
     * @access private
     * @since  1.2.0a1
     * @param  array     $parts
     * @param  String    $className
     * @return String
     */
    function _makeClassPath($parts, $className)
    {
        $path = $parts['classPath'];

        if($this->_baseDir == "") {
            $path = preg_replace('|^/|', '', $path);
        }

        return $this->_baseDir .
                ((preg_match('|\/$|', $path) || $path == "")
                 ? ($path . $className .".class.php") : $path);
    }
}

?>
