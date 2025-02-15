<?php

/**
 * This file contains the menu frame (category tree) backend page for article list.
 *
 * @package    Core
 * @subpackage Backend
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

global $syncidcat, $syncfromlang, $multiple, $markscript, $tpl, $action, $lang, $sess, $client, $cfg, $db, $area, $frame, $idcat, $currentuser, $_cecRegistry, $perm;

// Display critical error if no valid client is selected
if ($client < 1) {
    $oPage = new cGuiPage('con_left_top');
    $oPage->displayCriticalError(i18n("No Client selected"));
    $oPage->render();
    return;
}

cInclude("includes", "functions.str.php");
cInclude("includes", "functions.tpl.php");
cInclude('includes', 'functions.lang.php');

/**
 *
 * @param int $iIdcat
 * @param array $aWholelist
 *
 * @return string
 *
 * @throws cDbException
 * @throws cInvalidArgumentException
 * @throws cException
 */
function showTree($iIdcat, &$aWholelist) {
    global $check_global_rights, $sess, $cfg, $perm, $db, $db2, $db3, $area, $client, $lang, $navigationTree;

    $tpl = new cTemplate();
    $tpl->reset();

    $iIdcat = (int) $iIdcat;

    foreach ($navigationTree[$iIdcat] as $sKey => $aValue) {

        $cfgdata = '';
        $aCssClasses = [];

        // Check rights per cat
        if (!$check_global_rights) {
            $check_rights = false;
        } else {
            $check_rights = true;
        }

        if (!$check_rights) {
            $check_rights = ($aValue['forcedisplay'] == 1) ? true : false;
        }

        $idcat = (int) $aValue['idcat'];
        $level = $aValue['level'] - 1;
        $name = $aValue['name'];

        if ($check_rights) {

            $idtpl = ($aValue['idtpl'] != '') ? $aValue['idtpl'] : 0;

            // if (($aValue["idlang"] != $lang) || ($aValue['articles'] == true)) {
            //     $aCssClasses[] = 'con_sync';
            // }

            $check_rights = $perm->have_perm_area_action_item("con", "con_changetemplate", $aValue['idcat']);
            if (!$check_rights) {
                $check_rights = $perm->have_perm_area_action("con", "con_changetemplate");
            }

            $changetemplate = ($check_rights) ? 1 : 0;

            $check_rights = $perm->have_perm_area_action_item("con", "con_makecatonline", $aValue['idcat']);
            if (!$check_rights) {
                $check_rights = $perm->have_perm_area_action("con", "con_makecatonline");
            }

            $onoffline = ($check_rights) ? 1 : 0;

            $check_rights = $perm->have_perm_area_action_item("con", "con_makepublic", $aValue['idcat']);
            if (!$check_rights) {
                $check_rights = $perm->have_perm_area_action("con", "con_makepublic");
            }

            $makepublic = ($check_rights) ? 1 : 0;

            $check_rights = $perm->have_perm_area_action_item("con", "con_tplcfg_edit", $aValue['idcat']);
            if (!$check_rights) {
                $check_rights = $perm->have_perm_area_action("con", "con_tplcfg_edit");
            }

            $templateconfig = ($check_rights) ? 1 : 0;

            if ($aValue["idlang"] == $lang) {
                // Build cfgdata string
                $cfgdata = $idcat . "-" . $idtpl . "-" . $aValue['online'] . "-" . $aValue['public'] . "-" .
                        $changetemplate . "-" .
                        $onoffline . "-" .
                        $makepublic . "-" . $templateconfig;
            } else {
                $cfgdata = "";
            }

            // Select the appropriate folder-image depending on the structure properties
            if ($aValue['online'] == 1) {
                // Category is online

                if ($aValue['public'] == 0) {
                    // Category is locked
                    if ($aValue['no_start'] || $aValue['no_online']) {
                        $aAnchorClass = 'on_error_locked';
                    } else {
                        $aAnchorClass = 'on_locked';
                    }
                } else {
                    // Category is public
                    if ($aValue['no_start'] || $aValue['no_online']) {
                        $aAnchorClass = 'on_error';
                    } else {
                        $aAnchorClass = 'on';
                    }
                }
            } else {
                // Category is offline

                if ($aValue['public'] == 0) {
                    // Category is locked
                    if ($aValue['no_start'] || $aValue['no_online']) {
                        $aAnchorClass = 'off_error_locked';
                    } else {
                        $aAnchorClass = 'off_locked';
                    }
                } else {
                    // Category is public
                    if ($aValue['no_start'] || $aValue['no_online']) {
                        $aAnchorClass = 'off_error';
                    } else {
                        $aAnchorClass = 'off';
                    }
                }
            }

            if ($aValue['islast'] == 1) {
                $aCssClasses[] = 'last';
            }

            if ($aValue['collapsed'] == 1 && isset($navigationTree[$idcat]) && is_array($navigationTree[$idcat])) {
                $aCssClasses[] = 'collapsed';
            }

            if ($aValue['active']) {
                $aCssClasses[] = 'active';
            }

            $bIsSyncable = false;
            if ($aValue["idlang"] != $lang) {
                // Fetch parent id and check if it is syncronized
                $sql = "SELECT parentid FROM %s WHERE idcat = '%s'";
                $db->query(sprintf($sql, $cfg["tab"]["cat"], $idcat));
                if ($db->nextRecord()) {
                    if ($db->f("parentid") != 0) {
                        $parentid = $db->f("parentid");
                        $sql = "SELECT idcatlang FROM %s WHERE idcat = '%s' AND idlang = '%s'";
                        $db->query(sprintf($sql, $cfg["tab"]["cat_lang"], cSecurity::toInteger($parentid), cSecurity::toInteger($lang)));

                        if ($db->nextRecord()) {
                            $aCssClasses[] = 'con_sync';
                            $bIsSyncable = true;
                        }
                    } else {
                        $aCssClasses[] = 'con_sync';
                        $bIsSyncable = true;
                    }
                }
            }

            // Last param defines if cat is syncable or not, all other rights are disabled at this point
            if ($bIsSyncable) {
                if ($cfgdata != '') {
                    $cfgdata .= '-1';
                } else {
                    $cfgdata = $idcat . "-" . $idtpl . "-" . $aValue['online'] . "-" . $aValue['public'] .
                            "-0-0-0-0-1";
                }
            } else {
                if ($cfgdata != '') {
                    $cfgdata .= '-0';
                } else {
                    $cfgdata = $idcat . "-" . $idtpl . "-" . $aValue['online'] . "-" . $aValue['public'] .
                            "-0-0-0-0-0";
                }
            }

            $strName = cSecurity::unFilter($name);
            $title = ($aValue['langPopup'] && $aValue['langPopup'] != "") ? $aValue['langPopup']."\n " : "";
            $mstr = '<a class="' . $aAnchorClass . '" href="#" title="'.$title.'idcat' . '&#58; ' . $idcat . '">' . $strName . '</a>';

            // Build Tree
            $tpl->set('d', 'CFGDATA', $cfgdata);
            if (isset($navigationTree[$idcat]) && is_array($navigationTree[$idcat])) {
                $tpl->set('d', 'SUBCATS', showTree($idcat, $aWholelist));
                $tpl->set('d', 'COLLAPSE', '<a href="#"> </a>');
                $aWholelist[] = $idcat;
            } else {
                $tpl->set('d', 'SUBCATS', '');
                $tpl->set('d', 'COLLAPSE', '<span> </span>');
            }
            $tpl->set('d', 'CAT', $mstr);
            $tpl->set('d', 'CSS_CLASS', ' class="' . implode(' ', $aCssClasses) . '"');

            $tpl->next();
        } else {
            if (is_array($navigationTree[(int) $aValue['idcat']])) {
                $sTpl = showTree((int) $aValue['idcat'], $aWholelist);
                if (!preg_match('/^<ul>\s*<\/ul>$/', $sTpl)) {
                    $tpl->set('d', 'CFGDATA', '0-0-0-0-0-0-0-0-0');
                    $tpl->set('d', 'SUBCATS', $sTpl);
                    $tpl->set('d', 'COLLAPSE', '<a href="#"></a>');
                    $tpl->set('d', 'CAT', '<a class="off_disabled" href="#">' . $name . '</a>');
                    $tpl->set('d', 'CSS_CLASS', ' class="active"');
                    $tpl->next();
                }
                $aWholelist[] = $aValue['idcat'];
            }
        }
    }
    return $tpl->generate($cfg['path']['templates'] . 'template.con_str_overview.list.html', 1);
}

