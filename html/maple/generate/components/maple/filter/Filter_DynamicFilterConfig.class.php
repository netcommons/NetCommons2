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
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_DynamicFilterConfig.class.php,v 1.1 2006/10/13 08:50:13 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/filter/Abstract.class.php');

/**
 * 
 * 
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Filter_DynamicFilterConfig extends Filter_Abstract
{
    var $_component;

    var $_method;

    var $_targetFilterName;
    
    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_DynamicFilterConfig()
    {
        parent::Filter_Abstract();
    }

    /**
     * 
     *
     * @access  public
     */
    function execute()
    {
        $container =& DIContainerFactory::getContainer();
        $filterChain =& $container->getComponent("FilterChain");
        $className = get_class($this);

        $log =& LogFactory::getLog();
        $log->trace("${className}の前処理が実行されました", "{$className}#execute");

        //
        // ここに前処理を記述
        //

        $this->_component = $this->getAttribute('component');
        $this->_method    = $this->getAttribute('method');
        $this->_targetFilterName = $this->getAttribute('targetFilterName');
        
        if($this->_validate()) {
            $componentAttrs = $this->_getAttrsFromComponent();
            if(is_array($componentAttrs)) {
                $this->_setAttrsToFilter($filterChain, $componentAttrs);
            }
        }

        //
        // ここで一旦次のフィルターに制御を移す
        //
        $filterChain->execute();

        //
        // ここに後処理を記述
        //

        $log->trace("${className}の後処理が実行されました", "${className}#execute");
    }

    /**
     * 
     * @since 06/07/29 14:10
     * @return String
     */
    function _validate()
    {
        $caller = __CLASS__ .'#'. __FUNCTION__;
        
        if(!$this->_targetFilterName) {
            $this->_fatalError(
                'filter name is not specified',
                $caller);
            return false;
        }

        if(!$this->_component) {
            $this->_fatalError(
                'component is not specified',
                $caller);
            return false;
        }

        if(!$this->_method) {
            $this->_fatalError(
                'method name is not specified',
                $caller);
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @since 06/07/29 14:04
     * @param  Object $filterChain
     * @param  array    $attrs
     * @return boolean
     */
    function _setAttrsToFilter(&$filterChain, $attrs)
    {
        if(count($attrs) == 0) {
            return true;
        }

        $name = $this->_targetFilterName;
        $filter =& $filterChain->getFilterByName($name);
        if(!is_object($filter)) {
            return false;
        }
        $filter->setAttributes($attrs);
        return true;
    }

    /**
     * 
     * @since 06/07/29 14:04
     * @return array
     */
    function _getAttrsFromComponent()
    {
        $caller = __CLASS__ .'#'. __FUNCTION__;
        
        $container =& DIContainerFactory::getContainer();
        $c =& $container->getComponent($this->_component);

        if(!is_object($c)) {
            $this->_fatalError("'{$this->_component}' is not registered", $caller);
            return false;
        } elseif(!method_exists($c, $this->_method)) {
            $this->_fatalError("'{$this->_component}' dosen't implement '$this->_method'", $caller);
            return false;
        }

        $method = $this->_method;
        $attrs = $c->$method();
        if(!is_array($attrs)) {
            $log->_fatalError(
                "'{$this->_component}->{$method}()' returned a non-array value",
                $caller);
            return false;
        }
        return $attrs;
    }
}
?>
