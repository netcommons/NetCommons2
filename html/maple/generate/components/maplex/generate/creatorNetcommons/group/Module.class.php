<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * module関連ファイルを書き出す
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/Abstract.class.php');

class Maplex_Generate_CreatorNetcommons_Group_Module
 extends Maplex_Generate_CreatorNetcommons_Abstract
{
	var $installiniCreator;
	var $languageiniCreator;
	var $cssCreator;
	var $jsCreator;
	var $imagesCreator;
	var $templateCreator;
	var $configCreator;
    var $actionCreator;
    var $iniCreator;
    var $componentCreator;
    var $sqlCreator;
    
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
        //install.ini作成
        //
        $fileList += $this->installiniCreator->create($dto);
        
        //
        //language
        //
		$fileList += $this->languageiniCreator->create($dto);
		
		//
		//files
		//
		//$dto->csstype = "global";
		//$fileList += $this->cssCreator->create($dto);
		$dto->csstype = "module";
		$fileList += $this->cssCreator->create($dto);
		
		$fileList += $this->jsCreator->create($dto);
		$fileList += $this->imagesCreator->create($dto);
		
		//
		// templates
		//
		//action-view-main
		$dto->templateName = $dto->moduleName . "_view_main_init.html";
		$dto->skeletonTemplateName = "template";
		$fileList += $this->templateCreator->create($dto);
		
		$dto->templateName = $dto->moduleName ."_script.html";
		$dto->skeletonTemplateName = "template_script";
		$fileList += $this->templateCreator->create($dto);
		
		//action-view-edit action-view-admin
		if($dto->moduleType != "simple") {
			$dto->templateName = $dto->moduleName ."_view_edit_init.html";
			$dto->skeletonTemplateName = "template";
			$fileList += $this->templateCreator->create($dto);
			
			$dto->templateName = $dto->moduleName ."_view_edit_style.html";
			$dto->skeletonTemplateName = "template";
			$fileList += $this->templateCreator->create($dto);
		}
		//if($dto->moduleType == "full") {
		//	$dto->templateName = $dto->moduleName ."_admin.html";
		//	$dto->skeletonTemplateName = "template";
		//	$fileList += $this->templateCreator->create($dto);
		//	
		//	$dto->templateName = $dto->moduleName ."_create.html";
		//	$dto->skeletonTemplateName = "template";
		//	$fileList += $this->templateCreator->create($dto);
		//	
		//	$dto->templateName = $dto->moduleName ."_update.html";
		//	$dto->skeletonTemplateName = "template";
		//	$fileList += $this->templateCreator->create($dto);
		//}
		
		//
		// config
		//
		if($dto->moduleType == "full") {
			$dto->configName = "main.ini";
			$fileList += $this->configCreator->create($dto);
		}
		
		//
		// action maple.ini
		//
		$dto->Type = "main";
		$dto->actionName = $dto->moduleName . "_view_main_init";
		$fileList += $this->actionCreator->create($dto);
		
		$dto->view_actionName = $dto->moduleName . "_view_main_init";
		$dto->skeletonTemplateName = "maple.ini";
		$fileList += $this->iniCreator->create($dto);
		
		$dto->actionName = $dto->moduleName . "_" . "view";
		$dto->skeletonTemplateName = "maple.ini_global";
		$fileList += $this->iniCreator->create($dto);
		
		//modinfo
		$dto->actionName = $dto->moduleName . "_" . "language" . "_" . $dto->langDir;
		$dto->skeletonTemplateName = "modinfo.ini";
		$dto->inifileName = "modinfo.ini";
		$fileList += $this->iniCreator->create($dto);
		
		if($dto->moduleType != "simple") {
			//dicon
			$dto->actionName = $dto->moduleName . "_" . "view";
			$dto->skeletonTemplateName = "dicon.ini";
			$dto->inifileName = "dicon.ini";
			$fileList += $this->iniCreator->create($dto);
		
			$dto->inifileName = null;
			//
			// Action
			//
			
			//edit ini
			$dto->Type = "edit";
			$dto->actionName = $dto->moduleName . "_view_edit";
			$dto->skeletonTemplateName = "maple.ini_edit";
			$fileList += $this->iniCreator->create($dto);
			
			$dto->Type = "edit";
			$dto->actionName = $dto->moduleName . "_view_edit_init";
			$dto->view_actionName = $dto->moduleName . "_view_edit_init";
			$fileList += $this->actionCreator->create($dto);
			$dto->skeletonTemplateName = "maple.ini";
			$fileList += $this->iniCreator->create($dto);
			
			//$dto->actionName = $dto->moduleName . "_view_edit";
			//$dto->skeletonTemplateName = "maple.ini";
			//$fileList += $this->iniCreator->create($dto);
			
			$dto->Type = "main";
			$dto->actionName = $dto->moduleName . "_action_main_init";
			$dto->view_actionName = $dto->moduleName . "_view_main_init";
			$fileList += $this->actionCreator->create($dto);
			$dto->skeletonTemplateName = "maple.ini";
			$fileList += $this->iniCreator->create($dto);
			
			$dto->Type = "edit";
			$dto->actionName = $dto->moduleName . "_action_edit_init";
			$dto->view_actionName = $dto->moduleName . "_view_edit_init";
			$fileList += $this->actionCreator->create($dto);
			$dto->skeletonTemplateName = "maple.ini";
			$fileList += $this->iniCreator->create($dto);
			
			//$dto->actionName = $dto->moduleName . "_action_edit";
			//$dto->skeletonTemplateName = "maple.ini";
			//$fileList += $this->iniCreator->create($dto);
			
			$dto->actionName = $dto->moduleName . "_" . "action";
			$dto->skeletonTemplateName = "maple.ini_global";
			$fileList += $this->iniCreator->create($dto);
			
			//
			//style
			//
			$dto->Type = "style";
			
			$dto->actionName = $dto->moduleName . "_view_edit_style";
			$dto->view_actionName = $dto->moduleName . "_view_edit_style";
			$fileList += $this->actionCreator->create($dto);
			$dto->skeletonTemplateName = "maple.ini";
			$fileList += $this->iniCreator->create($dto);
			
			$dto->actionName = $dto->moduleName . "_action_edit_style";
			$dto->view_actionName = $dto->moduleName . "_view_edit_style";
			$fileList += $this->actionCreator->create($dto);
			$dto->skeletonTemplateName = "maple.ini";
			$fileList += $this->iniCreator->create($dto);
			
			//dicon
			$dto->actionName = $dto->moduleName . "_" . "action";
			$dto->inifileName = "dicon.ini";
			$dto->skeletonTemplateName = "dicon.ini";
			$fileList += $this->iniCreator->create($dto);
			
			//components
			$dto->componentName = $dto->moduleName . "." . "components.view";
			$dto->setType = "module";
			$fileList += $this->componentCreator->create($dto);
			
			$dto->componentName = $dto->moduleName . "." . "components.action";
			$dto->setType = "module";
			$fileList += $this->componentCreator->create($dto);
			
			//sqlファイル
			$fileList += $this->sqlCreator->create($dto);
		
			//初期化
			$dto->view_actionName ="";
			
			//---------------------------------------------------------------
			// Action 
			//---------------------------------------------------------------
			
			
			//Delete
			//$dto->Type = "delete";
			//$dto->actionName = $dto->moduleName . "_action_admin_delete";
			//$fileList += $this->actionCreator->create($dto);
			//$dto->inifileName = null;
			//$dto->skeletonTemplateName = "maple.ini_preexecute";
			//$fileList += $this->iniCreator->create($dto);
			
			//---------------------------------------------------------------
			// View 
			//---------------------------------------------------------------
			$dto->inifileName = "maple.ini";
			$dto->actionName = $dto->moduleName . "_view_admin";
			$dto->skeletonTemplateName = "maple.ini_nobuild";
			$fileList += $this->iniCreator->create($dto);
			
			$dto->actionName = $dto->moduleName . "_" . "view";
			$dto->skeletonTemplateName = "maple.ini_global";
			$fileList += $this->iniCreator->create($dto);
			
			//Whatnew
			//$dto->Type = "whatnew";
			//$dto->actionName = $dto->moduleName . "_view_admin_whatnew";			
			//$dto->view_actionName = $dto->moduleName . "_view_admin_whatnew";
			//$fileList += $this->actionCreator->create($dto);
			//$dto->inifileName = null;
			//$dto->skeletonTemplateName = "maple.ini_preexecute";
			//$fileList += $this->iniCreator->create($dto);
			
			//Search
			$dto->Type = "search";
			$dto->actionName = $dto->moduleName . "_view_admin_search";
			$dto->view_actionName = $dto->moduleName . "_view_admin_search";
			$fileList += $this->actionCreator->create($dto);
			$dto->inifileName = null;
			$dto->skeletonTemplateName = "maple.ini_search";
			$fileList += $this->iniCreator->create($dto);
			
		}
		
		if($dto->moduleType == "full") {
			//初期化
			$dto->view_actionName ="";
			
			//---------------------------------------------------------------
			// Action 
			//---------------------------------------------------------------
			//Operation
			$dto->Type = "operation";
			$dto->actionName = $dto->moduleName . "_action_admin_operation";
			$fileList += $this->actionCreator->create($dto);
			$dto->inifileName = null;
			$dto->skeletonTemplateName = "maple.ini_operation";
			$fileList += $this->iniCreator->create($dto);
			
			//dicon
			$dto->actionName = $dto->moduleName . "_action_admin_operation";
			$dto->skeletonTemplateName = "dicon.ini_operation";
			$dto->inifileName = "dicon.ini";
			$fileList += $this->iniCreator->create($dto);
			
			
			//Move
			//$dto->Type = "move";
			//$dto->actionName = $dto->moduleName . "_action_admin_move";
			//$fileList += $this->actionCreator->create($dto);
			//$dto->inifileName = null;
			//$dto->skeletonTemplateName = "maple.ini_preexecute";
			//$fileList += $this->iniCreator->create($dto);
			
			//Copy
			//$dto->Type = "copy";
			//$dto->actionName = $dto->moduleName . "_action_admin_copy";
			//$fileList += $this->actionCreator->create($dto);
			//$dto->inifileName = null;
			//$dto->skeletonTemplateName = "maple.ini_preexecute";
			//$fileList += $this->iniCreator->create($dto);
			
			//Shortcut
			//$dto->Type = "shortcut";
			//$dto->actionName = $dto->moduleName . "_action_admin_shortcut";
			//$fileList += $this->actionCreator->create($dto);
			//$dto->inifileName = null;
			//$dto->skeletonTemplateName = "maple.ini_preexecute";
			//$fileList += $this->iniCreator->create($dto);
			
			//---------------------------------------------------------------
			// View 
			//---------------------------------------------------------------
			
			//personalinf
			//$dto->Type = "personalinf";
			//$dto->actionName = $dto->moduleName . "_view_admin_personalinf";
			//$dto->view_actionName = $dto->moduleName . "_view_admin_personalinf";
			//$fileList += $this->actionCreator->create($dto);
			//$dto->inifileName = null;
			//$dto->skeletonTemplateName = "maple.ini_preexecute";
			//$fileList += $this->iniCreator->create($dto);
		}
        
        return $fileList;
    }
}
?>
