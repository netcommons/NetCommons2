<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * pmテーブル登録用クラス
 */
class Pm_Components_Filter_Display
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pm_Components_Filter_Display() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	function addFlag($action_id, $checked = false, $default = '')
	{
		return array(
			'action_id' => $action_id,
			'checked' => $checked,
			'options' => false,
			'default' => $default,
			'template' => 'pm_view_filter_flag.html',
		);
	}
	
	function addTag($action_id, $checked = false, $default = '')
	{
		$container =& DIContainerFactory::getContainer();
		$pmView =& $container->getComponent("pmView");
		$tags = &$pmView->getTags();
		
		$options = array();
		if(is_array($tags)){
			foreach($tags as $tag){
				$options[] = array('id' => $tag['tag_id'], 'title' => $tag['tag_name']);
			}
		}
		
		return array(
			'action_id' => $action_id,
			'checked' => $checked,
			'options' => $options,
			'default' => $default,
			'template' => 'pm_view_filter_tag.html',
		);
	}
	
	function forward($action_id, $checked = false, $default = '')
	{
		return array(
			'action_id' => $action_id,
			'checked' => $checked,
			'options' => false,
			'default' => $default,
			'template' => 'pm_view_filter_forward.html',
		);
	}
	
	function markRead($action_id, $checked = false, $default = '')
	{
		return array(
			'action_id' => $action_id,
			'checked' => $checked,
			'options' => false,
			'default' => $default,
			'template' => 'pm_view_filter_read.html',
		);
	}
	
	function remove($action_id, $checked = false, $default = '')
	{
		return array(
			'action_id' => $action_id,
			'checked' => $checked,
			'options' => false,
			'default' => $default,
			'template' => 'pm_view_filter_delete.html',
		);
	}
	
	function getDescription($key, $parameter = ''){
		$description = "";
		
		switch($key){
			case "PM_FILTER_ADD_TAG":
				$container =& DIContainerFactory::getContainer();
				$pmView =& $container->getComponent("pmView");
				$tag = &$pmView->getTag($parameter, false);
				$description = sprintf(constant($key), $tag["tag_name"]);
				break;
				
			case "PM_FILTER_FORWARD":
				$description = sprintf(constant($key), $parameter);
				break;
			
			case "PM_FILTER_MARK_READ":
			case "PM_FILTER_ADD_FLAG":
			case "PM_FILTER_REMOVE":
				$description = constant($key);
				break;
		}
		
		return $description;
	}
}
?>