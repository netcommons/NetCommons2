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

require_once MAPLE_DIR ."/core/DIContainerInitializer.class.php";
require_once MAPLE_DIR ."/core/ComponentLocatorFactory.class.php";

/**
 * Mapleに依存する情報は全てこのクラス内で設定される
 * 
 * @package maple.dicontainer
 * @author  Hawk
 */
class DIContainerInitializerLocal extends DIContainerInitializer
{
    /**
     * Constructor
     * Maple依存の引数を親のコンストラクタに渡す
     * 
     * @access private
     */
    function DIContainerInitializerLocal()
    {
        $container =& DIContainerFactory::getContainer();
        
        $factory =& new ComponentLocatorFactory();
        $factory->setLocatorDir(MAPLE_DIR .'/componentlocator');
        $factory->setInitArgs('dicon',     array('DIContainer' => &$container));
        $factory->setInitArgs('maple',     array('baseDir' => ""));		//修正 Ryuji.M(ディレクトリ構成がmaple3.1のままのため?)
        $factory->setInitArgs('component', array('baseDir' => ""));	//修正 Ryuji.M(ディレクトリ構成がmaple3.1のままのため?)
		$factory->setInitArgs('modules',   array('baseDir' => MODULE_DIR));		//追加 Ryuji.M
		
        parent::DIContainerInitializer(
            $container, 
            $factory, 
            array(&$this, 'handleError'), 
            CONFIG_CODE, 
            INTERNAL_CODE
        );
    }

    /**
     * 
     * 
     * @override
     * @access protected
     */
    function _fillUpScheme($uri)
    {
        if(preg_match('|^.+://|', $uri)) {
            return $uri;
        }

        if(strstr($uri, '.') !== false) {
            return "component://{$uri}";
            
        } elseif(strstr($uri, '/') !== false) {
            return "maple://{$uri}";
            
        } elseif(strstr($uri, '_') !== false) {
            return "pear://{$uri}";
        }
        
        return "pear://{$uri}";
    }
    
    /**
     * SINGLETON
     * 
     * @static
     * @access public
     * @return DIContainerInitializerLocal
     */
    function &getInstance()
    {
        static $singleton = null;
        if($singleton === null) {
            $singleton = new DIContainerInitializerLocal();
        }
        return $singleton;
    }

    /**
     * エラーハンドラ
     * 
     * @access public
     * @param string  error message
     * @param string  caller info
     */
    function handleError($msg, $caller)
    {
        $log =& LogFactory::getLog();
        $log->error($msg, $caller);
    }
}

?>
