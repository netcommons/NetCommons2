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
 * @version     CVS: $Id: Filter_ExtractProperties.class.php,v 1.1 2006/10/13 08:50:13 Ryuji.M Exp $
 */

/**
 * コンポーネントのプロパティをRequestに展開する
 *
 * @package     Maple.generate
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class Filter_ExtractProperties extends Filter_Abstract
{
    var $componentKey;

    var $extractObject = false;

    var $override = false;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_ExtractProperties()
    {
        parent::Filter_Abstract();
    }

    /**
     * コンポーネントのプロパティをRequestに展開する
     *
     * @access  public
     */
    function execute()
    {
        $className = get_class($this);
        $container =& DIContainerFactory::getContainer();
        
        $log =& LogFactory::getLog();
        $log->trace("${className}の前処理が実行されました", "{$className}#execute");

        if($this->componentKey &&
           is_object($obj =& $container->getComponent($this->componentKey))) {
            
            $req =& $container->getComponent('Request');
            foreach($obj as $prop => $value) {
                if(preg_match('/^_/', $prop) ||
                   ($req->getParameter($prop) !== null && !$this->override)) {
                    continue;
                }
                
                if(!is_object($value)) {
                    $req->setParameter($prop, $value);
                } elseif($this->extractObject) {
                    $req->setParameterRef($prop, $obj->$prop);
                }
            }
        }
        //
        // ここで一旦次のフィルターに制御を移す
        //

        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        //
        // ここに後処理を記述
        //

        $log->trace("${className}の後処理が実行されました", "${className}#execute");
    }
}
?>
