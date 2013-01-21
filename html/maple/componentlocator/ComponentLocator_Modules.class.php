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
 * @author      Ryuji.M
 */

/**
 * _baseDirをルートとする、コンポーネント・クラスの命名規則に従うクラスを初期化する
 * デフォルトのInjectTypeはsetter
 * component://[InjectType[:InitOption]@]path.to.class
 * 
 * @author Hawk
 * @package maple.componentlocator
 * @access public
 */
class ComponentLocator_Modules extends ComponentLocator
{
    var $_baseDir = '.';
    
    /**
     * Constructor
     * 
     * @param string
     * @param Array
     */
    function ComponentLocator_Modules($name, $args=array())
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
     * @param Array     URIをパースした配列 classNameのみ必須
     * @param Array     [Optional] 初期化に用いる引数
     * @return Object or null
     */
    function &_initComponent($parts, $args=array())
    {
        $component = null;
        
        //className is required
        if($parts['className']=="") {
            return $component;
        }

        //default init type is setter.
        $initType  = ($parts['initType']!="") ? $parts['initType'] : "setter";

        $dotSeparated = $parts['className'];
        $className = $this->_makeClassName($dotSeparated);
        
        if(!$this->_includeClass($dotSeparated, $className)) {
            return $component;
        }

        $component =& UniFactory::createInstance($className, $initType, $args, $parts['initOption']);
        return $component;
    }

    /**
     * .で区切られたパスからクラス名を生成
     * 
     * @access private
     * @param string    `.'で区切られたパス
     * @return string 
     */
    function _makeClassName($dotSeparated)
    {
        return str_replace(' ', '_', ucwords(str_replace('.', ' ', $dotSeparated)));
    }

    /**
     * `.'で区切られたパスからファイルパスを生成、読み込む
     * 
     * @access private
     * @param string    `.'で区切られたパス
     * @param string    クラス名
     * @return bool
     */
    function _includeClass($dotSeparated, $className)
    {
        /* 先頭が // になると危険なので */
        if($dotSeparated == "" || $dotSeparated{0} == '.') {
            return false;
        }
        
        $pathNew  = $this->_makeClassPathNew($dotSeparated, $className);
        if (file_exists($pathNew)) {
        	include_once $pathNew;
        	return true;
        }
        $pathOld  = $this->_makeClassPathOld($dotSeparated, $className);
        if (file_exists($pathOld)) {
        	include_once $pathOld;
        	return true;
        }
        $pathPear = $this->_makeClassPathPear($dotSeparated, $className, '.php');
        if (file_exists($pathPear)) {
        	include_once $pathPear;
        	return true;
        }
		
        return false;
    }

    /**
     * `.'で区切られたパスからファイルパスを生成
     * 
     * @access private
     * @param string    `.'で区切られたパス
     * @param string    クラス名
     * @return string
     */
    function _makeClassPathNew($dotSeparated, $className)
    {
        $pathList   = explode(".", $dotSeparated);
        $basename = ucfirst(array_pop($pathList));
        $classPath = join("/", $pathList);
      
        return $this->_getBaseDir() ."${classPath}/${basename}.class.php";
    }
    
    /**
     * `.'で区切られたパスからファイルパスを生成
     * 
     * @access private
     * @param string    `.'で区切られたパス
     * @param string    クラス名
     * @return string
     */
    function _makeClassPathOld($dotSeparated, $className)
    {
        $classPath = $this->_getBaseDir() . str_replace('.', '/', 
            preg_replace('/(\.?)[^\.]+$/', '$1', $dotSeparated)) . $className .".class.php";
        return $classPath;
    }
    
    /**
     * `.'で区切られたパスからファイルパスを生成（PEAR規約版）
     * 
     * @access private
     * @param string `.'で区切られたパス
     * @param string クラス名
     * @param string 接尾辞（デフォルト: .php）
     * @return string
     */
    function _makeClassPathPear($dotSeparated, $className, $suffix='.php')
    {
        return $this->_getBaseDir() . str_replace('_', '/', $className) . $suffix;
    }

    function _getBaseDir()
    {
        return $this->_baseDir ? $this->_baseDir ."/" : "";
    }
}

?>
