<?php

/**
 * This file contains the maillog success collection and item class.
 *
 * @package    Core
 * @subpackage GenericDB_Model
 * @author     Simon Sprankel
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Mail log success collection
 *
 * @package    Core
 * @subpackage GenericDB_Model
 * @method cApiMailLogSuccess createNewItem
 * @method cApiMailLogSuccess|bool next
 */
class cApiMailLogSuccessCollection extends ItemCollection {
    /**
     * Constructor to create an instance of this class.
     *
     * @throws cInvalidArgumentException
     */
    public function __construct() {
        parent::__construct(cRegistry::getDbTableName('mail_log_success'), 'idmailsuccess');
        $this->_setItemClass('cApiMailLogSuccess');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiMailLogCollection');
    }

    /**
     * Creates a new mail log success entry with the given data.
     *
     * @param int    $idmail
     * @param array  $recipient
     * @param bool   $success
     * @param string $exception
     *
     * @return cApiMailLogSuccess
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public function create($idmail, $recipient, $success, $exception) {
        $item = $this->createNewItem();

        $item->set('idmail', $idmail);
        $item->set('recipient', json_encode($recipient));
        $item->set('success', $success);
        $item->set('exception', $exception);

        $item->store();

        return $item;
    }
}

/**
 * Mail log success item
 *
 * @package    Core
 * @subpackage GenericDB_Model
 */
class cApiMailLogSuccess extends Item
{
    /**
     * Constructor
     *
     * @param mixed $mId
     *
     * @throws cDbException
     * @throws cException
     */
    public function __construct($mId = false) {
        parent::__construct(cRegistry::getDbTableName('mail_log_success'), 'idmailsuccess');
        $this->setFilters([], []);
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