$db2 = cRegistry::getDb();
$db3 = cRegistry::getDb();

// Refresh or reset right frames, when a synclang is changed or a category is synchronized
$tpl->reset();

if ($action == "con_synccat" || isset($_GET['refresh_syncoptions']) && $_GET['refresh_syncoptions'] == 'true') {
    $tpl->set('s', 'RELOAD_RIGHT', 'reloadRightFrame();');
} else {
    $tpl->set('s', 'RELOAD_RIGHT', '');
}

if ($action == "con_synccat") {
    strSyncCategory($syncidcat, $syncfromlang, $lang, $multiple);
    $remakeStrTable = true;
}

if (!is_object($db2))
    $db2 = cRegistry::getDb();

if (!isset($remakeStrTable)) {
    $remakeStrTable = false;
}

if (!isset($remakeCatTable)) {
    $remakeCatTable = false;
}

$sess->register("remakeCatTable");
$sess->register("CatTableClient");
$sess->register("CatTableLang");
$sess->register("remakeStrTable");

if (isset($syncoptions)) {
    $syncfrom = $syncoptions;
    $remakeCatTable = true;
}

if (!isset($syncfrom)) {
    $syncfrom = 0;
}

$sess->register("syncfrom");

$syncoptions = $syncfrom;

if (!isset($CatTableClient)) {
    $CatTableClient = 0;
}

