<?php

/**
 * AMR debugger class
 *
 * @package    Plugin
 * @subpackage ModRewrite
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Mod rewrite debugger class.
 *
 * @author     Murat Purc <murat@purc.de>
 * @package    Plugin
 * @subpackage ModRewrite
 */
class ModRewriteDebugger {

    /**
     * Flag to enable debugger
     * @var  bool
     */
    protected static $_bEnabled = false;

    /**
     * Enable debugger setter.
     * @param  bool  $bEnabled
     */
    public static function setEnabled($bEnabled) {
        self::$_bEnabled = (bool) $bEnabled;
    }

    /**
     * Adds variable to debugger.
     * Wrapper for <code>cDebug::getDebugger('visible_adv')</code>.
     *
     * @param  mixed  $mVar   The variable to dump
     * @param  string $sLabel Description for passed $mVar
     *
     * @throws cInvalidArgumentException
     */
    public static function add($mVar, $sLabel = '') {
        if (!self::$_bEnabled) {
            return;
        }
        cDebug::getDebugger()->add($mVar, $sLabel);
    }

    /**
     * Returns output of all added variables to debug.
     *
     * @return  string
     * @throws cInvalidArgumentException
     */
    public static function getAll() {
        if (!self::$_bEnabled) {
            return '';
        }
        ob_start();
        cDebug::getDebugger()->showAll();
        $sOutput = ob_get_contents();
        ob_end_clean();
        return $sOutput;
    }

    /**
     * Logs variable to debugger.
     * Wrapper for <code>cDebug::getDebugger(cDebug::DEBUGGER_FILE)</code>.
     *
     * @param  mixed  $mVar   The variable to log the contents
     * @param  string $sLabel Description for passed $mVar
     *
     * @throws cInvalidArgumentException
     */
    public static function log($mVar, $sLabel = '') {
        if (!self::$_bEnabled) {
            return;
        }
        cDebug::getDebugger(cDebug::DEBUGGER_FILE)->show($mVar, $sLabel);
    }

}
