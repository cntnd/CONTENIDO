<?php

/**
 * CONTENIDO Chain.
 * Generate metatags for current article if they are not set in article
 * properties
 *
 * @package    Core
 * @subpackage Chain
 * @author     Andreas Lindner
 * @author     Unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

cInclude('plugins', 'repository/keyword_density.php');

/**
 *
 * @param array $metatags
 *
 * @return array
 *
 * @throws cDbException|cException
 */
function cecCreateMetatags($metatags) {
    // (Re)build metatags

    $db = cRegistry::getDb();
    $cfg = cRegistry::getConfig();
    $lang = cSecurity::toInteger(cRegistry::getLanguageId());
    $idart = cSecurity::toInteger(cRegistry::getArticleId());
    $idartlang = cSecurity::toInteger(cRegistry::getArticleLanguageId());

    // Get encoding
    $oLang = new cApiLanguage($lang);
    if ($oLang->get('encoding')) {
        $sEncoding = cString::toUpperCase($oLang->get('encoding'));
    } else {
        $sEncoding = 'ISO-8859-1';
    }

    // Get idcat of homepage
    $sql = "SELECT a.idcat
        FROM
            " . $cfg['tab']['cat_tree'] . " AS a,
            " . $cfg['tab']['cat_lang'] . " AS b
        WHERE
            (a.idcat = b.idcat) AND
            (b.visible = 1) AND
            (b.idlang = " . $lang . ")
        ORDER BY a.idtree LIMIT 1";

    $db->query($sql);

    $idCatHomepage = $db->nextRecord() ? cSecurity::toInteger($db->f('idcat')) : 0;

    $availableTags = conGetAvailableMetaTagTypes();

    // Get first headline and first text for current article
    // @todo use this cApiArticleLanguage instance in code below, instead of
    // creating it again and again!
    $oArt = new cApiArticleLanguage();
    $oArt->loadByArticleAndLanguageId($idart, $lang);

    // Set idartlang, if not set
    if ($idartlang == '') {
        $idartlang = $oArt->getField('idartlang');
    }

    $arrHead1 = $oArt->getContent('htmlhead');
    $arrHead2 = $oArt->getContent('head');

    if (!is_array($arrHead1)) {
        $arrHead1 = [];
    }

    if (!is_array($arrHead2)) {
        $arrHead2 = [];
    }

    $arrHeadlines = array_merge($arrHead1, $arrHead2);
    $sHeadline = '';

    foreach ($arrHeadlines as $key => $value) {
        if ($value != '') {
            $sHeadline = $value;
            break;
        }
    }

    $sHeadline = strip_tags($sHeadline);
    $sHeadline = cString::getPartOfString(str_replace("\r\n", ' ', $sHeadline), 0, 100);

    $arrText1 = $oArt->getContent('html');
    $arrText2 = $oArt->getContent('text');

    if (!is_array($arrText1)) {
        $arrText1 = [];
    }

    if (!is_array($arrText2)) {
        $arrText2 = [];
    }

    $arrText = array_merge($arrText1, $arrText2);
    $sText = '';

    foreach ($arrText as $key => $value) {
        if ($value != '') {
            $sText = $value;
            break;
        }
    }

    $sText = strip_tags(urldecode($sText));
    $sText = keywordDensity('', $sText);

    // Get metatags for homepage
    $arrHomepageMetaTags = [];

    $sql = "SELECT `startidartlang` FROM `%s` WHERE `idcat` = %d AND `idlang` = %d";
    $db->query($sql, $cfg['tab']['cat_lang'], $idCatHomepage, $lang);

    if ($db->nextRecord()) {
        $iIdArtLangHomepage = cSecurity::toInteger($db->f('startidartlang'));

        // Get idart of homepage
        $sql = "SELECT `idart` FROM `%s` WHERE `idartlang` = %d";
        $db->query($sql,  $cfg['tab']['art_lang'], $iIdArtLangHomepage);
        $iIdArtHomepage = $db->nextRecord() ? cSecurity::toInteger($db->f('idart')) : 0;

        $t1 = $cfg['tab']['meta_tag'];
        $t2 = $cfg['tab']['meta_type'];

        $sql = "SELECT " . $t1 . ".metavalue," . $t2 . ".metatype FROM " . $t1 . " INNER JOIN " . $t2 . " ON " . $t1 . ".idmetatype = " . $t2 . ".idmetatype WHERE " . $t1 . ".idartlang =" . $iIdArtLangHomepage . " ORDER BY " . $t2 . ".metatype";

        $db->query($sql);

        while ($db->nextRecord()) {
            $arrHomepageMetaTags[$db->f('metatype')] = $db->f('metavalue');
        }

        $oArt = new cApiArticleLanguage();
        $oArt->loadByArticleAndLanguageId($iIdArtHomepage, $lang);

        $arrHomepageMetaTags['pagetitle'] = $oArt->getField('title');
    }

    // Cycle through all metatags
    foreach ($availableTags as $key => $value) {
        $metavalue = conGetMetaValue($idartlang, $key);

        if (cString::getStringLength($metavalue) == 0) {
            // Add values for metatags that don't have a value in the current
            // article
            switch (cString::toLowerCase($value['metatype'])) {
                case 'author':
                    // Build author metatag from name of last modifier
                    $oArt = new cApiArticleLanguage();
                    $oArt->loadByArticleAndLanguageId($idart, $lang);

                    $lastModifier = $oArt->getField('modifiedby');
                    $oUser = new cApiUser(md5($lastModifier));
                    $lastModifierName = $oUser->getRealName();

                    $iCheck = CheckIfMetaTagExists($metatags, 'author');
                    $metatags[$iCheck]['name'] = 'author';
                    $metatags[$iCheck]['content'] = $lastModifierName;

                    break;
                case 'description':
                    // Build description metatag from first headline on page
                    $iCheck = CheckIfMetaTagExists($metatags, 'description');
                    $metatags[$iCheck]['name'] = 'description';
                    $metatags[$iCheck]['content'] = $sHeadline;

                    break;
                case 'keywords':
                    $iCheck = CheckIfMetaTagExists($metatags, 'keywords');
                    $metatags[$iCheck]['name'] = 'keywords';
                    $metatags[$iCheck]['content'] = $sText;

                    break;
                case 'revisit-after':
                case 'robots':
                case 'expires':
                    // Build these 3 metatags from entries in homepage
                    $sCurrentTag = isset($value['name']) ? cString::toLowerCase($value['name']) : '';
                    $iCheck = CheckIfMetaTagExists($metatags, $sCurrentTag);
                    if($sCurrentTag != '' && $arrHomepageMetaTags[$sCurrentTag] != "") {
                        $metatags[$iCheck]['name'] = $sCurrentTag;
                        $metatags[$iCheck]['content'] = $arrHomepageMetaTags[$sCurrentTag];
                    }

                    break;
            }
        }
    }

    return $metatags;
}

/**
 * Checks if the metatag already exists inside the metatag list.
 *
 * @param array|mixed $arrMetatags
 *         List of metatags or not a list
 * @param string $sCheckForMetaTag
 *         The metatag to check
 * @return int
 *         Position of metatag inside the metatag list or the next available position
 */
function CheckIfMetaTagExists($arrMetatags, $sCheckForMetaTag) {
    if (!is_array($arrMetatags) || count($arrMetatags) == 0) {
        // metatag list ist not set or empty, return initial position
        return 0;
    }

    // loop through existing metatags and check against the list-item name
    foreach ($arrMetatags as $pos => $item) {
        if (isset($item['name']) && $item['name'] == $sCheckForMetaTag && $item['name'] != '') {
            // metatag found -> return the position
            return $pos;
        }
    }

    // metatag doesn't exists, return next position
    return count($arrMetatags);
}
