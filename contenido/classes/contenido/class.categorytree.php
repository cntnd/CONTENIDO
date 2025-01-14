<?php

/**
 * This file contains the category tree collection and item class.
 *
 * @package    Core
 * @subpackage GenericDB_Model
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Category tree collection
 *
 * @package    Core
 * @subpackage GenericDB_Model
 * @method cApiCategoryTree createNewItem
 * @method cApiCategoryTree|bool next
 */
class cApiCategoryTreeCollection extends ItemCollection {
    /**
     * Constructor to create an instance of this class.
     *
     * @param bool $select [optional]
     *                     where clause to use for selection (see ItemCollection::select())
     *
     * @throws cDbException
     * @throws cInvalidArgumentException
     */
    public function __construct($select = false) {
        parent::__construct(cRegistry::getDbTableName('cat_tree'), 'idtree');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiCategoryCollection');

        $this->_setItemClass('cApiCategoryTree');
        if ($select !== false) {
            $this->select($select);
        }
    }

    /**
     * Returns category tree structure by selecting the data from several tables
     * ().
     *
     * @param int $client
     *         Client id
     * @param int $lang
     *         Language id
     *
     * @return array
     *         Category tree structure as follows:
     *         <pre>
     *         $arr[n] (int) idtree value
     *         $arr[n]['idcat'] (int)
     *         $arr[n]['level'] (int)
     *         $arr[n]['idtplcfg'] (int)
     *         $arr[n]['visible'] (int)
     *         $arr[n]['name'] (string)
     *         $arr[n]['public'] (int)
     *         $arr[n]['urlname'] (string)
     *         $arr[n]['is_start'] (int)
     *         </pre>
     *
     * @throws cDbException
     */
    function getCategoryTreeStructureByClientIdAndLanguageId($client, $lang) {
        $aCatTree = [];

        $sql = 'SELECT * FROM `:cat_tree` AS A, `:cat` AS B, `:cat_lang` AS C ' . 'WHERE A.idcat = B.idcat AND B.idcat = C.idcat AND C.idlang = :idlang AND idclient = :idclient ' . 'ORDER BY idtree';

        $sql = $this->db->prepare(
            $sql,
            [
                'cat_tree' => $this->table,
                'cat'      => cRegistry::getDbTableName('cat'),
                'cat_lang' => cRegistry::getDbTableName('cat_lang'),
                'idlang'   => (int)$lang,
                'idclient' => (int)$client,
            ]
        );
        $this->db->query($sql);

        while ($this->db->nextRecord()) {
            $aCatTree[$this->db->f('idtree')] = [
                'idcat'    => $this->db->f('idcat'),
                'level'    => $this->db->f('level'),
                'idtplcfg' => $this->db->f('idtplcfg'),
                'visible'  => $this->db->f('visible'),
                'name'     => $this->db->f('name'),
                'public'   => $this->db->f('public'),
                'urlname'  => $this->db->f('urlname'),
                'is_start' => $this->db->f('is_start'),
            ];
        }

        return $aCatTree;
    }
}

/**
 * Category tree item
 *
 * @package    Core
 * @subpackage GenericDB_Model
 */
class cApiCategoryTree extends Item {
    /**
     * Constructor to create an instance of this class.
     *
     * @param mixed $mId [optional]
     *                   Specifies the ID of item to load
     *
     * @throws cDbException
     * @throws cException
     */
    public function __construct($mId = false) {
        parent::__construct(cRegistry::getDbTableName('cat_tree'), 'idtree');
        $this->setFilters([], []);
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
