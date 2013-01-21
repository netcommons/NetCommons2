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
 * Pear命名規則に従うコンポーネントを指定するURI
 * クラスファイルがinclude_pathに含まれていることが前提
 * pear://[InjectType[:InitOption]@]ClassName
 * 
 * @author Hawk
 * @package maple.componentlocator
 * @access public
 */
class ComponentLocator_Pear extends ComponentLocator
{
    /**
     * Constructor
     * 
     * @param string
     * @param Array
     */
    function ComponentLocator_Pear($name)
    {
        parent::ComponentLocator($name);
    }
    
    /**
     * クラス名とファイルパスを取得、読み込み、UniFactoryへの委譲
     * 
     * @override
     * @access public
     * @param Array        UCLをパースした配列 classNameのみ必須
     * @param Array        [Optional] 初期化に用いる引数
     * @return Object or null
     */
    function &_initComponent($parts, $args=array())
    {
        $obj = null;
        //className is required
        if($parts['className']=="") {
            return $obj;
        }

        $className = $parts['className'];
        $classPath = str_replace('_', '/', $className) .".php";

        //default type is constructor
        $initType  = ($parts['initType'] != "") ? $parts['initType'] : "constructor";

        //because file_exists does not search include_path.
        if(!@include_once($classPath)) {
            return $obj;
        }
        
        $obj =& UniFactory::createInstance($className, $initType, $args, $parts['initOption']);

        /*
            最低限のエラーチェックだが、本来はもっとしっかりしたエラー処理
            （厳密なチェックという話ではなく、ユーザへの伝達機構）が必要
        */
        if(is_a($obj, 'PEAR_Error')) {
            //TODO : error raising ...
            return $obj;
        }
        return $obj;
    }
}

?>
