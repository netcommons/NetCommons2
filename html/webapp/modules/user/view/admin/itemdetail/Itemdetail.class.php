<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定>項目追加(項目編集)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Itemdetail extends Action
{
    // リクエストパラメータを受け取るため
    var $item_id = null;

    // 使用コンポーネントを受け取るため
    var $session = null;
	var $usersView = null;

    
    // バリデートによりセット
    var $items = null;
    
    // 値をセットするため
    var $filepath = null;
    var $items_def = null;
    var $options = null;
    var $options_len = null;
    var $isDisable = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	//
    	// PHP定義名称説明用ファイル取得
    	//
    	$lang = $this->session->getParameter("_lang");
    	$filename = WEBAPP_DIR."/language/".$lang."/items.ini";
    	$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		$contents = preg_replace("/\[Define\]\s*/i", "", $contents);
    	$contents = preg_replace("/\n/i", "<br />", $contents);
    	$this->items_def = $contents;
    	
    	$this->filepath = "webapp/language/XXXX/items.ini";					//固定値
    	
    	if($this->item_id == 0) $this->items = null;						//初期化
    	
		//
		// リスト値
		//
    	$this->options = array();
    	$this->options_len = 0;
    	if($this->item_id == 0 || 
    		($this->items['type'] != USER_TYPE_CHECKBOX &&
    		$this->items['type'] != USER_TYPE_RADIO &&
    		$this->items['type'] != USER_TYPE_SELECT)) {
    		// 新規登録	or 選択式でなければ
    		//選択式を選んだ場合のデータを作成しておく
    		$default_selected_options = explode("|", USER_DEFAULT_SELECTED_OPTIONS);
    		$count = 0;
    		foreach($default_selected_options as $default_selected_option) {
    			$this->options[$count]['options'] = USER_DEFAULT_OPTIONS.($count+1);
    			$this->options[$count]['default_selected'] = intval($default_selected_option);
    			$count++;
    		}
    		$this->options_len = $count;
    	} else {
    		$options = explode("|", $this->items['options']);
    		$default_selected_options = explode("|", $this->items['default_selected']);
    		$count = 0;
    		$total_len = count($default_selected_options);
    		foreach($default_selected_options as $default_selected_option) {
    			if($options[$count] == "" && $total_len - 1 == $count) continue;		//最後の「|」の後の空文字列
    			$this->options[$count]['options'] = $options[$count];
    			$this->options[$count]['default_selected'] = intval($default_selected_option);
    			$count++;
    		}
    		$this->options_len = $count;
    	}
    	if($this->items != null) {
    		$this->items['def_item_name'] = defined($this->items['item_name']) ? constant($this->items['item_name']) : $this->items['item_name'];
    	}

		$this->isDisable = $this->usersView->isUsersTableField($this->items['tag_name']);

    	return 'success';
    }
}
?>
