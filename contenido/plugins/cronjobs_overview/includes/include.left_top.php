<?php

/**
 * This file contains the left top frame backend page for the plugin cronjob overview.
 *
 * @package    Plugin
 * @subpackage CronjobOverview
 * @author     Rusmir Jusufovic
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

/**
 * @var cPermission $perm
 * @var string $area
 * @var array $cfg
 * @var cGuiNotification $notification
 * @var cSession $sess
 */

//Has the user permission for crontab_edit
if (!$perm->have_perm_area_action($area, 'crontab_edit')) {
    $notification->displayNotification('error', i18n('Permission denied', 'cronjobs_overview'));
    return -1;
}

$file = $file ?? '';

$tpl = new cTemplate();
$tpl->set('s', 'LABLE_CRONJOB_EDIT', i18n('Edit cronjob', 'cronjobs_overview'));
$tpl->set('s', 'ROW', 'javascript:Con.multiLink(\'right_bottom\', \''.$sess->url("main.php?area=cronjob&frame=4&action=crontab_edit&file=$file").'\', \'left_bottom\',\''.$sess->url("main.php?area=cronjob&frame=2").'\');');
$tpl->generate(cRegistry::getBackendPath() .  $cfg['path']['plugins'] . "cronjobs_overview/templates/left_top.html");
