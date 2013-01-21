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

require_once MAPLE_DIR .'/core/UniFactory.class.php';
require_once MAPLE_DIR .'/core/ComponentLocatorFactory.class.php';

/**
 * コンポーネントの位置と初期化方法を表す URI をパースして
 * 初期化を行うためクラス
 * 
 * @author Hawk
 * @package maple.componentlocator
 * @abstruct
 * @access public
 */
class ComponentLocator
{
    var $_name;
    
    /**
     * Constructor
     * $name はこのLocatorが初期化の対象とするscheme
     * （つまりComponentLocator_Foo が foo://を対象とするとは限らない）
     * 
     * @access public
     * @param string  初期化対象scheme
     */
    function ComponentLocator($name)
    {
        $this->_name = $name;
    }
    
    /**
     * $scheme が初期化対象であるか否かを判定する
     * 
     * @param string 
     * @return bool
     */
    function isMatch($scheme)
    {
        return ($this->_name == $scheme);
    }
    
    /**
     * コンポーネントを取得する。但しURIをパースして妥当性を検証するだけで、
     * 実際の処理内容はサブクラスごとに _initComponent で実装する
     * 
     * @access public
     * @param string path
     * @param Array [Optional] arguments
     * @return Object or null
     */
    function &getComponent($path, $args=array())
    {
        $component = null;
        $parts = $this->_parseURI($path);
        if(!$parts) return $component;
        
        $component =& $this->_initComponent($parts, $args);
        return $component;
    }
    
    /**
     * 実際のコンポーネント初期化処理
     * 
     * @abstruct
     * @access protected
     * @param Array path
     * @param Array [Optional] arguments
     * @return Object or null
     */
    function &_initComponent($parts, $args)
    {
        return $this->doInstantiation($parts, $args);
    }
    
    /**
     * URIを解析して、その各部分に分かりやすい別名を付ける
     * 
     * @access protected
     * @param string  URI
     * @return Array 
     */
     function _parseURI($path)
     {
        $parts = @parse_url($path);

        /*
            parse failed or 
            path contains no scheme or 
            sheme and class are mismatch
        */
        if($parts===false || 
            !isset($parts['scheme']) ||
                !$this->isMatch($parts['scheme'])) {
            return false;
        }

        //set aliases
        $aliases = array(
            'scheme' => 'scheme',
            'host'   => 'className',
            'path'   => 'classPath',
            'user'     => 'initType',
            'pass'   => 'initOption'
        );
        foreach($aliases as $orig => $alias) {
            $parts[$alias] = isset($parts[$orig]) ? $parts[$orig] : "";
        }
        return $parts;
    }

    
    /**
     * 
     * @deprecated
     */
    function &doInstantiation($parts, $args){}

}
?>
