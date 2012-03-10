<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Path resolving functions
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Includes
 * @version    1.1.9
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 *
 * {@internal
 *   created unknown
 *   modified 2006-12-14, init array $results in fct prResolvePathViaURLNames, prResolvePathViaCategoryNames, return type is now integer
 *   modified 2008-06-26, Frederic Schneider, add security fix
 * 	 modified 2008-08-11, Bilal Arslan, Change prResolvePathViaCategoryNames function for take current path language id!
 * 	 modified 2008-11-11, Andreas Lindner, Change prResolvePathViaCategoryNames, suppress change of current language if an url path
 * 	 						is found in current and at least one more language
 *   modified 2009-10-23, Murat Purc, removed deprecated function (PHP 5.3 ready) and formatting
 *
 *   $Id$:
 * }}
 *
 */

if(!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * prResolvePathViaURLNames: Resolves a path using some fuzzy logic.
 *
 * Warning: If you use this function, try to pass a "good" path. This
 *          function doesn't guarantee that the matches are logically
 *          best-matches.
 *
 * This function operates on the category aliases. It compares the given path with the urlpaths generated by function
 * prCreateURLNameLocationString() based on category aliases.
 *
 * @param $path string Path to resolve
 * @return integer Closest matching category ID (idcat)
 */
function prResolvePathViaURLNames($path) {
    $handle = startTiming("prResolvePathViaURLNames", array ($path));

    global $cfg, $lang, $client;

    /* Initialize variables */
    $db = new DB_Contenido;
    $categories = array();
    $results = array();

    /* Pre-process path */
    $path = strtolower(str_replace(" ", "", $path));

    if ($cfg["pathresolve_heapcache"] == true) {
        $pathresolve_tablename = $cfg["sql"]["sqlprefix"]."_pathresolve_cache";

        $sql = "SHOW TABLES LIKE '".Contenido_Security::escapeDB($pathresolve_tablename, $db)."'";
        $db->query($sql);

        if (!$db->next_record()) {
            /**
             * @TODO: Externalize table creation
             *
             * Important: This is really a hack! Don't use pathresolve_heapcache if you are
             * not sure what it does.
             * @TODO: pls insert to this create table statetment MAX_ROWS.
             */
            $sql = 'CREATE TABLE `'.Contenido_Security::escapeDB($pathresolve_tablename, $db).'` (
                            `idpathresolvecache` INT( 10 ) NOT NULL AUTO_INCREMENT,
                            `path` VARCHAR( 255 ) NOT NULL ,
                            `idcat` INT( 10 ) NOT NULL ,
                            `idlang` INT( 10 ) NOT NULL ,
                            `lastcached` INT(10) NOT NULL,
                             PRIMARY KEY  (`idpathresolvecache`)
                            ) ENGINE = HEAP;';

            $db->query($sql);
        }

        $sql = "SELECT idpathresolvecache, idcat, lastcached FROM %s WHERE path LIKE '%s' AND idlang='%s' ORDER BY lastcached DESC LIMIT 1";
        $db->query(sprintf($sql, Contenido_Security::escapeDB($pathresolve_tablename, $db), Contenido_Security::escapeDB($path, $db), Contenido_Security::toInteger($lang)));

        if ($db->next_record()) {
            if (isset ($cfg["pathresolve_heapcache_time"])) {
                $iCacheTime = $cfg["pathresolve_heapcache_time"];
            } else {
                $iCacheTime = 60 * 60 * 24;
            }

            $tmp_idcat = $db->f("idcat");

            if ($db->f("lastcached") + $iCacheTime < time()) {
                $sql = "DELETE FROM %s WHERE idpathresolvecache = '%s'";
                $db->query(sprintf($sql, Contenido_Security::escapeDB($pathresolve_tablename, $db), Contenido_Security::toInteger($db->f("idpathresolvecache"))));
            } else {
                return $db->f("idcat");
            }
        }
    }

    /* Fetch all category names, build path strings */
//	change the where statement for get all languages
    $sql = "SELECT * FROM ".$cfg["tab"]["cat_tree"]." AS A, ".$cfg["tab"]["cat"]." AS B, ".$cfg["tab"]["cat_lang"]." AS C WHERE A.idcat=B.idcat AND B.idcat=C.idcat AND C.idlang='".Contenido_Security::toInteger($lang)."'
            AND C.visible = 1 AND B.idclient='".Contenido_Security::toInteger($client)."' ORDER BY A.idtree";
    $db->query($sql);

    $catpath = array ();
    while ($db->next_record()) {
        $cat_str = "";
        prCreateURLNameLocationString($db->f("idcat"), "/", $cat_str, false, "", 0, 0, true, true);

        /* Store path */
        $catpath[$db->f("idcat")] = $cat_str;
        $catnames[$db->f("idcat")] = $db->f("name");
        $catlevels[$db->f("idcat")] = $db->f("level");
    }

    /* Compare strings using the similar_text algorythm */
    $percent = 0;
    foreach ($catpath as $key => $value) {
        $value = strtolower(str_replace(" ", "", $value));

        similar_text($value, $path, $percent);

        $firstpath = strpos($value, "/");

        if ($firstpath !== 0) {
            $xpath = substr($value, $firstpath);
            $ypath = substr($path, 0, strlen($path) - 1);
            if ($xpath == $ypath) {
                $results[$key] = 100;
            } else {
                $results[$key] = $percent;
            }
        } else {
            $results[$key] = $percent;
        }

    }

    arsort($results, SORT_NUMERIC);
    reset($results);

    endAndLogTiming($handle);

    if ($cfg["pathresolve_heapcache"] == true) {
        //$nid = $db->nextid($pathresolve_tablename);

        $sql = "INSERT INTO %s SET  path='%s', idcat='%s', idlang='%s', lastcached=%s";
        $db->query(sprintf($sql, Contenido_Security::toInteger($pathresolve_tablename), Contenido_Security::escapeDB($path, $db), Contenido_Security::toInteger(key($results)), Contenido_Security::toInteger($lang), time()));
    }

    return (int) key($results);
}

