<?php
/**
 * This file contains the stat collection and item class.
 *
 * @package          Core
 * @subpackage       GenericDB_Model
 * @version          SVN Revision $Rev:$
 *
 * @author           Murat Purc <murat@purc.de>
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Statistic collection
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiStatCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['stat'], 'idstat');
        $this->_setItemClass('cApiStat');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiCategoryArticleCollection');
        $this->_setJoinPartner('cApiLanguageCollection');
        $this->_setJoinPartner('cApiClientCollection');
    }

    /**
     * Tracks a visit.
     * Increments a existing entry or creates a new one.
     *
     * @param int $iIdCatArt
     * @param int $iIdLang
     * @param int $iIdClient
     */
    public function trackVisit($iIdCatArt, $iIdLang, $iIdClient) {
        $oStat = $this->fetchByCatArtAndLang($iIdCatArt, $iIdLang);
        if (is_object($oStat)) {
            $oStat->increment();
        } else {
            $this->create($iIdCatArt, $iIdLang, $iIdClient);
        }
    }

    /**
     * Creates a stat entry.
     *
     * @param int $iIdCatArt
     * @param int $iIdLang
     * @param int $iIdClient
     * @param int $iVisited
     * @return cApiStat
     */
    public function create($iIdCatArt, $iIdLang, $iIdClient, $iVisited = 1) {
        $oItem = parent::createNewItem();

        $oItem->set('visited', (int) $iVisited);
        $oItem->set('idcatart', (int) $iIdCatArt);
        $oItem->set('idlang', (int) $iIdLang);
        $oItem->set('idclient', (int) $iIdClient);
        $oItem->store();

        return $oItem;
    }

    /**
     * Returns a stat entry by category article and language.
     *
     * @param int $iIdCatArt
     * @param int $iIdLang
     * @return cApiStat|null
     */
    public function fetchByCatArtAndLang($iIdCatArt, $iIdLang) {
        $this->select('idcatart=' . (int) $iIdCatArt . ' AND idlang=' . (int) $iIdLang);
        return $this->next();
    }

    /**
     * Deletes statistics entries by category article id and language id.
     *
     * @param int $idcatart
     * @param int $idlang
     * @return int Number of deleted items
     */
    public function deleteByCategoryArticleAndLanguage($idcatart, $idlang) {
        $where = 'idcatart = ' . (int) $idcatart . ' AND idlang = ' . (int) $idlang;
        return $this->deleteByWhereClause($where);
    }

}

/**
 * Statistic item
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiStat extends Item {

    /**
     * Constructor Function
     *
     * @param mixed $mId Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['stat'], 'idstat');
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    public function increment() {
        $this->set('visited', $this->get('visited') + 1);
        $this->store();
    }

}