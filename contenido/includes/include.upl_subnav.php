<?php
/**
 * This file contains the sub navigation frame backend page in upload section.
 *
 * @package          Core
 * @subpackage       Backend
 * @version          SVN Revision $Rev:$
 *
 * @author           Jan Lengowski
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// Use remembered path from upl_last_path (from session)
if (!isset($_GET['path']) && $sess->isRegistered('upl_last_path')) {
    $_GET['path'] = $upl_last_path;
}

if (!isset($_GET['path'])) {
    $tpl->reset();
    $tpl->generate($cfg['path']['templates'] . $cfg['templates']['right_top_blank']);
    return;
}

$path = $_GET['path'];
$area = $_GET['area'];
$anchorTpl = '<a class="white" onclick="sub.clicked(this)" target="right_bottom" href="%s">%s</a>';

$nav = new cGuiNavigation();

$sql = "SELECT
            idarea
        FROM
            " . $cfg['tab']['area'] . " AS a
        WHERE
            a.name = '" . $db->escape($area) . "' OR
            a.parent_id = '" . $db->escape($area) . "'
        ORDER BY
            idarea";

$db->query($sql);

$in_str = '';

while ($db->nextRecord()) {
    $in_str .= $db->f('idarea') . ',';
}

$len = strlen($in_str) - 1;
$in_str = substr($in_str, 0, $len);
$in_str = '(' . $in_str . ')';

$sql = "SELECT
            b.location AS location,
            a.name AS name
        FROM
            " . $cfg["tab"]["area"] . " AS a,
            " . $cfg["tab"]["nav_sub"] . " AS b
        WHERE
            b.idarea IN " . $in_str . " AND
            b.idarea = a.idarea AND
            b.level = 1 AND
            b.online = 1
        ORDER BY
            b.idnavs";

$db->query($sql);

while ($db->nextRecord()) {
    // Extract names from the XML document.
    $caption = $nav->getName($db->f("location"));

    $areaName = $db->f("name");

    if ($perm->have_perm_area_action($areaName)) {
        if ($areaName != "upl_edit") {
            // Set template data
            $tpl->set('d', 'ID', 'c_' . $tpl->dyn_cnt);
            $tpl->set('d', 'DATA_NAME', $areaName);
            $tpl->set('d', 'CLASS', '');
            $tpl->set('d', 'OPTIONS', '');
            $tpl->set('d', 'CAPTION', sprintf($anchorTpl, $sess->url("main.php?area=$areaName&frame=4&path=$path&appendparameters=$appendparameters"), $caption));
            $tpl->next();
        }
    }
}

$tpl->set('s', 'COLSPAN', ($tpl->dyn_cnt * 2) + 2);

$tpl->set('s', 'CLASS', ''); // With menu (left frame)

// Generate the third navigation layer
$tpl->generate($cfg['path']['templates'] . $cfg['templates']['subnav']);