/**
 * prResolvePathViaCategoryNames: Resolves a path using some fuzzy logic.
 *
 * Warning: If you use this function, try to pass a "good" path. This
 *          function doesn't guarantee that the matches are logically
 *          best-matches.
 *
 * This function operates on the actual category names.
 *
 * @param $path string Path to resolve
 * @return integer Closest matching category ID (idcat)
 */
function prResolvePathViaCategoryNames($path, &$iLangCheck) {
    $handle = startTiming("prResolvePathViaCategoryNames", array ($path));

    global $cfg, $lang, $client;

    /* Initialize variables */
    $db = new DB_Contenido;
    $categories = array ();
    $results = array();
    $iLangCheckOrg = $iLangCheck;

    /* Added since 2008-08 from Bilal Arslan */
//	To take only path body
    if (preg_match('/^\/(.*)\/$/', $path, $aResults)) {
        $aResult = explode("/", $aResults[1]);
    } elseif (preg_match('/^\/(.*)$/', $path, $aResults)) {
        $aResult = explode("/", $aResults[1]);
    } else {
        $aResults[1] = $path;
    }

    $aResults[1] = strtolower(preg_replace('/-/', ' ', $aResults[1]));

//	Init to Compare, save path in array
    $aPathsToCompare = explode("/", $aResults[1]);
    $iCountPath = count($aPathsToCompare);

//  init lang id
    $iLangCheck=0;

    /* Pre-process path */
    $path = strtolower(str_replace(" ", "", $path));

    /* Fetch all category names, build path strings */
//	change the where statement for get all languages
    $sql = "SELECT * FROM ".$cfg["tab"]["cat_tree"]." AS A, ".$cfg["tab"]["cat"]." AS B, ".$cfg["tab"]["cat_lang"]." AS C WHERE A.idcat=B.idcat AND B.idcat=C.idcat
            AND C.visible = 1 AND B.idclient='".Contenido_Security::toInteger($client)."' ORDER BY A.idtree";
    $db->query($sql);

    $catpath = array ();
    $arrLangMatches = array();

    while ($db->next_record()) {
        $cat_str = "";
        $aTemp = "";
        $iFor = 0;
        $bLang = false;

//		$level is changeless 0!!!
        conCreateLocationString($db->f("idcat"), "/", $cat_str, false, '', 0, $db->f("idlang"));
        /* Store path */
        $catpath[$db->f("idcat")] =  $cat_str;
        $catnames[$db->f("idcat")] = $db->f("name");
        $catlevels[$db->f("idcat")] = $db->f("level");

//		Init variables for take a language id
        $aTemp =  strtolower($cat_str);
        $aDBToCompare =  explode("/", $aTemp);
        $iCountDB = count($aDBToCompare);
        $iCountDBFor = $iCountDB - 1;
//		take min. count of two arrays
        ($iCountDB > $iCountPath) ? $iFor = $iCountPath : $iFor = $iCountDB;
        $iCountM = $iFor-1;

        for ($i=0; $i<$iFor; $i++) {
            if ($aPathsToCompare[$iCountM] == $aDBToCompare[$iCountDBFor]) {
                $bLang	= true;
            } else {
                $bLang = false;
            }
            $iCountM--;
            $iCountDBFor--;
//			compare, only if current element is lastone and we are in true path
            if($i == $iFor-1 && $bLang) {
                $iLangCheck = $db->f("idlang");
                $arrLangMatches[] = $iLangCheck;
            }
        }

    }

    #Suppress wrongly language change if url name can be found in current language
    if ($iLangCheckOrg == 0) {
        if (in_array($lang, $arrLangMatches)) {
            $iLangCheck = $lang;
        }
    }

    /* Compare strings using the similar_text algorythm */
    $percent = 0;
    foreach ($catpath as $key => $value) {
        $value = strtolower(str_replace(" ", "", $value));

        similar_text($value, $path, $percent);

        $results[$key] = $percent;
    }

    foreach ($catnames as $key => $value) {
        $value = strtolower(str_replace(" ", "", $value));
        similar_text($value, $path, $percent);

        /* Apply weight */
        $percent = $percent * $catlevels[$key];

        if ($results[$key] > $percent) {
            $results[$key] = $percent;
        }
    }

    arsort($results, SORT_NUMERIC);
    reset($results);

    endAndLogTiming($handle);
    return (int) key($results);
}

