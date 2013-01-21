<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アカウント自動登録時のメールアドレスの必須チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_Validator_Mailitem extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値(autoregist_use_items)     
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// container取得
		$container =& DIContainerFactory::getContainer();
		$usersView =& $container->getComponent("usersView");
		
    	$use_items = $attributes;

    	if (!isset($use_items)) {
    		return _INVALID_INPUT;
    	}

    	// eメールと携帯メール
    	$where = array(
    		"({items}.type = 'email' OR {items}.type = 'mobile_email')" => null
    	);
    	$items =& $usersView->getItems($where, null, null, null, array($this, "_getItemsFetchcallback"));
    	
    	foreach($use_items as $use_item) {
    		// 不正入力チェック
    		if (!$use_item) continue;
    		$list = explode(":", $use_item);
    		if (count($list) != 2) continue;
    		// eメールか携帯メールが必須(value = _ON)になっているか？
    		list($id, $value) = $list;
    		if (array_key_exists($id, $items)) {
    			if ($value == SYSTEM_AUTOREGIST_DEFAULT_MUST_ITEM || $value == SYSTEM_AUTOREGIST_CHECKED_ITEM) {
    				return;
    			}
    		}
    	}
        return $errStr;
    }

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			// item_idをkeyに連想配列を作成(valueはダミー)
			$ret[$row['item_id']] = _ON;
		}
		return $ret;
	}
}
?>
