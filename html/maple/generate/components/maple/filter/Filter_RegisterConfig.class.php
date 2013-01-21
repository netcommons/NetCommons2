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
 * @version     CVS: $Id: Filter_RegisterConfig.class.php,v 1.2 2006/10/18 08:55:27 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/filter/Abstract.class.php');

/**
 * generationの対象となるwebappについてのGlobalConfigを
 * DIContainerに登録する
 * 
 * [RegisterConfig]
 * seach_try = 20
 * missing_ok = false
 * 
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Filter_RegisterConfig extends Filter_Abstract
{
    var $componentKey = 'globalConfigDto';

    var $errorType = 'error';
    
    /**
     * CmdArgs2Dtoで生成されたDTO
     * 
     * @var  object  $dto  
     */
    var $dto;
    
    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_RegisterConfig()
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
        
        $actionChain =& $container->getComponent('ActionChain');
        $errorList =& $actionChain->getCurErrorList();
        $className = get_class($this);

        $log =& LogFactory::getLog();
        $log->trace("${className}の前処理が実行されました", "{$className}#execute");

        $this->dto =& $container->getComponent($this->componentKey);
        $this->_prefilter();

        //
        // ここで一旦次のフィルターに制御を移す
        //
        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        //
        // ここに後処理を記述
        //

        $log->trace("${className}の後処理が実行されました", "${className}#execute");
    }

    /**
     * 
     * @since 06/07/16 21:37
     */
    function _prefilter()
    {
        $container =& DIContainerFactory::getContainer();
        $log =& LogFactory::getLog();

        //とりあえず登録しておく
        $globalConfig =& new GlobalConfig(false);
        $container->register($globalConfig, 'TargetWebappConfig');

        if(!is_object($this->dto)) {
            $this->_fatalError(
                "{$this->componentKey} is not in the DIContainer",
                __CLASS__ ."#". __FUNCTION__);
            return false;
        }
        
        //NetCommons用に修正
        if(!($webappDir  = $this->_getWebappDir(WORKING_DIR)) or
           !($configPath = $this->_getConfigPath($webappDir)) or
           !($baseDir    = $this->_getBaseDir(BASE_DIR))) {
            return false;
        }
        //if(!($webappDir  = $this->_getWebappDir(WORKING_DIR)) or
        //   !($configPath = $this->_getConfigPath($webappDir)) or
        //   !($baseDir    = $this->_getBaseDir($webappDir))) {
        //    return false;
        //}

        $globalConfig->setValue('WEBAPP_DIR',  $webappDir);
        $globalConfig->setValue('BASE_DIR',    $baseDir);
        $globalConfig->setValue('WORKING_DIR', WORKING_DIR);
        
        $globalConfig->loadFromFile($configPath);
    }

    /**
     * デフォルトのGLOBAL_CONFIG
     * 
     * @since 06/07/18 16:05
     * @return String
     */
    function _getDefaultConfig()
    {
        $container =& DIContainerFactory::getContainer();
        $fileUtil  =& $container->getComponent('fileUtil');
        
        return $fileUtil->findIncludableFile('maple/config/webapp/'. GLOBAL_CONFIG);
    }

    /**
     * 
     * @since 06/07/18 16:48
     * @param  String    $webappDir
     * @return String or false
     */
    //NetCommons用に修正
    //function _getBaseDir($webappDir)
    function _getBaseDir($baseDir)
    {
        $dto =& $this->dto;
        
        if(isset($dto->baseDir) && $dto->baseDir) {
            if(!file_exists($dto->baseDir) || !is_dir($dto->baseDir)) {
                $this->_error(
                    "{$dto->baseDir} is not a valid BASE_DIR");
                return false;
            } else {
                return $dto->baseDir;
            }
        }
        //NetCommons用に修正
        return $baseDir;
        //return dirname(preg_replace('|[\\/]$|', '', $webappDir));
    }

    /**
     * 
     * @since 06/07/18 15:50
     * @param  String    $webappDir
     * @return String
     */
    function _getConfigPath($webappDir)
    {
        if(isset($this->dto->configPath) && $this->dto->configPath) {
            if(!file_exists($this->dto->configPath)) {
                $this->_error(
                    "{$this->dto->configPath} is not found");
                return false;
            } else {
                return $this->dto->configPath;
            }
        }
        
        if(file_exists($webappDir ."/config/". GLOBAL_CONFIG)) {
            return $webappDir ."/config/". GLOBAL_CONFIG;
        } else {
            return $this->_getDefaultConfig();
        }
    }

    /**
     * 
     * @since 06/07/18 16:05
     * @param  String    $wd  current working directory
     * @return String
     */
    function _getWebappDir($wd)
    {
        $webappDir = null;

        $dto =& $this->dto;
        if(isset($dto->webappDir) && $dto->webappDir) {
            //webappDirが直接指定されている場合
            $webappDir = $dto->webappDir;
            
        } elseif(isset($dto->baseDir) && $dto->baseDir &&
                 isset($dto->webappName)  && $dto->webappName) {
            //baseDirおよびwebappが指定されている場合
            $webappDir = $dto->baseDir ."/". $dto->webappName;
            
        } elseif($this->getAttribute('workDir_is_baseDir') &&
                 isset($dto->webappName) && $dto->webappName) {
            //working directoryをbaseDirとして使い、
            //かつwebappNameが指定されている場合
            $webappDir = $wd ."/". $dto->webappName;

        } else {
            //その他の場合
            $webappDir = $this->_searchWebappDir($wd);
        }
        $webappDir = preg_replace('|[\\/]$|', '', $webappDir);
        
        if(!$this->getAttribute('missing_ok') &&
           !file_exists($webappDir ."/config/". GLOBAL_CONFIG)) {
            
            $this->_error(
                "{$webappDir} is not a valid WEBAPP_DIR");
            return false;
        }
        return $webappDir;
    }

    /**
     * 
     * @since 06/07/18 16:05
     * @param  String    $wd  current working directory
     * @return String
     */
    function _searchWebappDir($wd)
    {
        $try = $this->getAttribute('search_try');

        if($try <= 0) {
            return $wd;
        }

        $prev = "";
        $crr = $wd;
        while($try > 0 && $crr && $crr != $prev) {
            if(file_exists($crr ."/config/". GLOBAL_CONFIG)) {
                return $crr;
            }
            
            $prev = $crr;
            $crr = realpath($crr ."/../");
            $try--;
        }
        return $wd;
    }
}

?>