if ($CatTableClient != $client) {
    $remakeCatTable = true;
}

if (!isset($CatTableLang)) {
    $CatTableLang = 0;
}

if ($CatTableLang != $lang) {
    $remakeCatTable = true;
}

$CatTableClient = $client;
$CatTableLang = $lang;

if ($syncoptions == -1) {
    $sql = "SELECT
                a.preid AS preid,
                a.postid AS postid,
                a.parentid AS parentid,
                c.idcat AS idcat,
                c.level AS level,
                b.name AS name,
                b.public AS public,
                b.visible AS online,
                d.idtpl AS idtpl,
                b.idlang AS idlang,
                c.idtree AS idtree
            FROM
                (" . $cfg["tab"]["cat"] . " AS a,
                " . $cfg["tab"]["cat_lang"] . " AS b,
                " . $cfg["tab"]["cat_tree"] . " AS c)
            LEFT JOIN
                " . $cfg["tab"]["tpl_conf"] . " AS d
                ON d.idtplcfg = b.idtplcfg
            WHERE
                a.idclient = '" . cSecurity::toInteger($client) . "' AND
                b.idlang   = '" . cSecurity::toInteger($lang) . "' AND
                c.idcat    = b.idcat AND
                b.idcat    = a.idcat
            ORDER BY
                c.idtree ASC";
} else {
    $sql = "SELECT
                a.preid AS preid,
                a.postid AS postid,
                a.parentid AS parentid,
                c.idcat AS idcat,
                c.level AS level,
                b.name AS name,
                b.public AS public,
                b.visible AS online,
                d.idtpl AS idtpl,
                b.idlang AS idlang,
                c.idtree AS idtree
            FROM
                (" . $cfg["tab"]["cat"] . " AS a,
                " . $cfg["tab"]["cat_lang"] . " AS b,
                " . $cfg["tab"]["cat_tree"] . " AS c)
            LEFT JOIN
                " . $cfg["tab"]["tpl_conf"] . " AS d
                ON d.idtplcfg = b.idtplcfg
            WHERE
                a.idclient = '" . cSecurity::toInteger($client) . "' AND
                (b.idlang  = '" . cSecurity::toInteger($lang) . "' OR
                 b.idlang  = '" . cSecurity::toInteger($syncoptions) . "') AND
                c.idcat    = b.idcat AND
                b.idcat    = a.idcat
            ORDER BY
                c.idtree ASC";
}

