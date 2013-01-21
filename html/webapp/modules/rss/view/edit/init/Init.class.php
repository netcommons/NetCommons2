<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RSS編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Rss_View_Edit_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
	
	// コンポーネントを受け取るため
	var $rssView = null;

	// 値をセットするため
	var $rss = null;
	var $cache_times = array();
	var $visible_rows = array();
	var $imagine = null;
	
    /**
     * RSS編集画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$this->rss = $this->rssView->getRss();
        if ($this->rss === false) {
        	return "error";
        }
		if (empty($this->rss)) {
			$container =& DIContainerFactory::getContainer();
			$configView =& $container->getComponent("configView");
			$configs = $configView->getConfig($this->module_id, false);
			$this->rss["url"] = $configs["url"]["conf_value"];
			$this->rss["encoding"] = $configs["encoding"]["conf_value"];
			$this->rss["cache_time"] = $configs["cache_time"]["conf_value"];
			$this->rss["visible_row"] = $configs["visible_row"]["conf_value"];
			$this->rss["imagine"] = constant($configs["imagine"]["conf_value"]);
		}

		$this->cache_times = $this->_getOptionsArray(_RSS_CACHE_TIME_VALUE, _RSS_CACHE_TIME);
		$this->visible_rows = $this->_getOptionsArray(_RSS_VISIBLE_ROW_VALUE, _RSS_VISIBLE_ROW);

		return "success";
    }

	/**
	 * optionタグ生成用の配列を取得する
	 *
	 * @param	array	$values		optionタグのvalue属性文字列（|区切り）
	 * @param	array	$options	optionタグの値文字列（|区切り）
     * @return array	optionタグ生成用の配列
	 * @access	public
	 */
	function _getOptionsArray($values, $options) 
	{
		$return = array();
		
		$values = explode("|", $values);
		if (isset($options)) {
			$options = explode("|", $options);
		} else {
			$options = $values;
		}
		
		foreach (array_keys($values) as $key) {
			$value = $values[$key];
			$return[$value] = $options[$key];
		}
		
		return $return;
	}
}
?>
