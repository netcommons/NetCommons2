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
class Photoalbum_Action_Main_Album_Chgseq extends Action
{
    // リクエストパラメータを受け取るため
    var $album_id = null;
    var $drop_album_id = null;
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
			"album_id"=>intval($this->album_id)
		);
    	$album = $this->db->selectExecute("photoalbum_album",$params);
        if ($album === false || !isset($album[0])) {
        	return 'error';
        }

        $params = array(
			"album_id"=>intval($this->drop_album_id)
		);
    	$drop_album = $this->db->selectExecute("photoalbum_album",$params);
        if ($drop_album === false || !isset($drop_album[0])) {
        	return 'error';
        }

        $album_id = $album[0]['album_id'];
        
        //移動元デクリメント
        //前詰め処理
    	$params = array(
			"album_id"=>intval($album_id)
		);
		$sequence_param = array(
			"display_sequence"=> $album[0]['display_sequence']
		);
    	$result = $this->db->seqExecute("photoalbum_album", $params, $sequence_param);
    	if($result === false) {
    		return 'error';
    	}
    	
    	if($album[0]['display_sequence'] > $drop_album[0]['display_sequence']) {
	        if($this->position == "top") {
	        	$drop_display_sequence = $drop_album[0]['display_sequence'];
	        } else {
	        	$drop_display_sequence = $drop_album[0]['display_sequence'] + 1;
	        }
	    } else {
	    	if($this->position == "top") {
	        	$drop_display_sequence = $drop_album[0]['display_sequence'] - 1;
	        } else {
	        	$drop_display_sequence = $drop_album[0]['display_sequence'];
	        }
	    }

	    //移動先インクリメント
	    $params = array(
			"album_id"=>intval($album_id)
		);
		$sequence_param = array(
			"display_sequence"=> $drop_display_sequence
		);
    	$result = $this->db->seqExecute("photoalbum_album", $params, $sequence_param, 1);
    	if($result === false) {
    		return 'error';
    	}
    	
    	//更新
    	$params = array(
    		"album_id"=>intval($album_id),
			"display_sequence" => $drop_display_sequence
		);
		$where_params = array(
			"album_id" => intval($this->album_id)
		);
    	$result = $this->db->updateExecute("photoalbum_album", $params, $where_params, true);
    	if($result === false) {
    		return 'error';
    	}
    	
    	return 'success';
    }
}
?>
