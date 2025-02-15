<?php

/**
 *
 * @package    Plugin
 * @subpackage SIWECOS
 * @author     Fulai Zhang <fulai.zhang@4fb.de>
 * @copyright  four for business AG
 * @link       https://www.4fb.de
 */

// assert CONTENIDO framework
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * SIWECOS form item collection class.
 * It's a kind of model.
 *
 * @author     Fulai Zhang <fulai.zhang@4fb.de>
 * @method SIWECOS createNewItem
 * @method SIWECOS|bool next
 */
class SIWECOSCollection extends ItemCollection
{
    /**
     * SIWECOSCollection constructor.
     *
     * @param bool $where
     *
     * @throws cDbException
     * @throws cInvalidArgumentException
     */
    public function __construct($where = false)
    {
        parent::__construct('con_pi_siwecos', 'idsiwecos');
        $this->_setItemClass('SIWECOS');
        if (false !== $where) {
            $this->select($where);
        }
    }

    /**
     * Get forms of given client in given language.
     *
     * @param $client
     * @param $lang
     *
     * @return array
     * @throws SIWECOSException
     * @throws cDbException
     * @throws cException
     */
    public static function getByClientAndLang($client, $lang)
    {
        if (0 >= cSecurity::toInteger($client)) {
            $msg = i18n('ERR_MISSING_CLIENT', 'siwecos');
            throw new SIWECOSException($msg);
        }

        if (0 >= cSecurity::toInteger($lang)) {
            $msg = i18n('ERR_MISSING_LANG', 'siwecos');
            throw new SIWECOSException($msg);
        }

        return self::_getBy($client, $lang);
    }

    /**
     * Get forms according to given params.
     *
     * @param $client
     * @param $lang
     *
     * @return array
     * @throws cDbException
     */
    private static function _getBy($client, $lang)
    {
        global $idsiwecos;

        if ($idsiwecos) {
            $str = "AND `idsiwecos` = " . cSecurity::toInteger($idsiwecos);
        } else {
            $str = '';
        }

        $db = cRegistry::getDb();
        $db->query(
            "SELECT *
            FROM `" . cRegistry::getDbTableName('siwecos') . "`
            WHERE 
                `idclient` = " . cSecurity::toInteger($client) . "
                AND `idlang` = " . cSecurity::toInteger($lang) . "
                " . $str . "
            ;"
        );

        $forms = [];
        while ($db->nextRecord()) {
            $forms[$db->f('idsiwecos')]['idsiwecos']   = $db->f('idsiwecos');
            $forms[$db->f('idsiwecos')]['domain']      = $db->f('domain');
            $forms[$db->f('idsiwecos')]['email']       = $db->f('email');
            $forms[$db->f('idsiwecos')]['userToken']   = $db->f('userToken');
            $forms[$db->f('idsiwecos')]['domainToken'] = $db->f('domainToken');
            $forms[$db->f('idsiwecos')]['dangerLevel'] = $db->f('dangerLevel');
            $forms[$db->f('idsiwecos')]['author']      = $db->f('author');
            $forms[$db->f('idsiwecos')]['created']     = $db->f('created');
        }

        return $forms;
    }
}

/**
 * Class SIWECOS
 */
class SIWECOS extends Item
{
    /**
     * name of this plugin
     *
     * @var string
     */
    private static $_name = 'siwecos';

    /**
     * SIWECOS constructor.
     *
     * @param bool $id
     *
     * @throws cDbException
     * @throws cException
     */
    public function __construct($id = false)
    {
        parent::__construct('con_pi_siwecos', 'idsiwecos');
        $this->setFilters([], []);
        if (false !== $id) {
            $this->loadByPrimaryKey($id);
        }
    }

    /**
     * get pluginsname
     *
     * @return string
     */
    public static function getName()
    {
        return self::$_name;
    }

    /**
     * @param Exception $e
     *
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public static function logException(Exception $e)
    {
        if (getSystemProperty('debug', 'debug_for_plugins') == 'true') {
            $cfg         = cRegistry::getConfig();
            $destination = $cfg['path']['contenido_logs'] . 'errorlog.txt';
            $writer      = cLogWriter::factory('file', ['destination' => $destination]);
            $log         = new cLog($writer);
            $log->err($e->getMessage());
            $log->err($e->getTraceAsString());
        }
    }

    /**
     * Creates a notification widget in order to display an exception message in
     * backend.
     *
     * @param Exception $e
     *
     * @return string
     */
    public static function notifyException(Exception $e)
    {
        $cGuiNotification = new cGuiNotification();
        $level            = cGuiNotification::LEVEL_ERROR;
        $message          = $e->getMessage();

        return $cGuiNotification->returnNotification($level, $message);
    }

    /**
     * Deletes this form with all its fields and stored data.
     * The forms data table is also dropped.
     *
     * @throws SIWECOSException
     * @throws cDbException
     * @throws cException
     */
    public function delete()
    {
        global $idsiwecos;

        $oSIWECOSCollection = new SIWECOSCollection();
        $success = $oSIWECOSCollection->delete($idsiwecos);

        if (!$success) {
            $msg = i18n('ERR_DELETE_ENTITY', 'siwecos');
            throw new SIWECOSException($msg);
        }
    }
}

/**
 * Base class for all SIWECOS related exceptions.
 *
 * @author fulai zhang <fulai.zhang@4fb.de>
 */
class SIWECOSException extends cException
{
}
