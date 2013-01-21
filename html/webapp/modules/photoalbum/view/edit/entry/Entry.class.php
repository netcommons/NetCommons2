<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_View_Edit_Entry extends Action
{
	// 使用コンポーネントを受け取るため
    var $photoalbumView = null;
    
    // validatorから受け取るため
    var $photoalbum = null;

	// 値をセットするため
    var $photoalbumNumber = null;

    /**
     * フォトアルバム入力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->photoalbum["photoalbum_id"])) {
			return "success";
		}

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->photoalbumNumber = $this->photoalbumView->getPhotoalbumCount();
		if ($this->photoalbumNumber === false) {
        	return "error";
        }
        $this->photoalbumNumber++;
        
		return "success";
    }
}
?>