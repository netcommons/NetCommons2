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

require_once('maplex/generate/creatorNetcommons/SingleFile.class.php');

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
class Maplex_Generate_CreatorNetcommons_Action extends Maplex_Generate_CreatorNetcommons_SingleFile
{
    var $actionChain;
    
    function create(&$dto)
    {
    	if($dto->Type == "search")
    		$dto->skeletonTemplateName = "action_search";
    	else if($dto->Type == "delete")
    		$dto->skeletonTemplateName = "action_delete";
    	else if($dto->Type == "operation")
    		$dto->skeletonTemplateName = "action_operation";
    	else
    		$dto->skeletonTemplateName = "action";
    		
        list ($classname, $filename) = $this->actionChain->makeNames($dto->actionName);
        //$filename = $this->replaceWithConfig('MODULE_DIR', $filename);
        $filename = $this->config->getValue("WEBAPP_MODULE_DIR") . preg_replace(
            '/^'. preg_quote(constant('MODULE_DIR'), '/') .'/',
            '',
            $filename);
		
		$view_action_name = $dto->actionName;
		$module_type = $dto->moduleType;
		$dir_name = $dto->moduleName;
		$pathList = explode("_", $dto->actionName);
    	if(isset($pathList[1]) && $pathList[1] == "view") {
    		$action_type = "view";
    	} else {
    		$action_type = "action";
    	}
    	
        return $this->output(
            $filename,
            array('classname' => $classname,
            		'type'=> $dto->Type,
            		'dir_name'=> $dir_name,
            		'module_type'=> $module_type,
            		'view_action_name' => $view_action_name,
            		'action_type'=> $action_type),
            		'INTERNAL_CODE',
            		$this->getTemplateFile($dto->skeletonTemplateName)
            		);
    }
}
?>
