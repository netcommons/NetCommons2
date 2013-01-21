<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 画像アップロードクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action_Admin_Upload extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;

	// 使用コンポーネントを受け取るため
	var $uploadsAction = null;
	var $db = null;
	
	//値をセットするため

	/**
     * アップロードメイン表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	$filelist = $this->uploadsAction->uploads();
		$result = $this->db->updateExecute("mobile_modules", array("upload_id"=>$filelist[0]["upload_id"]), array("module_id" => $this->module_id));
    	return true;
    }
}
?>
