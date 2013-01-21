<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サイト閉鎖画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_View_Closesite extends Action
{
	// リクエストパラメータを受け取るため
	
	// 使用コンポーネントを受け取るため
	var $configView = null;
	var $session = null;
	var $pagesCompmain = null;
	var $getData = null;

	// 値をセットするため
	var $closesite_message = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config == false) return 'error';
		
		$this->session->setParameter("_page_title",PAGES_CLOSESITE_TITLE);
		
		//$renderer =& SmartyTemplate::getInstance();
		//$renderer->assign('header_field',$this->configView->getMetaHeader());
		
		if($config['closesite']['conf_value'] == _OFF) {
			// 既に閉鎖中ではない
			$this->redirect_url = BASE_URL . INDEX_FILE_NAME;
			$this->redirect_message = PAGES_NOT_CLOSESITE;
			return 'error_redirect';
		}
		
		$this->closesite_message = $config['closesite_text']['conf_value'];

		$this->getData->setParameter('script_str', '');

		$isMobile = $this->session->getParameter('_mobile_flag');
		if (empty($isMobile)) {
			$this->pagesCompmain->setLoginHtml();
		}

		return 'success';
	}
}
?>