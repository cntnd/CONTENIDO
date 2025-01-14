<?php

/**
 * This file contains Plugin Manager Relations recipient class.
 *
 * @package    Plugin
 * @subpackage PluginManager
 * @author     Frederic Schneider
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Plugin Manager Relations recipient class.
 *
 * @package    Plugin
 * @subpackage PluginManager
 * @author     Frederic Schneider
 * @method PimPluginRelations createNewItem
 * @method PimPluginRelations|bool next
 */
class PimPluginRelationsCollection extends ItemCollection {
    /**
     * Constructor Function
     *
     * @throws cInvalidArgumentException
     */
    public function __construct() {
        parent::__construct(cRegistry::getDbTableName('plugins_rel'), 'idpluginrelation');
        $this->_setItemClass('PimPluginRelations');
    }

    /**
     * Create a new plugin
     *
     * @param $idItem   int Is equivalent to idarea or idnavm
     * @param $idPlugin int Plugin Id
     * @param $type     string Relation to tables *_area and *_nav_main
     *
     * @return Item
     *
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public function create($idItem, $idPlugin, $type) {

        // create a new entry
        $item = $this->createNewItem();
        $item->set('iditem', $idItem);
        $item->set('idplugin', $idPlugin);
        $item->set('type', $type);

        $item->store();

        return $item;
    }

}

/**
 * Single Plugin Manager Relations Item
 */
class PimPluginRelations extends Item {

    /**
     * @var string Error storage
     */
    protected $_sError;

    /**
     * Constructor Function
     *
     * @param  mixed $id Specifies the id of item to load
     *
     * @throws cDbException
     * @throws cException
     */
    public function __construct($id = false) {
        parent::__construct(cRegistry::getDbTableName('plugins_rel'), 'idpluginrelation');
        $this->_sError = '';
        if ($id !== false) {
            $this->loadByPrimaryKey($id);
        }
    }

    /**
     * User-defined setter for pim relations fields.
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $bSafe Flag to run defined inFilter on passed value
     *
     * @return bool
     */
    public function setField($name, $value, $bSafe = true) {
        switch ($name) {
            case 'idplugin':
            case 'iditem':
                $value = cSecurity::toInteger($value);
                break;
        }

        return parent::setField($name, $value, $bSafe);
    }

}
