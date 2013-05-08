<?php
/**
 * This file contains the nav sub collection and item class.
 *
 * @package          Core
 * @subpackage       GenericDB_Model
 * @version          SVN Revision $Rev:$
 *
 * @author           Frederic Schneider
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * File collection
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiNavSubCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['nav_sub'], 'idnavs');
        $this->_setItemClass('cApiNavSub');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiNavMainCollection');
        $this->_setJoinPartner('cApiAreaCollection');
    }

    public function create($navm, $area, $level, $location, $online = '1') {
        $item = parent::createNewItem();

        if (is_string($area)) {
            $c = new cApiArea();
            $c->loadBy('name', $area);

            if ($c->isLoaded()) {
                $area = $c->get('idarea');
            } else {
                $area = 0;
                cWarning(__FILE__, __LINE__, "Could not resolve area [$area] passed to method [create], assuming 0");
            }
        }

        $navm = cSecurity::toInteger($navm);
        $level = cSecurity::toInteger($level);
        $location = cSecurity::escapeString($location);
        $online = cSecurity::toInteger($online);

        $item->set('idnavm', $navm);
        $item->set('idarea', $area);
        $item->set('level', $level);
        $item->set('location', $location);
        $item->set('online', $online);

        $item->store();

        return ($item);
    }

}

/**
 * NavMain item
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiNavSub extends Item {

    /**
     * Constructor Function
     *
     * @param mixed $mId Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['nav_sub'], 'idnavs');
        $this->setFilters(array(
            'addslashes'
        ), array(
            'stripslashes'
        ));
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}