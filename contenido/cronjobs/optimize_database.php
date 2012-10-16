<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 *  Cron Job to move old statistics into the stat_archive table
 * 
 * Requirements: 
 * @con_php_req 5
 *
 * @package    Contenido Backend <Area>
 * @version    1.12
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * {@internal 
 *   $Id: optimize_database.php 1157 2010-05-20 14:10:43Z xmurrix $:
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    define('CON_FRAMEWORK', true);
}

global $cfg, $area;

// CONTENIDO path
$contenidoPath = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../')) . '/';

// CONTENIDO startup process
include_once($contenidoPath . 'includes/startup.php');

if (!isRunningFromWeb() || function_exists('runJob') || $area == 'cronjobs') {
    $db = new DB_Contenido();

    foreach ($cfg['tab'] as $key => $value) {
        $sql = 'OPTIMIZE TABLE ' . $value;
        $db->query($sql);
    }

    if ($cfg['statistics_heap_table']) {
        $sHeapTable = $cfg['tab']['stat_heap_table'];
        buildHeapTable($sHeapTable, $db);
    }
}

?>