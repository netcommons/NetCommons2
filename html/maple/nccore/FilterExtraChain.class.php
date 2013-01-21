<?php
//
// $Id: FilterExtraChain.class.php,v 1.4 2008/06/02 09:05:59 Ryuji.M Exp $
//

require_once MAPLE_DIR.'/core/FilterChain.class.php';

/**
 * Filterを保持するクラス
 * hasFilterByName追加
 * 
 *
 * @author	Ryuji Masukawa
 **/
class FilterExtraChain extends FilterChain 
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function FilterExtraChain()
    {
        $this->FilterChain();
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
            
            // 指定したアクションではフィルタを実行しないようにする 
            // 参考：http://d.hatena.ne.jp/bobchin/20060226
            // 追加 ///////////////////////////////////////////////////////
            if (isset($value['_excludes'])) {
            	$excludes = explode(",", $value['_excludes']);
            	$container =& DIContainerFactory::getContainer();
                $actionChain =& $container->getComponent("ActionChain");
                $curAction = $actionChain->getCurActionName();
                $exclude_continu_flag = false;
            	foreach($excludes as $exclude) {
            		if(preg_match("/".strtolower($exclude)."_"."/", strtolower($curAction)."_") != 0) {
            			$exclude_continu_flag = true;
            			break;
            		}
            	}
            	if($exclude_continu_flag) {
            		continue;	
            	}
            	//$excludes = preg_split('/\s*,\s*/', $value['_excludes'], -1, PREG_SPLIT_NO_EMPTY);
                //$container =& DIContainerFactory::getContainer();
                //$actionChain =& $container->getComponent("ActionChain");
                //$curAction = $actionChain->getCurActionName();
                //if (in_array($curAction, $excludes)) {
                //    continue;
                //}
                $c =& $config->getConfig();
                unset($c[$section]['_excludes']);
            }
            // 追加 ///////////////////////////////////////////////////////

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
     * 指定された名前のFilterが登録されているかどうかチェック
     *
     * @return  boolean 登録されているかどうか
     */
    function hasFilterByName($name)
    {
        $log =& LogFactory::getLog();

        if ($name == "") {
            $log->warn("引数が不正です", "FilterExtraChain#getFilterByName");
            return false;
        }

        if (!isset($this->_list[$name]) || !is_object($this->_list[$name])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * FilterChainをコピー
     * @return array
     * @access  public
     * @since   3.0.0
     */
    function copy()
    {
    	return array($this->_list,$this->_position,$this->_index);
    }
    /**
     * FilterChainのコピーしたものを元に戻す
     * @return array
     * @access  public
     * @since   3.0.0
     */
    function paste($list_arr)
    {
    	$this->_list     = $list_arr[0];
        $this->_position = $list_arr[1];
        $this->_index    = $list_arr[2];	
    }
}
?>
