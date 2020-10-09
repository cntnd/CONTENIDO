<?php
/**
 * Defines all general variables of CONTENIDO.
 *
 * NOTE: This configuration file was generated by CONTENIDO setup!
 *       If you want to modify the configurations for some reason, create a file
 *       "config.local.php" in "data/config/{environment}/" and define your own settings.
 *
 * @package    Core
 * @subpackage Backend_ConfigFile
 * @author     System
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

global $cfg;

/* Section 1: Path settings
 * ------------------------
 *
 * Path settings which will vary along different CONTENIDO settings.
 *
 * A little note about web and server path settings:
 * - A Web Path can be imagined as web addresses. Example:
 *   http://192.168.1.1/test/
 * - A Server Path is the path on the server's hard disk. Example:
 *   /var/www/html/contenido    for Unix systems OR
 *   c:/htdocs/contenido        for Windows systems
 */

/* The root server path where all frontends reside */
$cfg['path']['frontend']                = '{CONTENIDO_ROOT}';

/* The root server path to the CONTENIDO backend */
$cfg['path']['contenido']               = $cfg['path']['frontend'] . '/contenido/';

/* The root server path to the data directory */
$cfg['path']['data']                    = $cfg['path']['frontend'] . '/data/';

/* The server path to all WYSIWYG-Editors */
$cfg['path']['all_wysiwyg']             = $cfg['path']['contenido']  . 'external/wysiwyg/';

/* The selected wysiwyg editor*/
$cfg['wysiwyg']['editor']               = 'tinymce4';

/* The server path to the desired WYSIWYG-Editor */
$cfg['path']['wysiwyg']                 = $cfg['path']['all_wysiwyg'] . $cfg['wysiwyg']['editor'] . '/';

/* The web server path to the CONTENIDO backend */
$cfg['path']['contenido_fullhtml']      = '{CONTENIDO_WEB}/contenido/';

/* The web path to all WYSIWYG-Editors */
$cfg['path']['all_wysiwyg_html']        = $cfg['path']['contenido_fullhtml'] . 'external/wysiwyg/';

/* The web path to the desired WYSIWYG-Editor */
$cfg['path']['wysiwyg_html']            = $cfg['path']['all_wysiwyg_html'] . $cfg['wysiwyg']['editor'] . '/';



/* Section 2: Database settings
 * ----------------------------
 *
 * Database settings for MySQLi/MySQL. Note that we don't support other databases.
 */

/* The prefix for all CONTENIDO system tables, usually 'con' */
$cfg['sql']['sqlprefix'] = '{MYSQL_PREFIX}';

/* Database extension/driver to use, feasible values are 'mysqli' or 'mysql' */
$cfg['database_extension'] = '{DB_EXTENSION}';

/**
 * Extended database settings. This settings will be used from CONTENIDO 4.9.0.
 *
 * @since  CONTENIDO version 4.9.0
 */
$cfg['db'] = [
    'connection' => [
        'host'     => '{MYSQL_HOST}', // (string) The host where your database runs on
        'database' => '{MYSQL_DB}',   // (string) The database name which you use
        'user'     => '{MYSQL_USER}', // (string) The username to access the database
        'password' => '{MYSQL_PASS}', // (string) The password to access the database
        'charset'  => '{MYSQL_CHARSET}', // (string) The charset of connection to database
        'options'  => [
            // (string[]) Database options
{MYSQL_OPTIONS}
        ],
    ],
    'haltBehavior'    => 'report', // (string) Feasible values are 'yes', 'no' or 'report'
    'haltMsgPrefix'   => (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] . ' ' : '',
    'enableProfiling' => false,    // (bool) Flag to enable profiling
];

/* Section 3: UTF-8 flag
 * ----------------------------
 *
 * Setting for UTF-8 flag
 *
 * @since	CONTENIDO version 4.9.5
 */
{CON_UTF8}
?>