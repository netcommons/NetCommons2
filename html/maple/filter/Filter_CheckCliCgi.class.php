<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * CLI環境で動作しているかをチェックするFilter
 *
 * @package     Maple.filter
 * @author      Ryuji.M
 * @copyright  2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */
class Filter_CheckCliCgi extends Filter
{
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.1.0
     */
    function Filter_CheckCliCgi()
    {
        parent::Filter();
    }

    /**
     * Actionを実行
     *
     * @access  public
     * @since   3.1.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->trace("Filter_CheckCliCgiの前処理が実行されました", "Filter_CheckCliCgi#execute");

        $sapiName = php_sapi_name();
        if ($sapiName != 'cli' && $sapiName != 'cgi') {
            $message = 'CLIではない環境で実行されました';
            $log->error($message, "Filter_CheckCliCgi#execute");

            if (isset($_SERVER['REQUEST_METHOD']) &&
                $_SERVER['REQUEST_METHOD']) {
                header("HTTP/1.0 403 Forbidden");
            } else {
                if (OUTPUT_CODE != SCRIPT_CODE) {
                    $message = mb_convert_encoding($message, OUTPUT_CODE, SCRIPT_CODE);
                }
                print $message;
            }

            exit;
        }

        $container =& DIContainerFactory::getContainer();
        $filterChain =& $container->getComponent("FilterChain");
        $filterChain->execute();

        $log->trace("Filter_CheckCliCgiの後処理が実行されました", "Filter_CheckCliCgi#execute");
    }
}
?>