/**
 * Recursive function to create an URL name location string
 *
 * @param int $idcat ID of the starting category
 * @param string $seperator Seperation string
 * @param string $cat_str Category location string (by reference)
 * @param boolean $makeLink create location string with links
 * @param string $linkClass stylesheet class for the links
 * @param integer first navigation level location string should be printed out (first level = 0!!)
 *
 * @return string location string
 *
 * @author Jan Lengowski <jan.lengowski@4fb.de>
 * @author Marco Jahn <marco.jahn@4fb.de>
 *
 * @copyright four for business AG <www.4fb.de>
 */
function prCreateURLNameLocationString($idcat, $seperator, & $cat_str, $makeLink = false, $linkClass = "",
    $firstTreeElementToUse = 0, $uselang = 0, $final = true, $usecache = false) {
    global $cfg, $client, $cfgClient, $lang, $sess, $_URLlocationStringCache;

    if ($final == true) {
        $cat_str = "";
    }

    if ($idcat == 0) {
        $cat_str = "Lost and Found";
        return;
    }

    if ($uselang == 0) {
        $uselang = $lang;
    }

    if ($final == true && $usecache == true) {
        if (!is_array($_URLlocationStringCache)) {
            if (file_exists($cfgClient[$client]["path"]["frontend"]."cache/locationstring-url-cache-$uselang.txt")) {
                $_URLlocationStringCache = unserialize(file_get_contents($cfgClient[$client]["path"]["frontend"]."cache/locationstring-url-cache-$uselang.txt"));
            } else {
                $_URLlocationStringCache = array ();
            }
        }

        if (array_key_exists($idcat, $_URLlocationStringCache)) {
            if ($_URLlocationStringCache[$idcat]["expires"] > time()) {
                $cat_str = $_URLlocationStringCache[$idcat]["name"];
                return;
            }
        }
    }

    $db = new DB_Contenido;

    $sql = "SELECT
                    a.urlname AS urlname,
                    a.name	AS name,
                    a.idcat AS idcat,
                    b.parentid AS parentid,
                    c.level as level
                FROM
                    ".$cfg["tab"]["cat_lang"]." AS a,
                    ".$cfg["tab"]["cat"]." AS b,
                    ".$cfg["tab"]["cat_tree"]." AS c
                WHERE
                    a.idlang    = '".Contenido_Security::toInteger($uselang)."' AND
                    b.idclient  = '".Contenido_Security::toInteger($client)."' AND
                    b.idcat     = '".Contenido_Security::toInteger($idcat)."' AND
                    a.idcat     = b.idcat AND
                    c.idcat = b.idcat";

    $db->query($sql);
    $db->next_record();

    if ($db->f("level") >= $firstTreeElementToUse) {
        $name = $db->f("urlname");

        if (trim($name) == "") {
            $name = $db->f("name");
        }

        $parentid = $db->f("parentid");

        //create link
        if ($makeLink == true) {
            $linkUrl = $sess->url("front_content.php?idcat=$idcat");
            $name = '<a href="'.$linkUrl.'" class="'.$linkClass.'">'.$name.'</a>';
        }

        $tmp_cat_str = $name.$seperator.$cat_str;
        $cat_str = $tmp_cat_str;
    }

    if ($parentid != 0) {
        prCreateURLNameLocationString($parentid, $seperator, $cat_str, $makeLink, $linkClass, $firstTreeElementToUse, $uselang, false, $usecache);
    } else {
        $sep_length = strlen($seperator);
        $str_length = strlen($cat_str);
        $tmp_length = $str_length - $sep_length;
        $cat_str = substr($cat_str, 0, $tmp_length);
    }

    if ($final == true && $usecache == true) {
        $_URLlocationStringCache[$idcat]["name"] = $cat_str;
        $_URLlocationStringCache[$idcat]["expires"] = time() + 3600;

        if (is_writable($cfgClient[$client]["path"]["frontend"]."cache/") || (strtolower(substr(PHP_OS, 0, 3)) == "win")) {
            file_put_contents($cfgClient[$client]["path"]["frontend"]."cache/locationstring-url-cache-$uselang.txt", serialize($_URLlocationStringCache));
        }
    }
}
?>
