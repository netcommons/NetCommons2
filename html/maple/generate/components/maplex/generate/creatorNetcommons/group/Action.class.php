<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Action関連ファイルを書き出す
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/Abstract.class.php');

class Maplex_Generate_CreatorNetcommons_Group_Action
 extends Maplex_Generate_CreatorNetcommons_Abstract
{
    var $actionCreator;
    var $iniCreator;
    
    /**
     * action generatorのロジックを実行する
     *
     * @param  object  $dto    DTOクラスのインスタンス
     * @access  public
     */
    function create(&$dto)
    {
        $fileList = array();
        
		//
		// action maple.ini
		//
		$fileList += $this->actionCreator->create($dto);
		
		if($dto->templateType != "simple") {
			$dto->view_actionName = $dto->actionName;
			$dto->skeletonTemplateName = "maple.ini";
			$fileList += $this->iniCreator->create($dto);
		}
		return $fileList;
    }
}
?>
