<?php
//
// $Id: Filter_HeaderMenu.class.php,v 1.12 2008/01/23 02:56:23 Ryuji.M Exp $
//

 /**
 * モジュールヘッダーメニューの登録(ブロック上部にタブを表示させる)
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_HeaderMenu extends Filter {
	/**
	 * @var モジュールヘッダーメニューの登録
	 *
	 * @access	private
	 **/
	var $_menu;
	var $_container;
	
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_HeaderMenu() {
		parent::Filter();
	}

	/**
	 * モジュールヘッダーメニューの登録(ブロック上部にタブを表示させる)
	 *
	 * @access	public
	 **/
	function execute() {
		$this->_container =& DIContainerFactory::getContainer();
		$common =& $this->_container->getComponent("commonMain");
		$actionChain =& $this->_container->getComponent("ActionChain");
		$action_name = $actionChain->getCurActionName();
		$pathList = explode("_", $action_name);
		$edit_action_name = "";
		if(isset($pathList[0])) {
			$getdata =& $this->_container->getComponent("GetData");
			$modules =& $getdata->getParameter("modules");
			if (preg_match("/edit_init$/i", $modules[$pathList[0]]['edit_action_name'])) {
				$edit_action_name = $modules[$pathList[0]]['edit_action_name'];
			}
		}
		
		$headermenuArray["menu"] = array();
		$headermenuNumber = array();
		
		$attributes = $this->getAttributes();
		$headermenuArray["active"] = 1;
		$muneNumber = 1;
		$activeNumber = 0;
		if(!isset($attributes['mode']) || $attributes['mode'] != "nobuild") {
			foreach ($attributes as $key => $value) {
				if($key == "mode") continue;
				
				$key_array = explode(",", $key);
				if(count($key_array) == 1) {
					$active_colum = _OFF;
					$menuText = $key;
				} else {
					$active_colum = _ON;
					$menuText = $key_array[1];
				}
				//$muneNumber++;
				//権限により表示・非表示を切り替える等で使用		
				$valueList = explode("->", $value);
				$menuArray = array();
				if (count($valueList) == 1) {
					$menuArray["text"] = $menuText;
					$menuArray["click"] = (!empty($value)) ? $this->_getValue($value) : "";
					if ($edit_action_name != "" && preg_match("/'".$edit_action_name."'/i", $menuArray["click"])) {
						//アクション名称「XXX_XXX_edit_init」がedit_action_nameならば、
						//ショートカットブロックならば表示しない
						if (!$common->isResultByOperatorString("_shortcut_flag==_OFF")) {
							$menuArray = array();
						}
					}
					//$menuArray["click"] = str_replace("{\$block_id}", $block_id, $value);
				} else {
					// 権限チェック等
					if ($common->isResultByOperatorString($valueList[0])) {
						$menuArray["text"] = $menuText;
						$menuArray["click"] = (!empty($valueList[1])) ? $this->_getValue($valueList[1]) : "";
						//$menuArray["click"] = "parent.". str_replace(array("{\$block_id}", "{\$id}"), array($block_id, $id), $valueList[1]);
					}
				}
				if($active_colum) $activeNumber = $muneNumber;
				if(isset($menuArray["text"])) {
					if(isset($headermenuNumber[$menuText])) {
						if($active_colum) {
							$headermenuArray["active"] = $headermenuNumber[$menuText]; 
						}
						$headermenuArray["menu"][$headermenuNumber[$menuText]] = $menuArray;
					} else {
						if($active_colum) {
							$headermenuArray["active"] = $muneNumber; 
						}
						$headermenuArray["menu"][$muneNumber] = $menuArray;
						//number保存用
						$headermenuNumber[$menuText] = $muneNumber;
					}
				}
				if(isset($menuArray["text"])) {
					$muneNumber++;	
				}
				//$tempArray["menu"][$muneNumber] = $menuArray;
				//ksort($tempArray["menu"]);
			}
			
			$this->_menu = $headermenuArray;
		}	
		$log =& LogFactory::getLog();
		$log->trace("Filter_HeaderMenuの前処理が実行されました", "Filter_HeaderMenu#execute");
		
		//
		// 後処理
		//
		
		$filterChain =& $this->_container->getComponent("FilterChain");
		$filterChain->execute();

		$log->trace("Filter_HeaderMenuの後処理が実行されました", "Filter_HeaderMenu#execute");
	}
	
	/**
	 * Activeボタン設定
	 * 
 	 * @param int 1～	（Activeにするボタンの位置）
	 * @access	public
	 **/
    function setActive($active_num)
	{
		$this->_menu["active"] = $active_num;
	}
	
	/**
	 * ヘッダーメニュー配列の取得
	 * 
 	 * @return	array	変換したメニュー配列
	 * @access	public
	 **/
    function getMenu()
	{
		return $this->_menu;
	}
	
	/**
	 * ヘッダーメニュー配列のクリア
	 * 
 	 * @return	array	変換したメニュー配列
	 * @access	public
	 **/
    function clear()
	{
		unset($this->_menu);
		$this->_menu = null;
	}
	
	/**
	 * ヘッダーメニュー削除
	 * @param int menu_num(1からはじまる順番)
 	 * @return	boolean true or false
	 * @access	public
	 **/
    function removeNum($menu_num)
	{
		if(!isset($this->_menu['menu'][$menu_num])) {
			return false;
		}
		unset($this->_menu['menu'][$menu_num]);
		//アクティブなタブであれば
		if($this->_menu['active'] == $menu_num) $this->_menu['active'] = 1; //一番左固定
	}
	
	/**
	 * ヘッダーメニュー削除
	 * @param string $this->_menu['menu']['text']をキーに削除
 	 * @return	boolean true or false
	 * @access	public
	 **/
    function removeText($remove_text)
	{
		//$count = 1;
		foreach($this->_menu['menu'] as $key => $menu) {
			if($menu['text'] == $remove_text) {
				$this->removeNum($key);
				break;
			}
			//$count++;
		}
	}
	
	/**
	 * メソッド文字列取得
	 * 
 	 * @return	array	メソッド文字列
	 * @access	private
	 **/
	function _getValue($value) {
		$value_len = 7;
		if(substr($value, 0, $value_len) == "define:") {
			$value = substr($value, $value_len, strlen($value) - $value_len);
			if($value == "auto") {
				$value = "commonCls.sendView('<{\$id}>',{'action':'<{\$action_name}>'});";	
			}else if (defined($value)) {
				$value = constant($value);
			} else {
				$value = "commonCls.sendView('<{\$id}>',{'action':'".$value."'});";
			}
		}
		return $value;
	}
}
?>