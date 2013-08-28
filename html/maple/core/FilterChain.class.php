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
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: FilterChain.class.php,v 1.5 2007/11/06 08:58:34 Ryuji.M Exp $
 */

require_once FILTER_DIR . '/Filter.interface.php';

/**
 * Filterを保持するクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class FilterChain
{
    /**
     * @var Filterを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_list;

    /**
     * @var Filterの位置を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_position;

    /**
     * @var 現在実行されているFilterの位置を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_index;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function FilterChain()
    {
        $this->_list     = array();
        $this->_position = array();
        $this->_index    = -1;
    }

    /**
     * FilterChainの最後にFilterを追加
     *
     * @param   string  $name   Filterのクラス名
     * @param   string  $alias  Filterのエイリアス名
     * @access  public
     * @since   3.0.0
     */
    function add($name, $alias = '')
    {
        $log =& LogFactory::getLog();
        
        $className = "Filter_" . ucfirst($name);
        $filename  = FILTER_DIR . "/${className}.class.php";
        if(!class_exists($className)) {
        	//既に存在していた場合、追加しないように修正(-0.001)
        	
	        //
	        // エイリアス名が指定されていない場合はクラス名をセット
	        //
	        if (empty($alias)) {
	            $alias = $name;
	        }
	
	        //
	        // Filterの実行が既に始まっていたらエラー(実行後の追加はエラー)
	        //
	        if ($this->_index > -1) {
	            $log->error("実行後にFilterが追加されています(${name}[alias:${alias}])", "FilterChain#add");
	            return false;
	        }
	
	        //
	        // Filterのクラス名が不正だったらエラー
	        //
	        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
	            $log->error("不正なFilterが指定されています(${name}[alias:${alias}])", "FilterChain#add");
	            return false;
	        }
	
	        //
	        // ファイルが存在していなければエラー
	        //
	        if (!(include_once $filename) or !class_exists($className)) {
	            $log->error("存在していないFilterが指定されています(${name}[alias:${alias}])", "FilterChain#add");
	            return false;
	        }
	        //
	        // 既に同名のFilterが追加されていたら何もしない
	        //
	        if (isset($this->_list[$alias]) && is_object($this->_list[$alias])) {
	            $log->info("このFilterは既に登録されています(${name}[alias:${alias}])", "FilterChain#add");
	            return true;
	        }
        }
        //
        // オブジェクトの生成に失敗していたらエラー
        //
        $filter =& new $className();

        if (!is_object($filter)) {
            $log->error("Filterの生成に失敗しました(${name}[alias:${alias}])", "FilterChain#add");
            return false;
        }

        $this->_list[$alias] =& $filter;
        $this->_position[]   =  $alias;

        return true;
    }

    /**
     * FilterChainをクリア
     *
     * @access  public
     * @since   3.0.0
     */
    function clear()
    {
        $this->_list     = array();
        $this->_position = array();
        $this->_index    = -1;
    }

    /**
     * FilterChainの長さを返却
     *
     * @return  integer FilterChainの長さ
     * @access  public
     * @since   3.0.0
     */
    function getSize()
    {
        return count($this->_list);
    }

    /**
     * 現在のFilter名を返却
     *
     * @return  string  Filterの名前
     * @access  public
     * @since   3.0.0
     */
    function getCurFilterName()
    {
        if (isset($this->_position[$this->_index])) {
            return $this->_position[$this->_index];
        }
    }

    /**
     * 指定された名前のFilterを返却
     *
     * @return  Object  Filterのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function &getFilterByName($name)
    {
        $log =& LogFactory::getLog();
        $result = false;

        if ($name == "") {
            $log->warn("引数が不正です", "FilterChain#getFilterByName");
            return $result;
        }

        if (!isset($this->_list[$name]) || !is_object($this->_list[$name])) {
            $log->error("指定されたFilterは登録されていません(${name})", "FilterChain#getFilterByName");
            return $result;
        }

        $filter =& $this->_list[$name];

        if (!is_object($filter)) {
            $log->error("Filterの取得に失敗しました(${name})", "FilterChain#getFilterByname");
            return $result;
        }

        return $filter;
    }

    /**
     * 指定したFilterに属性をセット
     *
     * @param   string  $name   Filterの名前
     * @param   array   $attributes セットする属性(配列)
     * @access  public
     * @since   3.0.0
     */
    function setAttributes($name, $attributes)
    {
        $filter =& $this->getFilterByname($name);

        if (!is_object($filter)) {
        	$log =& LogFactory::getLog();
            $log->error("Filterの取得に失敗しました(${name})", "FilterChain#setAttributes");
            return false;
        }

        return $filter->setAttributes($attributes);
    }

    /**
     * FilterChainを組み立てる
     *
     * @param   Object  $config ConfigUtilsのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function build(&$config)
    {
        $log =& LogFactory::getLog();
        foreach ($config->getConfig() as $section => $value) {
            $sections = explode(':', $section);
            $filterName = $sections[0]; // フィルタ名
            if (isset($sections[1]) && $sections[1]) { // 発動するREQUEST_METHOD
                $method = strtoupper($sections[1]);
            } else {
                $method = 'BOTH';
            }
            if (isset($sections[2]) && $sections[2]) { // エイリアス名
                $alias = $sections[2];
            } else {
                $alias = $filterName;
            }

            if (($method == 'BOTH') ||
                ($method == $_SERVER['REQUEST_METHOD'])) {
                $filterConfig =& $config->getSectionConfig($section);
                if (!$this->add($filterName, $alias)) {
                    $log->error("FilterChainへの追加に失敗しました(${section})", "FilterChain#build");
                    return false;
                }
                if (is_array($filterConfig) && (count($filterConfig) > 0)) {
                    $this->setAttributes($alias, $filterConfig);
                }
            }
        }
        return true;
    }

    /**
     * FilterChainの中の次のFilterを実行
     *
     * このメソッドはクラスメソッド
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();

        if ($this->getSize() < 1) {
            $log->error("Filterが追加されていません", "FilterChain#execute");
            return false;
        }
        if ($this->_index < ($this->getSize() - 1)) {
            $this->_index++;

            $name = $this->getCurFilterName();
            $filter =& $this->getFilterByname($name);

            if (!is_object($filter)) {
                $log->error("Filterの取得に失敗しました(${name})", "FilterChain#execute");
                return false;
            }
            return $filter->execute();
        }
        return true;
    }
}
?>