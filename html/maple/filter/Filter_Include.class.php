<?php
/**
 * ファイルを読み込むFilter
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Include extends Filter
{
    var $_container;

    var $_log;

    var $_filterChain;

    var $_method = "require";

    /**
     * Constructor
     *
     *
     */
    function Filter_Include()
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

        $loadOnlyOnce = $this->getAttribute('loadOnlyOnce');
        $loadOnlyOnce = ($loadOnlyOnce === null ? true : $loadOnlyOnce);
        $method = $this->getAttribute('method');
        $method = ($method === null ? "require" : $method);

        foreach($filePaths as $path) {
			if ($method == "require" && $loadOnlyOnce) {
				require_once($path);
			} elseif ($method == "require" && !$loadOnlyOnce) {
				require($path);
			} elseif ($method == "include" && $loadOnlyOnce) {
				include_once($path);
			} elseif ($method == "include" && !$loadOnlyOnce) {
				include($path);
			}
        }
    }

    /**
     * アクション $actionName に対する設定ファイルを取得する
     *
     * @param string  action name
     * @param string  webapp_dir
     * @param string  module_dir
     * @return array
     */
    function _getFilesForAction($actionName, $webapp_dir=WEBAPP_DIR, $module_dir=MODULE_DIR)
    {
        $filenames = $this->getAttribute("filenames");

        $results = array();
        foreach($filenames as $path) {
            if(preg_match('|^/|', $path)) {
                $results[] = $webapp_dir .$path;
            } else {
                $results[] = $module_dir ."/". str_replace('_', '/', $actionName) ."/". $path;
            }
        }

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
        if(preg_match('/^filename/', $key)) {
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
}
?>
