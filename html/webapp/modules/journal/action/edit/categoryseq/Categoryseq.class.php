<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ編集アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Categoryseq extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $category_id = null;
    var $drop_category_id = null;
    var $position = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * リスト編集アクション
     *
     * @access  public
     */
    function execute()
    {
    	//データ取得
    	$params = array(
			"category_id"=>intval($this->category_id)
		);
    	$cat = $this->db->selectExecute("journal_category",$params);
        if ($cat === false || !isset($cat[0])) {
        	return 'error';
        }

        $params = array(
			"category_id"=>intval($this->drop_category_id)
		);
    	$drop_cat = $this->db->selectExecute("journal_category",$params);
        if ($drop_cat === false || !isset($drop_cat[0])) {
        	return 'error';
        }

        $journal_id = $cat[0]['journal_id'];
        
        //移動元デクリメント
        //前詰め処理
    	$params = array(
			"journal_id"=>intval($journal_id)
		);
		$sequence_param = array(
			"display_sequence"=> $cat[0]['display_sequence']
		);
    	$result = $this->db->seqExecute("journal_category", $params, $sequence_param);
    	if($result === false) {
    		return 'error';
    	}
    	
    	if($cat[0]['display_sequence'] > $drop_cat[0]['display_sequence']) {
	        if($this->position == "top") {
	        	$drop_display_sequence = $drop_cat[0]['display_sequence'];
	        } else {
	        	$drop_display_sequence = $drop_cat[0]['display_sequence'] + 1;
	        }
	    } else {
	    	if($this->position == "top") {
	        	$drop_display_sequence = $drop_cat[0]['display_sequence'] - 1;
	        } else {
	        	$drop_display_sequence = $drop_cat[0]['display_sequence'];
	        }
	    }

	    //移動先インクリメント
	    $params = array(
			"journal_id"=>intval($journal_id)
		);
		$sequence_param = array(
			"display_sequence"=> $drop_display_sequence
		);
    	$result = $this->db->seqExecute("journal_category", $params, $sequence_param, 1);
    	if($result === false) {
    		return 'error';
    	}
    	
    	//更新
    	$params = array(
    		"journal_id"=>intval($journal_id),
			"display_sequence" => $drop_display_sequence
		);
		$where_params = array(
			"category_id" => intval($this->category_id)
		);
    	$result = $this->db->updateExecute("journal_category", $params, $where_params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
}
?>
