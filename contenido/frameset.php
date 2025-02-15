<?php

/**
 * This file loads the complete backend frameset.
 *
 * @package    Core
 * @subpackage Backend
 * @author     Olaf Niemann
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    define('CON_FRAMEWORK', true);
}

// CONTENIDO startup process
include_once('./includes/startup.php');

cRegistry::bootstrap(
    [
        'sess' => 'cSession',
        'auth' => 'cAuthHandlerBackend',
        'perm' => 'cPermission',
    ]
);

$area = cRegistry::getArea();
$belang = cRegistry::getBackendLanguage();
$cfg = cRegistry::getConfig();
$sess = cRegistry::getSession();

i18nInit($cfg['path']['contenido_locale'], $belang);

require_once($cfg['path']['contenido_config'] . 'cfg_actions.inc.php');

// Create CONTENIDO classes
$db  = cRegistry::getDb();
$tpl = new cTemplate();

// Build the CONTENIDO content area frameset
$tpl->reset();

$backendUrl = cRegistry::getBackendUrl();

if (isset($_GET['appendparameters'])) {
    $tpl->set('s', 'LEFT', str_replace('&', '&amp;', $sess->url("frameset_left.php?area=$area&appendparameters=" . $_GET['appendparameters'])));
    $tpl->set('s', 'RIGHT', str_replace('&', '&amp;', $sess->url("frameset_right.php?area=$area&appendparameters=" . $_GET['appendparameters'])));
    $tpl->set('s', 'WIDTH', getEffectiveSetting('backend', 'leftframewidth', 245));
} else {
    $tpl->set('s', 'LEFT', str_replace('&', '&amp;', $sess->url("frameset_left.php?area=$area")));
    $tpl->set('s', 'RIGHT', str_replace('&', '&amp;', $sess->url("frameset_right.php?area=$area")));
    $tpl->set('s', 'WIDTH', getEffectiveSetting('backend', 'leftframewidth', 245));
}

$tpl->set('s', 'VERSION',  CON_VERSION);
$tpl->set('s', 'LOCATION', $backendUrl);

// Hide menu-frame for some areas
$oAreaColl = new cApiAreaCollection();
$oAreaColl->select('menuless=1');
$aMenulessAreas = [];
while ($oItem = $oAreaColl->next()) {
    $aMenulessAreas[] = $oItem->get('name');
}

if (in_array($area, $aMenulessAreas) || (isset($menuless) && $menuless == 1)) {
    $menuless = true;
    if (isset($_GET['appendparameters'])) {
        $tpl->set('s', 'FRAME[1]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=1&appendparameters=" . $_GET['appendparameters'])));
        $tpl->set('s', 'FRAME[2]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=2&appendparameters=" . $_GET['appendparameters'])));
        $tpl->set('s', 'FRAME[3]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=3&appendparameters=" . $_GET['appendparameters'])));
        $tpl->set('s', 'FRAME[4]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=4&appendparameters=" . $_GET['appendparameters'])));
    } else {
        $tpl->set('s', 'FRAME[1]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=1")));
        $tpl->set('s', 'FRAME[2]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=2")));
        $tpl->set('s', 'FRAME[3]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=3")));
        $tpl->set('s', 'FRAME[4]', str_replace('&', '&amp;', $sess->url("main.php?area=$area&frame=4")));
    }
}

$tpl->set('s', 'CONTENIDOPATH', cRegistry::getBackendUrl() . 'favicon.ico');

if ((isset($menuless) && $menuless == 1)) {
    $tpl->generate($cfg['path']['templates'] . $cfg['templates']['frameset_menuless_content']);
} else {
    preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT'], $msie);
    preg_match('/safari/i', $_SERVER['HTTP_USER_AGENT'], $safari);
    $tpl->set('s', 'CONTENT_FRAME_BORDER', ($msie ? '0' : '1'));
    $tpl->set('s', 'LEFT_BORDER', ($safari ? '1' : '0'));
    $tpl->generate($cfg['path']['templates'] . $cfg['templates']['frameset_content']);
}

cRegistry::shutdown();

?>
