<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * module関連ファイルを生成する
 * @param
 * -------------------------------------------------------
 * moduleName：required
 * moduleType：required	
 *          		simple | normal | full
 * -------------------------------------------------------
 *install.ini          * 	   * 	   *
 *view                 * 	   * 	   *
 *language             * 	   * 	   *
 *files                * 	   * 	   *
 *templates            * 	   * 	   *
 *action               * 	   * 	   *
 *components           - 	   * 	   *
 *sql                  - 	   * 	   *
 *config               - 	   - 	   *
 *search_action        - 	   - 	   *
 *delete_action        - 	   - 	   -
 *block_add_action     - 	   - 	   -
 *block_delete_action  - 	   - 	   -
 *move_action          - 	   - 	   *
 *copy_action          - 	   - 	   *
 *shortcut_action      - 	   - 	   *
 *personalinf_action   - 	   - 	   -
 *whatnew_flag         - 	   - 	   *
 *backup_action        - 	   - 	   -
 *restore_action       - 	   - 	   -
 * -------------------------------------------------------
 * langDir：default="japanese"
 * sqlDir：default="mysql"
 * themeDir：default="classic_default"
 * templateDir：default="default"
 * 
 * @package     NetCommons.generate
 * @author      Ryuji.M
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */
require_once(MAPLE_DIR .'/actionBase/Generator.class.php');

/**
 * module関連ファイルを生成する
 *
 * @package     NetCommons.generate
 * @author      Ryuji.M
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */
class Maple_Generate_Generators_Module extends Action_Generator
{
    function prepareDto(&$dto)
    {
        if(!$dto->moduleType){
            $dto->moduleType = "normal";
        }
        if($dto->moduleType != "simple" && $dto->moduleType != "normal" && $dto->moduleType != "full"){
            $dto->moduleType = "normal";
        }
        if(!$dto->themeDir){
            $dto->themeDir = "classic_default";
        }
        if(!$dto->templateDir){
            $dto->templateDir = "default";
        }
        if(!$dto->langDir){
            $dto->langDir = "japanese";
        }
        if(!$dto->sqlDir){
            $dto->sqlDir = "mysql";
        }
        $dto->cssFile = "style.css";
        $dto->jsFile = $dto->moduleName.".js";
        $dto->copyImageDir = IMAGES_DIR;
    }
}
?>
