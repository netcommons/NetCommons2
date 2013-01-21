<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Imagine_Action_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $display = null;

	// 使用コンポーネントを受け取るため
	var $db = null;

	// validatorから受け取るため
    var $exists = null;

    /**
     * 表示方法変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"block_id" => $this->block_id,
			"display" => $this->display
		);
    	if ($this->exists == _ON) {
    		$result = $this->db->updateExecute("imagine_block", $params, "block_id", true);
		} else {
    		$result = $this->db->insertExecute("imagine_block", $params, true);
		}
	    if (!$result) {
	    	return "error";
	    }
	    
		return "success";
    }
}
?>