$db->query($sql);

if (isset($syncoptions)) {
    $remakeCatTable = true;
}

if (isset($online)) {
    $remakeCatTable = true;
}

if (isset($public)) {
    $remakeCatTable = true;
}

if (isset($idtpl)) {
    $remakeCatTable = true;
}

if (isset($force)) {
    $remakeCatTable = true;
}

$arrIn = [];
while ($db->nextRecord()) {
    $arrIn[] = $db->f('idcat');
}

$arrArtCache = [];
$aIsArticles = [];

if (count($arrIn) > 0) {
    $sIn = implode(',', $arrIn);

    $sql2 = "SELECT b.idcat, a.idart, idlang
            FROM " . $cfg["tab"]["art_lang"] . " AS a, " . $cfg["tab"]["cat_art"] . " AS b
            WHERE b.idcat IN (" . $db->escape($sIn) . ")
                AND (a.idlang = " . cSecurity::toInteger($syncoptions) . " OR a.idlang = " . cSecurity::toInteger($lang) . ")
                AND b.idart = a.idart";
    $db->query($sql2);

    while ($db->nextRecord()) {
        $arrArtCache[$db->f('idcat')][$db->f('idart')][$db->f('idlang')] = 'x';
    }
}

$db->query($sql);

while ($db->nextRecord()) {
    $entry = [];

    $entry['articles'] = false;

    if ($db->f("idlang") == $lang) {
        $arts = [];

        if (isset($arrArtCache[$db->f("idcat")])) {
            foreach ($arrArtCache[$db->f("idcat")] as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $arts[$key][$key2] = 1;
                }
            }
        }

        foreach ($arts as $idart => $entry) {
            if (is_array($entry)) {
                if (!array_key_exists($lang, $entry)) {
                    //$entry['articles'] = true;
                    $aIsArticles[$db->f("idcat")] = true;
                    break;
                }
            }
        }
    }
}

if ($syncoptions == -1) {
    $sql2 = "SELECT
                c.idcat AS idcat,
                SUM(a.online) AS online,
                d.startidartlang
            FROM
                " . $cfg["tab"]["art_lang"] . " AS a,
                " . $cfg["tab"]["art"] . " AS b,
                " . $cfg["tab"]["cat_art"] . " AS c,
                " . $cfg["tab"]["cat_lang"] . " AS d
            WHERE
                a.idlang = " . cSecurity::toInteger($lang) . " AND
                a.idart = b.idart AND
                b.idclient = '" . cSecurity::toInteger($client) . "' AND
                b.idart = c.idart AND
                c.idcat = d.idcat
            GROUP BY c.idcat, online, d.startidartlang";
} else {
    $sql2 = "SELECT
                c.idcat AS idcat,
                SUM(a.online) AS online,
                d.startidartlang
            FROM
                " . $cfg["tab"]["art_lang"] . " AS a,
                " . $cfg["tab"]["art"] . " AS b,
                " . $cfg["tab"]["cat_art"] . " AS c,
                " . $cfg["tab"]["cat_lang"] . " AS d
            WHERE
                a.idart = b.idart AND
                b.idclient = '" . cSecurity::toInteger($client) . "' AND
                b.idart = c.idart AND
                c.idcat = d.idcat
            GROUP BY c.idcat, online, d.startidartlang";
}
$db->query($sql2);

