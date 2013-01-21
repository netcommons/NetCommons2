<?php
//
//
// $Id: Filter_ValidateDef.class.php,v 1.2 2006/09/29 06:16:27 Ryuji.M Exp $
//

require_once MAPLE_DIR.'/nccore/ValidatorManager.class.php';

/**
 * Validateの実行準備および実行を行うFilter
 *
 * @author	TAKAHASHI Kunihiko
 * @package	maple.filter
 * @since	3.0.0
 **/
class Filter_ValidateDef extends Filter {
	/**
	 * コンストラクター
	 *
	 * @access	public
	 * @since	3.0.0
	 */
	function Filter_ValidateDef() {
		parent::Filter();
	}

	/**
	 * Validate処理を実行
	 *
	 * @access	public
	 * @since	3.0.0
	 **/
	function execute() {
		$log =& LogFactory::getLog();
		$log->trace("Filter_ValidateDefの前処理が実行されました", "Filter_ValidateDef#execute");

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$errorList =& $actionChain->getCurErrorList();
		$type = $errorList->getType();

		//
		// 前のフィルターでエラーが発生してなくて、項目がある場合には実行
		//
		$attributes = $this->getAttributes();

		if (($type == "") &&
			is_array($attributes) && (count($attributes) > 0)) {
			$validatorManager =& new ValidatorManager();
			$validatorManager->execute($attributes);
		}

		$filterChain =& $container->getComponent("FilterChain");
		$filterChain->execute();

		$log->trace("Filter_ValidateDefの後処理が実行されました", "Filter_ValidateDef#execute");
	}
}
?>
