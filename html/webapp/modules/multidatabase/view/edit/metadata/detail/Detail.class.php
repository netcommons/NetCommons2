<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メタデータ設定(メタデータ編集)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Edit_Metadata_detail extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $metadata_id = null;
    var $multidatabase_id = null;

    // 使用コンポーネントを受け取るため
    
    // バリデートによりセット
    var $mdb_obj = null;
    var $metadata = null;
    
    // 値をセットするため
    var $options = null;
    var $options_len = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	if($this->metadata_id == 0) {
    		$this->metadata = null;						//初期化
    	}
    	$this->options = array();
    	$this->options_len = 0;
    	if($this->metadata_id == 0 
    		|| ($this->metadata['type'] != MULTIDATABASE_META_TYPE_SECTION 
    			&& $this->metadata['type'] != MULTIDATABASE_META_TYPE_MULTIPLE)) {
    		// 新規登録	or 選択式でなければ
    		//選択式を選んだ場合のデータを作成しておく
    		for($i=0;$i<MULTIDATABASE_DEFAULT_SELECTED_NUM;$i++) {
    			$this->options[$i] = MULTIDATABASE_DEFAULT_OPTIONS.($i+1);
    		}
    		$this->options_len = $i;

    	} else {
    		$this->options = explode("|", $this->metadata['select_content']);
    		$this->options_len = count($this->options);
    	}
    	return 'success';
    }
}
?>
