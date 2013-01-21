<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システム管理>>入退会設定画面表示
 * 		入退会設定項目を表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_View_Main_Membership extends Action
{
	// リクエストパラメータを受け取るため
	var $languages = null;

    // 使用コンポーネントを受け取るため
    var $configView = null;
    var $systemView = null;
    var $authoritiesView = null;

    // フィルタによりセット
    
    // 値をセットするため
    var $config = null;
    var $membership_items = null;
    var $item_ids = array();
    var $must_items = null;
    var $use_items = null;
    var $authorities = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
        $this->config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _ENTER_EXIT_CONF_CATID);
        if ($this->config === false) {
            return 'error';
        }
        $where_params = array(
        	"user_authority_id != "._AUTH_ADMIN => null
        );
        $order_params = array(
        	"user_authority_id" => "DESC",
        	"system_flag" => "DESC",
        	"role_authority_id" => "ASC"
        );
		$this->authorities = $this->authoritiesView->getAuthorities($where_params, $order_params);
		if ($this->authorities === false) {
        	return 'error';
        }
		
        // 1:1|2:1|3:1|4:0| -> {1 => 1, 2 => 1, 3 => 1, 4 => 0}       
        $this->use_items = $this->systemView->parseUseItems($this->config["autoregist_use_items"]["conf_value"]);
        $this->default_use_items = $this->systemView->parseUseItems(SYSTEM_DEFAULT_AUTOREGIST_USE_ITEMS);

        $this->membership_items =& $this->systemView->getItems(null, null, null, null, array($this,"_getItemsAndSetIdsFetchcallback"));
        if ($this->membership_items === false) {
        	return 'error';
        }
        
    	foreach($this->membership_items as $item) {	
			if (isset($item["tag_name"])) {
				$this->item_ids[$item["tag_name"]] = $item["item_id"];
			}
    	}

        return 'success';
    }

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_getItemsAndSetIdsFetchcallback($result) {
		$items = array();
		for ($i = 0; $row = $result->fetchRow(); $i++) {
			//　自動登録に必要のないアイテムはスキップ
			if ($row["type"] == "system" or $row["type"] == "label") continue;
			if (!isset($row["col_num"]) || !isset($row["row_num"]) || ($row["col_num"] == 0 && $row["row_num"] == 0) ) continue;
			// if ($row["define_flag"] == _OFF) continue;
			if ($row["tag_name"] == "role_authority_name" || $row["tag_name"] == "active_flag_lang") continue;
			
			// 定数はローカル言語に変換
			if (defined($row["item_name"])) {
				$row["item_name"] = constant($row["item_name"]);
			}
			
			// 画面表示の設定
			// must:デフォルト設定, checked:任意, selectable:ユーザによる選択可能, hide:最初に表示しない
			$item_id = $row["item_id"];
			if ($this->use_items && isset($this->use_items[$item_id])) {
				$use_item_value = $this->use_items[$item_id];
				$default_use_item_value = _OFF;
				if ($this->default_use_items && isset($this->default_use_items[$item_id])) {
					$default_use_item_value = $this->default_use_items[$item_id];
				}
				if ($default_use_item_value == _ON) {
					$row["display_attribute"] = SYSTEM_AUTOREGIST_DEFAULT_MUST_ITEM;
				} elseif ($use_item_value == _ON) {
				 	$row["display_attribute"] = SYSTEM_AUTOREGIST_CHECKED_ITEM;
				} else {
					$row["display_attribute"] = SYSTEM_AUTOREGIST_SELECTABLE_ITEM;
				}
			} else {
				$row["display_attribute"] = SYSTEM_AUTOREGIST_HIDE_ITEM;
			}

			$items[$i] = $row;
		}
		
		// sort by col_num(high priority) and row_num
		usort($items, create_function('$a,$b', 
			'return ( ($a["col_num"]==$b["col_num"]) ? ($a["row_num"]-$b["row_num"]) : ($a["col_num"]-$b["col_num"]) );'
		));
		return $items;
	}
}
?>
