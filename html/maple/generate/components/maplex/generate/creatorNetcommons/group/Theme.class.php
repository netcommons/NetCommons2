<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * theme関連ファイルを書き出す
 *
 * @package     NetCommons.generate
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @project      NetCommons Project, supported by National Institute of Informatics
 * @license      http://www.netcommons.org/license.txt  NetCommons License
 * @access       public
 */

require_once('maplex/generate/creatorNetcommons/Abstract.class.php');

class Maplex_Generate_CreatorNetcommons_Group_Theme
 extends Maplex_Generate_CreatorNetcommons_Abstract
{
	var $themeCreator;
	var $imagesCreator;
	
    /**
     * theme関連ファイルを書き出す
     *
     * @param  object  $dto    DTOクラスのインスタンス
     * @access  public
     */
    function create(&$dto)
    {
        $fileList = array();
        $dto->Type = "config";
        //
        //theme.ini作成
        //
        $dto->skeletonTemplateName = "theme.ini";
        $dto->inifileName = "theme.ini";
        $fileList += $this->themeCreator->create($dto);
        //
        //icon_color.ini作成
        //  
        //if($dto->themeType == "full") {
        	$dto->skeletonTemplateName = "icon_color.ini";
        	$dto->inifileName = "icon_color.ini";
        	$fileList += $this->themeCreator->create($dto);
        //}
        
        
        //
        // block_custom.ini,page_custom.ini作成
        //
        if($dto->themeType == "full") {
        	$dto->skeletonTemplateName = "block_custom.ini";
        	$dto->inifileName = "block_custom.ini";
        	$fileList += $this->themeCreator->create($dto);
        	
        	$dto->skeletonTemplateName = "page_custom.ini";
        	$dto->inifileName = "page_custom.ini";
        	$fileList += $this->themeCreator->create($dto);
        }
        //
        // CSS
        //
        $dto->Type = "css";
        
        $dto->skeletonTemplateName = "theme_style.css";
    	$dto->inifileName = "style.css";
    	$fileList += $this->themeCreator->create($dto);
        if($dto->themeType != "simple") {
        	$dto->skeletonTemplateName = "theme_page_style.css";
	    	$dto->inifileName = "page_style.css";
	    	$fileList += $this->themeCreator->create($dto);
        }
        //
        // imagesコピー
        //
        $dto->Type = "images";
        $dto->copyImageDir = THEME_IMAGES_DIR."thumbnail/";
        $this->imagesCreator->create($dto);
        
        $dto->copyImageDir = THEME_IMAGES_DIR."images/";
        $this->imagesCreator->create($dto);
        
        //
        // language下：theme.ini,block_custom.ini作成
        //
        $dto->Type = "language";
        $dto->skeletonTemplateName = "theme_lang.ini";
    	$dto->inifileName = "theme.ini";
    	$fileList += $this->themeCreator->create($dto);
        if($dto->themeType == "full") {
        	$dto->skeletonTemplateName = "block_custom_lang.ini";
	    	$dto->inifileName = "block_custom.ini";
	    	$fileList += $this->themeCreator->create($dto);
        }
        
        //
        // templates作成
        //
        $dto->Type = "templates";
        $dto->skeletonTemplateName = "block.html";
    	$dto->inifileName = "block.html";
    	$fileList += $this->themeCreator->create($dto);
    	
    	$dto->skeletonTemplateName = "block_main.html";
    	$dto->inifileName = "block_main.html";
    	$fileList += $this->themeCreator->create($dto);
        
        return $fileList;
    }
}
?>
