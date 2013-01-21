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

/*
interface DIContainer
{
    function getComponent($name);
}
*/

/**
 * DIContainer内のコンポーネントを表現する
 * dicon://ComponentName[/MethodName]
 * 
 * @author Hawk
 * @package maple.componentlocator
 * @access public
 */
class ComponentLocator_Dicon extends ComponentLocator
{
    var $_container;
    
    /**
     * Constructor
     * 
     * @param string name
     * @param array ("DIContainer" => DIContainer)
     */
    function ComponentLocator_Dicon($name, $arg)
    {
        parent::ComponentLocator($name);
        $this->_container =& $arg['DIContainer'];
    }
    
    /**
     * DIContainerからコンポーネントを取得する
     * 
     * @override
     * @access public
     * @param Array        UCLをパースした配列 classNameのみ必須
     * @param Array        （現在は使用しない） 初期化に用いる引数
     * @return Object or null
     */
    function &_initComponent($parts, $args=array())
    {
        $component = null;
        $container =& $this->_container;
        
        //className is required
        if($parts['className']=="") {
            return $component;
        }
        
        $methodName = "";
        if(preg_match('|/([\w\d]+)|', $parts['classPath'], $m)) {
            $methodName = $m[1];
        }

        $componentName = $parts['className'];
        $obj =& $container->getComponent($componentName);
        
        if($methodName == "") {
            return $obj;
        }
        
        if(!method_exists($obj, $methodName)) {
            return $component;
        }
        $component =& $this->_callObjectMethod($obj, $methodName, $args);
        return $component;
    }
    

    function &_callObjectMethod(&$obj, $methodName, $args=array())
    {
        $args = array_values($args);
        $len = count($args);
        
        $r = null;
        
        /* 3つくらいまでは普通にCall */
        switch($len)
        {
            case 0:
                $r =& $obj->$methodName();
                break;
            case 1:
                $r =& $obj->$methodName($args[0]);
                break;
            case 2:
                $r =& $obj->$methodName($args[0], $args[1]);
                break;
            case 3:
                $r =& $obj->$methodName($args[0], $args[1], $args[2]);
                break;
            default:
                $tmpArgs = array();
                for($i=0; $i<$len; ++$i) {
                    $tmpArgs[] = '$args['. $i .']';
                }
                
                $script = '$r =& $obj->$methodName(' .join(',', $tmpArgs) .');';
                eval($script);
        }
        return $r;
    }
}

?>
