<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * テーマ関連ファイルを生成する
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

require_once(MAPLE_DIR .'/actionBase/Generator.class.php');

/**
 * テーマ関連ファイルを生成する
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Maple_Generate_Generators_Theme extends Action_Generator
{
    function prepareDto(&$dto)
    {
    	$pathList = explode("_", $dto->actionName);
    	$dto->themeName = $pathList[0];
    	if(!isset($pathList[1])) {
    		$dto->secondName = "classic_default";
    	} else {
    		$dto->secondName = $pathList[1];
    	}
    	
        if(!$dto->themeType){
            $dto->themeType = "normal";
        }
        if($dto->themeType != "simple" && $dto->themeType != "normal" && $dto->themeType != "full"){
            $dto->themeType = "normal";
        }
    }
}
?>
