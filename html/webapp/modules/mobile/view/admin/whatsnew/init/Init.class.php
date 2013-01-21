<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯メニュー編集画面表示クラス 編集画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_View_Admin_Whatsnew_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $module_id = null;

	// 使用コンポーネントを受け取るため
	var $mobileView = null;
	var $modulesView = null;
	var $configView = null;
	var $session = null;

	// 主担でなくても編集できるようにする
	var $headerbtn_edit = false;

	// 値をセットするため
	var $config = null;
	var $modules = null;
	var $display_modules = null;
	var $useable_module_count = null;

	function execute()
	{
		$this->session->removeParameter(array('mobile', 'whatsnew_enroll_room'));
		$this->session->removeParameter(array('mobile', 'mobile_whatsnew_select_myroom'));

		$this->config = $this->configView->getConfig($this->module_id, false);
		if( $this->config == false ) {
			return 'error';
		}

		$this->modules = $this->mobileView->getModules(null,array(&$this, '_callbackModules'));

		$this->display_modules = array_flip(explode(',',$this->config[MOBILE_WHATSNEW_SELECT_MODULE]['conf_value']));

		return 'success';
	}
	/**
	 * モジュールのデータを取得
	 *
	 * @access	private
	 */
	function _callbackModules(&$recordSet)
	{
		$modules =& $this->modulesView->getModules(array('whatnew_flag'=>_ON,'system_flag'=>_OFF), array('display_sequence'=>'ASC'),null,null,array($this,'_callbackWhatsnewModules'));
		$result = array();
		$this->useable_module_count = 0;
		while ($row = $recordSet->fetchRow()) {
			if(isset($modules[$row['module_id']])) {
				$pathList = explode('_', $row['mobile_action_name']);
				$row['dir_name'] = $pathList[0];
				$row['module_name'] = $this->modulesView->loadModuleName($row['dir_name']);
				if($row['use_flag']==_ON) {
					$this->useable_module_count++;
				}
				$result[$row['module_id']] = $row;
			}
		}
		return $result;
	}
	/**
	 * 新着対象のモジュールのデータを取得
	 *
	 * @access	private
	 */
	function _callbackWhatsnewModules(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[$row['module_id']] = $row;
		}
		return $result;
	}
}
?>
