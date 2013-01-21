<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目追加(編集)-プレビュー
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Preview extends Action
{
	// リクエストパラメータを受け取るため
	var $item_id = null;
	var $item_name = null;
	var $type = null;

	var $require_flag = null;
	var $allow_public_flag = null;
	var $define_flag = null;
	var $allow_email_reception_flag = null;

	var $description = null;
	var $attribute = null;

	var $options = null;
	var $default_selected = null;
	
	// バリデートによりセット
	var $items = null;

	
	// 使用コンポーネントを受け取るため
	//var $db = null;
	//var $session = null;
	//var $usersAction = null;
	//var $usersView = null;
	
	// 値をセットするため
	var $item = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->define_flag == _ON) {
			if(defined($this->item_name)) $this->item_name = constant($this->item_name);
			if(defined($this->description)) $this->description = constant($this->description);
		}
		$this->item = array(
							"item_id" => $this->item_id,
							"item_name" => $this->item_name,
							"type" => $this->type,
							"require_flag" => $this->require_flag,
							"allow_public_flag" => $this->allow_public_flag,
							"define_flag" => $this->define_flag,
							"allow_email_reception_flag" => $this->allow_email_reception_flag,
							"description" => $this->description,
							"attribute" => $this->attribute
						);
		if($this->item_id != 0) {
			$this->item = array_merge($this->items, $this->item);
			//$this->item = array_merge($this->item, $this->items);
			//$this->item['system_flag'] = $this->items['system_flag'];
			//$this->item['tag_name'] = $this->items['tag_name'];
		} else {
			$this->item['system_flag'] = _OFF;
			$this->item['tag_name'] = "";
		}
		$this->set_options = null;
		if(($this->item['system_flag'] == _OFF) && ($this->type == USER_TYPE_CHECKBOX || $this->type == USER_TYPE_RADIO ||
			$this->type == USER_TYPE_SELECT)) {
			//選択式
			$count = 0;
			foreach($this->options as $key => $default_selected_option) {
				if($this->define_flag == _ON && defined($this->options[$key])) {
					$this->item["set_options"][$count]['options'] = constant($this->options[$key]);
				} else {
	    			$this->item["set_options"][$count]['options'] = $this->options[$key];
				}
	    		$default_selected = ($this->default_selected != null && isset($this->default_selected[$key]) && $this->default_selected[$key] == _ON) ? _ON : _OFF;
	    		$this->item["set_options"][$count]['default_selected'] = $default_selected;
	    		$count++;
	    	}
		}			
		return 'success';
	}
}
?>
