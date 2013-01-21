<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_CheckConfig.class.php,v 1.1 2006/10/13 08:50:13 Ryuji.M Exp $
 */

require_once(MAPLE_DIR .'/filter/Abstract.class.php');

/**
 * TargetWebappConfigに必要な設定が含まれているかどうか調べる
 * 
 * [CheckConfig]
 * CONST1 =
 * CONST2 = optional
 * CONST3 = section
 * 
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Filter_CheckConfig extends Filter_Abstract
{
    var $_component = 'TargetWebappConfig';
    
    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_CheckConfig()
    {
        parent::Filter_Abstract();
    }

    /**
     * 
     *
     * @access  public
     */
    function execute()
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent('ActionChain');
        $errorList =& $actionChain->getCurErrorList();
        $className = get_class($this);
        $log =& LogFactory::getLog();

        $log->trace("${className}の前処理が実行されました", "{$className}#execute");

        $globalConfig =& $container->getComponent($this->_component);
        if(!is_object($globalConfig)) {
            $this->_fatalError(
                "{$this->_component}が登録されていません", "{$className}#execute");
        }
        
        if($errorList->getType() != "") {
            $errorList->add(get_class($this),
                            "failed to initialize {$this->_component}");
            
        } elseif(!$this-> _checkConstants($globalConfig, $errorList)) {
            $errorList->setType('error');
        }
        
        //
        //
        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        //
        // ここに後処理を記述
        //

        $log->trace("${className}の後処理が実行されました", "${className}#execute");
    }

    /**
     * 必要な値が設定されているかどうか調べる
     * 
     * 
     * @since 06/07/15 23:28
     * @return String
     */
    function _checkConstants(&$config, &$errorList)
    {
        $result = true;
        if(!is_array($consts = $this->getAttributes())) {
            return $result;
        }

        foreach($consts as $c => $type) {
            if(preg_match('/^_/', $c)) {
                continue;
            }
            
            if($type == 'section') {
               if($config->getSection($c) === null) {
                   $errorList->add('generate',
                                   sprintf(
                                       "%s section is not defined in the %s",
                                       $c,
                                       GLOBAL_CONFIG));
                   $result = false;
               }
                continue;
            }

            if(!$config->hasValue($c, false) && $type != 'optional') {
                   $errorList->add('generate',
                                   sprintf(
                                       "%s is not defined",
                                       $c));
                $result = false;
            }
        }
        
        return $result;
    }
}
?>