$aStartOnlineArticles = [];
while ($db->nextRecord()) {
    if ($db->f('startidartlang') > 0) {
        $aStartOnlineArticles[$db->f('idcat')]['is_start'] = true;
    } else {
        $aStartOnlineArticles[$db->f('idcat')]['is_start'] = false;
    }
    if ($db->f('online') > 0) {
        $aStartOnlineArticles[$db->f('idcat')]['is_online'] = true;
    } else {
        $aStartOnlineArticles[$db->f('idcat')]['is_online'] = false;
    }
}

$_cecIterator = $_cecRegistry->getIterator("Contenido.ArticleCategoryList.ListItems");

if ($_cecIterator->count() > 0) {
    while ($chainEntry = $_cecIterator->next()) {
        $listItem = $chainEntry->execute();

        if (is_array($listItem)) {
            if (!array_key_exists("expandcollapseimage", $listItem) || $listItem["expandcollapseimage"] == "") {
                $collapseImage = '<img src="images/spacer.gif" width="11" alt="" height="11">';
            } else {
                $collapseImage = $listItem["expandcollapseimage"];
            }

            if (!array_key_exists("image", $listItem) || $listItem["image"] == "") {
                $image = '<img src="images/spacer.gif" alt="">';
            } else {
                $image = $listItem["image"];
            }

            if (!array_key_exists("id", $listItem) || $listItem["id"] == "") {
                $id = rand();
            } else {
                $id = $listItem["id"];
            }

            if (array_key_exists("markable", $listItem)) {
                if ($listItem["markable"] == true) {
                    $mmark = $markscript;
                } else {
                    $mmark = "";
                }
            } else {
                $mmark = "";
            }
        }
    }
}

$languages = getLanguageNamesByClient($client);

// Expand all / Collapse all

$selflink = "main.php";
$expandlink = $sess->url($selflink . "?area=$area&frame=$frame&expand=all&syncoptions=$syncoptions");
$collapselink = $sess->url($selflink . "?area=$area&frame=$frame&collapse=all&syncoptions=$syncoptions");
$collapseimg = '<a href="' . $collapselink . '" alt="' . i18n("Close all categories") . '" title="' . i18n("Close all categories") . '"><img src="images/but_minus.gif"></a>';
$expandimg = '<a href="' . $expandlink . '" alt="' . i18n("Open all categories") . '" title="' . i18n("Open all categories") . '"><img src="images/but_plus.gif"></a>';
$allLinks = $expandimg . '<img src="images/spacer.gif" width="3" alt="">' . $collapseimg;
$text_direction = langGetTextDirection($lang);

// Check global rights
$check_global_rights = $perm->have_perm_area_action("con", "con_makestart");
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con_editart", "con_edit");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con_editart", "con_saveart");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con_editcontent", "con_editart");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con_editart", "con_newart");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con", "con_deleteart");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con", "con_makeonline");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con", "con_tplcfg_edit");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con", "con_makecatonline");
}
if (!$check_global_rights) {
    $check_global_rights = $perm->have_perm_area_action("con", "con_changetemplate");
}

if ($lang > $syncoptions) {
    $sOrder = 'DESC';
} else {
    $sOrder = 'ASC';
}

$fallbackLang = getEffectiveSetting('system', 'cat_fallback_language', 0);
$sqlLangPopup = ($fallbackLang != 0) ? "LEFT JOIN {$cfg['tab']['cat_lang']} AS b1 ON(b1.idcat = a.idcat AND b1.idlang = $fallbackLang) " : "";
$sqlLangB1 = ($fallbackLang != 0) ? "b1.name as langPopup, " : "";

$client = (int) $client;
$sql = "SELECT DISTINCT " .
        "a.idcat, " .
        "a.parentid, " .
        "a.preid, " .
        "a.postid, " .
        "a.parentid, " .
        "b.name, " .
        $sqlLangB1 .
        "b.idlang, " .
        "b.visible, " .
        "b.public, " .
        "c.idtree, " .
        "c.level, " .
        "d.idtpl " .
        "FROM {$cfg['tab']['cat']} AS a " .
        "LEFT JOIN {$cfg['tab']['cat_lang']} AS b ON a.idcat = b.idcat " .
        $sqlLangPopup .
        "LEFT JOIN {$cfg['tab']['cat_tree']} AS c ON (a.idcat = c.idcat AND b.idcat = c.idcat) " .
        "LEFT JOIN {$cfg["tab"]["tpl_conf"]} AS d ON b.idtplcfg = d.idtplcfg " .
        "WHERE " .
        "   a.idclient = {$client} " .
        "ORDER BY b.idlang {$sOrder}, c.idtree ASC ";
