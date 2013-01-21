<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯管理のモジュールインストール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Install extends Action
{
    // リクエストパラメータを受け取るため
	var $module_id = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $modulesView = null;
	var $mobileAction = null;

    // 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
    		"module_id" => $this->module_id,
			"upload_id" => _OFF,
			"mobile_action_name" => "mobile",
			"use_flag" => _ON,
			"display_position" => _DISPLAY_POSITION_HEADER,
			"display_sequence" => 1
		);
    	$result = $this->db->insertExecute("mobile_modules", $params, true);
    	if ($result === false) {
    		return false;
    	}
    	return $this->modulesView->getModules(null, array("{modules}.module_id"=>"ASC"), null, null, array($this, "_fetchcallback"));
    }

    /**
     * _fetchcallback処理
     *
     * @access  private
     */
    function _fetchcallback(&$recordSet)
    {
		while ($row = $recordSet->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
    		$dirname = $pathList[0];
    		$install_ini = $this->modulesView->loadInfo($dirname);
    		if (!$install_ini) {
	       		return false;	
	        }
			if (!isset($install_ini['Mobile'])) { continue; }
			
			$mobile_params = array("module_id"=>$row["module_id"]);
			foreach ($install_ini['Mobile'] as $key=>$item) {
				if ($key == "header" && isset($install_ini['Mobile']["header"]) && $install_ini['Mobile']["header"] == _ON) {
					$mobile_params["display_position"] = _DISPLAY_POSITION_HEADER;
					continue;
				}
				$mobile_params[$key] = $item;
			}
			$result = $this->mobileAction->insertMobile($row["module_id"], $mobile_params);
			if (!$result) {
	   			return false;	
			}
		}
    	return true;
    }
}
?>