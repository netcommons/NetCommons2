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

require_once(MAPLE_DIR ."/core/DIContainerInitializerLocal.class.php");

/**
 * DIContainerの準備を行うFilter
 * 複数ファイルの読み込みに対応する
 * 他featureは全てDIContainerInitializerに依存する
 * 
 * @author Hawk
 * @package maple.filter
 * @access public
 */
class Filter_DIContainer extends Filter
{
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_autoLoad = false;
    
    var $_dicon = 'dicon.ini';
    
    /**
     * Constructor
     * 
     * 
     */
    function Filter_DIContainer()
    {
        parent::Filter();
        $this->_attributes['filenames'] = array();
    }

    /**
     * プレフィルタ
     * DIContainerの初期化を行う
     * 
     * @access private
     */
    function _prefilter()
    {
        $actionChain =& $this->_container->getComponent("ActionChain");
        $filePaths = $this->_getFilesForAction($actionChain->getCurActionName());
        
        $initializer =& DIContainerInitializerLocal::getInstance();
        
        $loadOnlyOnce = $this->getAttribute('loadOnlyOnce');
        $loadOnlyOnce = ($loadOnlyOnce === null ? true : $loadOnlyOnce);
        foreach($filePaths as $path) {
            if(!($loadOnlyOnce && $initializer->isLoaded($path))) {
            	if(!$initializer->loadConfig($path)) {
                	$this->_log->error("DIContainerの初期化に失敗しました". $path, "Filter_DIContainer#_prefilter");
                    break;
                }
            }
        }
    }
    
    /**
     * アクション $actionName に対する設定ファイルを取得する
     * autoloadの設定によって読み込み方を変える
     * 
     * @param string  action name
     * @param string  webapp_dir
     * @param string  module_dir
     * @return array
     */
    function _getFilesForAction($actionName, $webapp_dir=WEBAPP_DIR, $module_dir=MODULE_DIR)
    {
        $autoload = $this->_isAutoLoad();

        if($autoload) {
            return $this->_getFilesAuto($actionName, $webapp_dir, $module_dir);
        } else {
            return $this->_getFilesManual($actionName, $webapp_dir, $module_dir);
        }
    }
    
    /**
     * ファイルパスの解決を行い、重複を削除する
     * 自動読み込み版
     * 
     * @param string  action name
     * @param string  webapp_dir
     * @param string  module_dir
     * @return array
     */
    function _getFilesAuto($actionName, $webapp_dir=WEBAPP_DIR, $module_dir=MODULE_DIR)
    {
        /* 明示的に指定されたファイル */
        $absolute = array();
        $relative = array();
        
        $filenames = $this->getAttribute("filenames");
        foreach($filenames as $path) {
            if(strncmp("/", $path, 1) == 0) {
            //if(preg_match('|^/|', $path)) {
                $absolute[] = $webapp_dir .$path;
            } else {
                $relative[] = $module_dir ."/". str_replace('_', '/', $actionName) ."/". $path;
            }
        }
        $manual = array_merge($absolute, $relative);

        //$manual = array_unique($manual);

        //var_dump($manual);
        
        /* 自動読み込み */
        $auto = array();
        $parts = explode('_', $actionName);
        array_unshift($parts, 'modules');

        $d = $webapp_dir;
        foreach($parts as $p) {
            $d.= '/'. $p;
            $dicon = $d .'/'. $this->_dicon;
            if(file_exists($dicon) && !in_array($dicon, $manual)) {
                $auto[] = $dicon;
            }
        }
        
        $results = array_merge($absolute, $auto, $relative);
        //var_dump($results);
        return $results;
    }

    /**
     * ファイルパスの解決を行い、重複を削除する
     * 手動指定版
     * 
     * @param string  action name
     * @param string  webapp_dir
     * @param string  module_dir
     * @return array
     */
    function _getFilesManual($actionName, $webapp_dir=WEBAPP_DIR, $module_dir=MODULE_DIR)
    {
        $filenames = $this->getAttribute("filenames");
        
        $results = array();
        foreach($filenames as $path) {
            if(strncmp("/", $path, 1) == 0) {
            //if(preg_match('|^/|', $path)) {
                $results[] = $webapp_dir .$path;
            } else {
                $results[] = $module_dir ."/". str_replace('_', '/', $actionName) ."/". $path;
            }
        }
        $results = array_unique($results);
        return $results;
    }
    
    function _postfilter()
    {
        
    }

    /**
     * フィルタ処理を実行
     * 
     * @access public
     */
    function execute()
    {
        $this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $className = get_class($this);

    
        $this->_log->trace("{$className}の前処理が実行されました", "{$className}#execute");
        $this->_prefilter();

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$className}の後処理が実行されました", "{$className}#execute");
    }
    


    /**
     * 複数ファイルを扱えるようにオーバーライドする
     * 
     * @override
     * @access public
     * @param string    $key    属性名
     * @param string    $value  属性の値
     */
    function setAttribute($key, $value)
    {
        if(strncmp("filename", $key, 8) == 0) {
        //if(preg_match('/^filename/', $key)) {
            $this->_attributes['filenames'][] = $value;
        } else {
            $this->_attributes[$key] = $value;
        }
    }
    
    /**
     * 複数ファイルを扱えるようにオーバーライドする
     * 
     * @override
     * @access public
     * @param Array
     */
    function setAttributes($attributes)
    {
        $log =& LogFactory::getLog();

        if (!is_array($attributes)) {
            $log->warn("引数が不正です", get_class($this) ."#setAttributes");
            return false;
        }

        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }
    
    function setFilenames($files)
    {
        $this->_attributes['filenames'] = $files;
    }
    
    function _isAutoLoad()
    {
        $r = $this->getAttribute('autoload');
        return ($r !== null ? $r : $this->_autoLoad);
    }
}
?>