$db->query($sql);
if ($client == 0) {
    $client = '';
}


$sExpandList = $currentuser->getUserProperty("system", "con_cat_expandstate");
if ($sExpandList != '') {
    $conexpandedList = unserialize($currentuser->getUserProperty("system", "con_cat_expandstate"));
} else {
    $conexpandedList = [];
}

if (!is_array($conexpandedList)) {
    $conexpandedList = [];
}

if (!isset($conexpandedList[$client]) || !is_array($conexpandedList[$client])) {
    $conexpandedList[$client] = [];
}

$navigationTree = [];
$aWholelist     = [];

while ($db->nextRecord()) {
    if (!isset($navigationTree[$db->f('parentid')][$db->f('idcat')]) && ($db->f('idlang') == $lang || $db->f('idlang') == $syncoptions)) {
        if (in_array($db->f('idcat'), $conexpandedList[$client])) {
            $collapsed = false;
        } else {
            $collapsed = true;
        }
        if ($perm->have_perm_item("con", $db->f('idcat'))) {
            $forcedisplay = 1;
        } else {
            $forcedisplay = 0;
        }
        if ($idcat == $db->f('idcat')) {
            $active = true;
        } else {
            $active = false;
        }
        $navigationTree[$db->f('parentid')][$db->f('idcat')] = [
            'idcat'        => $db->f('idcat'),
            'preid'        => $db->f('preid'),
            'postid'       => $db->f('postid'),
            'visible'      => $db->f('visible'),
            'online'       => $db->f('visible'),
            'public'       => $db->f('public'),
            'name'         => $db->f('name'),
            'langPopup'    => $db->f('langPopup'),
            'idlang'       => $db->f('idlang'),
            'idtpl'        => $db->f('idtpl'),
            'collapsed'    => $collapsed,
            'forcedisplay' => $forcedisplay,
            'active'       => $active,
            'islast'       => false,
            'articles'     => !empty($aIsArticles[$db->f("idcat")]) ? $aIsArticles[$db->f("idcat")] : false,
            'level'        => $db->f('level'),
        ];
        if ($aStartOnlineArticles[$db->f('idcat')]['is_start'] ?? false) {
            $navigationTree[$db->f('parentid')][$db->f('idcat')]['no_start'] = false;
        } else {
            $navigationTree[$db->f('parentid')][$db->f('idcat')]['no_start'] = true;
        }
        if ($aStartOnlineArticles[$db->f('idcat')]['is_online'] ?? false) {
            $navigationTree[$db->f('parentid')][$db->f('idcat')]['no_online'] = false;
        } else {
            $navigationTree[$db->f('parentid')][$db->f('idcat')]['no_online'] = true;
        }
    }
}

cDebug::out(print_r($navigationTree, true));

if (isset($navigationTree[0]) && count($navigationTree[0])) {
    $sCategories = showTree(0, $aWholelist);
}

$tpl->set('s', 'CATS', $sCategories ?? '');
$tpl->set('s', 'AREA', $area);
$tpl->set('s', 'DIRECTION', 'dir="' . langGetTextDirection($lang) . '"');
$tpl->set('s', 'SYNCOPTIONS', $syncoptions);
$tpl->set('s', 'AJAXURL',  cRegistry::getBackendUrl() . 'ajaxmain.php');
$tpl->set('s', 'WHOLELIST', implode(', ', $aWholelist));
$tpl->set('s', 'EXPANDEDLIST', implode(', ', $conexpandedList[$client]));

$tpl->generate($cfg['path']['templates'] . $cfg['templates']['con_str_overview']);
