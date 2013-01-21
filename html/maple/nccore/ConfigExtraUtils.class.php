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
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: ConfigExtraUtils.class.php,v 1.11 2006/12/11 06:00:07 Ryuji.M Exp $
 */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
require_once MAPLE_DIR.'/core/ConfigUtils.class.php';

if(version_compare(phpversion(), "5.0.0", ">=")) {
	//read_ini_file用
	include_once MAPLE_DIR ."/core/DIContainerInitializerLocal.class.php";
}

/**
 * 設定ファイルの内容を保持する
 *
 * @package     ConfigUtilsのラッパーくらす
 * @author      Ryuji Masukawa
 * @copyright   
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class ConfigExtraUtils extends ConfigUtils
{
	//再帰かどうか
	var $_recursive = null;
   
   /**
     * コンストラクター
     *
     * @access  public
     */
    function ConfigExtraUtils()
    {
        $this->ConfigUtils();
    }
	/**
     * シンプルに設定を読み込む
     * オプションで読み込むキーを指定することができる
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  array   $config
     * @param  array   $keys [optional]  keys to be read
     */
    function readSimpleConfig($config, $keys=null)
    {
        if(!is_array($keys)) {
            $keys = array_keys($config);
        }
		$recursiveNocall = null;
        if(isset($config['RecursiveNocall'])) {
            $recursiveNocall = $config['RecursiveNocall'];
            unset($config['RecursiveNocall']);
        }
        
        foreach($keys as $key) {
            if(!isset($config[$key])) {
                continue;
            }
            if($this->_recursive == false || $recursiveNocall == NULL || ($recursiveNocall != NULL && !array_key_exists($key,$recursiveNocall))) {        		
	            if($this->_isActionFilter($key)) {
	                $this->_preserve($key, $config[$key]);
	                $this->_actionKey = $key;
	            } else {
	                $this->_mergeOrAdd($key, $config[$key]);
	            }
            }
        }
    }

    /**
     * 設定ファイルを読み込む
     *
     * @access  public
     * @since   3.0.0
     */
    function execute($recursive=false)
    {
    	$this->_recursive = $recursive;
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        
        $this->readConfigFiles($actionChain->getCurActionName());
    }
    
     /**
     * 設定ファイルを読み込む
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 5.0.0ではread_ini_fileするように修正
     * @param  String    $filename
     * @param  boolean    $isDeepest
     */
    function readConfigFile($filename, $isDeepest)
    {
    	
    	if(file_exists($filename)) {
    		if(version_compare(phpversion(), "5.0.0", ">=")) {
    			$initializer =& DIContainerInitializerLocal::getInstance();
    			$config = $initializer->read_ini_file($filename, true);
    		} else {
    			$config = parse_ini_file($filename, true);
    		}
        //if(file_exists($filename) &&
        //   ($config = parse_ini_file($filename, true))) {
            
            if (CONFIG_CODE != INTERNAL_CODE) {
            	$config = mb_convert_encoding($config, INTERNAL_CODE, CONFIG_CODE);
                //mb_convert_variables(INTERNAL_CODE, CONFIG_CODE, $config);
            }
            $this->readConfig($config, $isDeepest);
        }
    }
}
?>