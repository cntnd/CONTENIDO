<?php

/**
 * This file contains the backend authentication handler class.
 *
 * @package    Core
 * @subpackage Authentication
 * @author     Dominik Ziegler
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * This class is the backend authentication handler for CONTENIDO.
 *
 * @package    Core
 * @subpackage Authentication
 */
class cAuthHandlerBackend extends cAuth {

    /**
     * Constructor to create an instance of this class.
     *
     * Automatically sets the lifetime of the authentication to the
     * configured value.
     */
    public function __construct() {
        $cfg = cRegistry::getConfig();
        $this->_lifetime = cSecurity::toInteger($cfg['backend']['timeout']);
        if ($this->_lifetime == 0) {
            $this->_lifetime = 15;
        }
    }

    /**
     * There is no pre authentication in backend.
     *
     * @inheritdoc
     */
    public function preAuthenticate() {
        return false;
    }

    /**
     * @deprecated [2023-02-05] Since 4.10.2, use {@see cAuthHandlerBackend::preAuthenticate} instead
     */
    public function preAuthorize() {
        return $this->preAuthenticate();
    }

    /**
     * Includes a file which displays the backend login form.
     * @inheritdoc
     * @throws cDbException|cException|cInvalidArgumentException
     */
    public function displayLoginForm() {
        // @TODO  We need a better solution for this.
        //        One idea could be to set the request/response type in
        //        global $cfg array instead of checking $_REQUEST['ajax']
        //        everywhere...
        if (!empty($_REQUEST['ajax'] ?? '')) {
            $oAjax = new cAjaxRequest();
            $sReturn = $oAjax->handle('authentication_fail');
            echo $sReturn;
        } else {
            include(cRegistry::getBackendPath() . 'main.loginform.php');
        }
    }

    /**
     * @inheritdoc
     * @throws cDbException|cException
     */
    public function validateCredentials() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $formtimestamp = $_POST['formtimestamp'] ?? '';

        // add slashes if they are not automatically added
        if (cRegistry::getConfigValue('simulate_magic_quotes') !== true) {
            // backward compatibility of passwords
            $password = addslashes($password);
            // avoid sql injection in query by username on cApiUserCollection select string
            $username = addslashes($username);
        }

        $groupPerm = [];

        if ($password == '') {
            return false;
        }

        if (($formtimestamp + (60 * 15)) < time()) {
            return false;
        }

        if (isset($username)) {
            $this->auth['uname'] = $username;
        } elseif ($this->_defaultNobody) {
            $uid = $this->auth['uname'] = $this->auth['uid'] = self::AUTH_UID_NOBODY;

            return $uid;
        }

        $uid = false;
        $perm = false;
        $pass = false;
        $salt = false;

        $userColl = new cApiUserCollection();
        $where = "username = '" . $username . "'";
        $where .= " AND (valid_from <= NOW() OR valid_from = '0000-00-00 00:00:00' OR valid_from is NULL)";
        $where .= " AND (valid_to >= NOW() OR valid_to = '0000-00-00 00:00:00' OR valid_to is NULL)";

        $maintenanceMode = getSystemProperty('maintenance', 'mode');
        if ($maintenanceMode == 'enabled') {
            $where .= " AND perms = 'sysadmin'";
        }

        $userColl->select($where);

        while (($item = $userColl->next()) !== false) {
            $uid = $item->get('user_id');
            $perm = $item->get('perms');
            // password is stored as a sha256 hash
            $pass = $item->get('password');
            $salt = $item->get("salt");
        }

        if (!$uid || hash("sha256", md5($password) . $salt) != $pass) {
            // No user found, sleep and exit
            sleep(2);

            return false;
        }

        if ($perm != '') {
            $groupPerm[] = $perm;
        }

        $groupColl = new cApiGroupCollection();
        $groups = $groupColl->fetchByUserID($uid);
        foreach ($groups as $group) {
            $groupPerm[] = $group->get('perms');
        }

        $perm = implode(',', $groupPerm);

        $this->auth['perm'] = $perm;

        return $uid;
    }

    /**
     * Log the successful authentication.
     *
     * Switches the globals $client & $lang to the first client/language for which the current user has permissions.
     * If a client/language combination is found the action "login" is added to the actionlog.
     * Eventually the global $saveLoginTime is set to true which will trigger the update of the user properties
     * "currentlogintime" and "lastlogintime" in mycontenido.
     *
     * @inheritdoc
     *
     * @throws cDbException|cException
     */
    public function logSuccessfulAuth() {
        global $client, $lang, $saveLoginTime;

        $perm = new cPermission();

        $saveLoginTime = false;

        // Find the first accessible client and language for the user
        $clientLangColl = new cApiClientLanguageCollection();
        $clientLangColl->select();

        $bFound = false;
        while ($bFound == false) {
            if (($item = $clientLangColl->next()) === false) {
                break;
            }

            $iTmpClient = $item->get('idclient');
            $iTmpLang = $item->get('idlang');

            if ($perm->have_perm_client_lang($iTmpClient, $iTmpLang)) {
                $client = $iTmpClient;
                $lang = $iTmpLang;
                $bFound = true;
            }
        }

        if (!isset($client) || !is_numeric($client) || !isset($lang) || !is_numeric($lang)) {
            return;
        }

        $idaction = $perm->getIdForAction('login');
        $uid = $this->getUserId();

        // create a actionlog entry
        $actionLogCol = new cApiActionlogCollection();
        $actionLogCol->create($uid, $client, $lang, $idaction, 0);

        $sess = cRegistry::getSession();
        $sess->register('saveLoginTime');
        $saveLoginTime = true;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function isLoggedIn() {
        $userId = $this->getUserId();
        if (!empty($userId)) {
            $user = new cApiUser($userId);

            return $user->get('user_id') != '';
        } else {
            return false;
        }
    }

}
