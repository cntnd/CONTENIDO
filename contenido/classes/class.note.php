<?php

/**
 * This file contains various note classes.
 *
 * @package    Core
 * @subpackage Backend
 * @author     Unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * This class uses the communication collection to serve a special collection
 * for notes.
 *
 * @package    Core
 * @subpackage GenericDB_Model
 */
class NoteCollection extends cApiCommunicationCollection {
    /**
     * Constructor to create an instance of this class.
     *
     * @throws cInvalidArgumentException
     */
    public function __construct() {
        parent::__construct();
        $this->_setItemClass('NoteItem');
    }

    /**
     * Selects one or more items from the database
     *
     * This function only extends the where statement. See the
     * original function for the parameters.
     *
     * @see ItemCollection::select()
     *
     * @param string $where    [optional]
     *                         Specifies the where clause.
     * @param string $group_by [optional]
     *                         Specifies the group by clause.
     * @param string $order_by [optional]
     *                         Specifies the order by clause.
     * @param string $limit    [optional]
     *                         Specifies the limit by clause.
     *
     * @return bool
     *         True on success, otherwise false
     *
     * @throws cDbException
     */
    public function select($where = '', $group_by = '', $order_by = '', $limit = '') {
        if ($where == '') {
            $where = "comtype='note'";
        } else {
            $where .= " AND comtype='note'";
        }

        return parent::select($where, $group_by, $order_by, $limit);
    }

    /**
     * Creates a new note item.
     *
     * @param string $itemtype
     *                         Item type (usually the class name)
     * @param mixed  $itemid
     *                         Item ID (usually the primary key)
     * @param int    $idlang
     *                         Language-ID
     * @param string $message
     *                         Message to store
     * @param string $category [optional]
     *
     * @return object
     *                         The new item
     *
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public function createItem($itemtype, $itemid, $idlang, $message, $category = '') {
        $item = parent::create();

        $item->set('subject', 'Note Item');
        $item->set('message', $message);
        $item->set('comtype', 'note');
        $item->store();

        $item->setProperty('note', 'itemtype', $itemtype);
        $item->setProperty('note', 'itemid', $itemid);
        $item->setProperty('note', 'idlang', $idlang);

        if ($category != '') {
            $item->setProperty('note', 'category', $category);
        }

        return $item;
    }
}

/**
 * This class uses the communication item to serve a special item for notes.
 *
 * @package    Core
 * @subpackage GenericDB_Model
 */
class NoteItem extends cApiCommunication {
}

/**
 * This class uses the iframe GUI class to serve a special iframe for notes.
 *
 * @package    Core
 * @subpackage GUI
 */
class NoteView extends cHTMLIFrame {

    /**
     *
     * @param string $sItemType
     * @param string $sItemId
     */
    public function __construct($sItemType, $sItemId) {
        global $sess;
        parent::__construct();
        $this->setSrc($sess->url("main.php?itemtype=$sItemType&itemid=$sItemId&area=note&frame=2"));
        $this->setBorder(0);
    }
}

/**
 * This class uses the div GUI class to serve a special div for note lists.
 *
 * @package    Core
 * @subpackage GUI
 */
class NoteList extends cHTMLDiv {

    /**
     * @var bool
     */
    protected $_bDeleteable;

    /**
     * @var string
     */
    protected $_sItemType;

    /**
     * @var string
     */
    protected $_sItemId;

    /**
     * Constructor to create an instance of this class.
     *
     * @param string $sItemType
     * @param string $sItemId
     */
    public function __construct($sItemType, $sItemId) {
        parent::__construct();

        $this->_sItemType = $sItemType;
        $this->_sItemId = $sItemId;

        $this->appendStyleDefinition('width', '100%');
    }

    /**
     *
     * @param bool $bDeleteable
     */
    public function setDeleteable($bDeleteable) {
        $this->_bDeleteable = $bDeleteable;
    }

    /**
     * (non-PHPdoc)
     *
     * @see cHTML::toHtml()
     *
     * @return string
     *     generated markup
     *
     * @throws cDbException
     * @throws cException
     */
    public function toHtml() {
        global $lang;

        $sItemType = $this->_sItemType;
        $sItemId = $this->_sItemId;

        $oPropertyCollection = new cApiPropertyCollection();
        $oPropertyCollection->select("itemtype = 'idcommunication' AND type = 'note' AND name = 'idlang' AND value = " . (int) $lang);

        $items = [];

        while ($oProperty = $oPropertyCollection->next()) {
            $items[] = $oProperty->get('itemid');
        }

        $oNoteItems = new NoteCollection();

        if (count($items) == 0) {
            $items[] = 0;
        }

        $oNoteItems->select('idcommunication IN (' . implode(', ', $items) . ')', '', 'created DESC');

        $i    = [];
        $dark = false;
        while ($oNoteItem = $oNoteItems->next()) {
            if ($oNoteItem->getProperty('note', 'itemtype') == $sItemType && $oNoteItem->getProperty('note', 'itemid') == $sItemId) {
                $j = new NoteListItem($sItemType, $sItemId, $oNoteItem->get('idcommunication'));
                $j->setAuthor($oNoteItem->get('author'));
                $j->setDate($oNoteItem->get('created'));
                $j->setMessage($oNoteItem->get('message'));
                $j->setBackground($dark);
                $j->setDeleteable($this->_bDeleteable);
                $dark = !$dark;
                $i[] = $j;
            }
        }

        $this->setContent($i);

        $result = parent::toHtml();

        return '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>' . $result . '</td></tr></table>';
    }
}

/**
 * This class uses the div GUI class to serve a special div for note list items.
 *
 * @package    Core
 * @subpackage GUI
 */
class NoteListItem extends cHTMLDiv {

    /**
     * @var int
     */
    private $_iDeleteItem;

    /**
     * @var string
     */
    private $_sItemType;

    /**
     * @var string
     */
    private $_sItemId;

    /**
     * @var bool
     */
    private $_bDeleteable;

    /**
     * @var bool|string
     */
    private $_sAuthor;

    /**
     * @var false|string
     */
    private $_sDate;

    /**
     * @var string
     */
    private $_sMessage;

    /**
     * Constructor to create an instance of this class.
     *
     * @param string $sItemType
     * @param string $sItemId
     * @param int $iDeleteItem
     */
    public function __construct($sItemType, $sItemId, $iDeleteItem) {
        parent::__construct();
        $this->appendStyleDefinition('padding', '2px');
        $this->setBackground();
        $this->setDeleteable(true);

        $this->_iDeleteItem = $iDeleteItem;
        $this->_sItemType = $sItemType;
        $this->_sItemId = $sItemId;
    }

    /**
     *
     * @param bool $bDeleteable
     */
    public function setDeleteable($bDeleteable) {
        $this->_bDeleteable = $bDeleteable;
    }

    /**
     *
     * @param bool $dark [optional]
     */
    public function setBackground($dark = false) {
    }

    /**
     *
     * @param string $sAuthor
     */
    public function setAuthor($sAuthor) {
        if (cString::getStringLength($sAuthor) == 32) {
            $result = getGroupOrUserName($sAuthor);

            if ($result !== false) {
                $sAuthor = $result;
            }
        }

        $this->_sAuthor = $sAuthor;
    }

    /**
     *
     * @param string|int $iDate
     */
    public function setDate($iDate) {
        $dateformat = getEffectiveSetting('dateformat', 'full', 'Y-m-d H:i:s');

        if (cSecurity::isString($iDate)) {
            $iDate = strtotime($iDate);
        }
        $this->_sDate = date($dateformat, $iDate);
    }

    /**
     *
     * @param string $sMessage
     */
    public function setMessage($sMessage) {
        $this->_sMessage = $sMessage;
    }

    /**
     *
     * @see cHTML::render()
     * @return string
     *         Generated markup
     */
    public function render() {
        global $sess;
        $itemtype = $this->_sItemType;
        $itemid = $this->_sItemId;
        $deleteitem = $this->_iDeleteItem;

        $table = '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td><b>';
        $table .= $this->_sAuthor;
        $table .= '</b></td><td class="text_right">';
        $table .= $this->_sDate;

        if ($this->_bDeleteable == true) {
            $oDeleteable = new cHTMLLink();
            $oDeleteable->setClass("con_img_button mgl3");
            $oDeletePic = new cHTMLImage(cRegistry::getBackendUrl() . '/images/delete.gif');
            $oDeleteable->setContent($oDeletePic);
            $oDeleteable->setLink($sess->url("main.php?frame=2&area=note&itemtype=$itemtype&itemid=$itemid&action=con&deleteitem=$deleteitem"));

            $table .= '</td><td width="1">' . $oDeleteable->render();
        }
        $table .= '</td></tr></table>';

        $oMessage = new cHTMLDiv();
        $oMessage->setContent($this->_sMessage);
        $oMessage->setStyle("padding-bottom: 8px; margin-top: 4px;");

        $this->setContent([$table, $oMessage]);

        return parent::render();
    }
}

/**
 * This class uses the link GUI class to serve a special link for notes.
 *
 * @package    Core
 * @subpackage GUI
 */
class NoteLink extends cHTMLLink {

    /**
     *
     * @var string Object type
     */
    private $_sItemType;

    /**
     *
     * @var string Object ID
     */
    private $_sItemID;

    /**
     *
     * @var bool If true, shows the note history
     */
    private $_bShowHistory;

    /**
     *
     * @var bool If true, history items can be deleted
     */
    private $_bDeleteHistoryItems;

    /**
     * Creates a new note link item.
     *
     * This link is used to show the popup from any position within the system.
     * The link contains the note image.
     *
     * @param string $sItemType
     *         Item type (usually the class name)
     * @param mixed $sItemID
     *         Item ID (usually the primary key)
     */
    public function __construct($sItemType, $sItemID) {
        parent::__construct();

        $img = new cHTMLImage('images/note.gif');
        $img->setStyle('padding-left: 2px; padding-right: 2px;');

        $img->setAlt(i18n('View notes / add note'));
        $this->setLink('#');
        $this->setContent($img->render());
        $this->setAlt(i18n('View notes / add note'));

        $this->_sItemType = $sItemType;
        $this->_sItemID = $sItemID;
        $this->_bShowHistory = false;
        $this->_bDeleteHistoryItems = false;
    }

    /**
     * Enables the display of all note items
     */
    public function enableHistory() {
        $this->_bShowHistory = true;
    }

    /**
     * Disables the display of all note items
     */
    public function disableHistory() {
        $this->_bShowHistory = false;
    }

    /**
     * Enables the delete function in the history view
     */
    public function enableHistoryDelete() {
        $this->_bDeleteHistoryItems = true;
    }

    /**
     * Disables the delete function in the history view
     */
    public function disableHistoryDelete() {
        $this->_bDeleteHistoryItems = false;
    }

    /**
     * @see cHTML::render()
     * @return string
     *         Generated markup
     */
    public function render() {
        global $sess;

        $itemtype = $this->_sItemType;
        $itemid = $this->_sItemID;

        $url = $sess->url("main.php?area=note&frame=1&itemtype=$itemtype&itemid=$itemid");
        $this->setEvent('click', "javascript:window.open('$url', 'todo', 'resizable=yes,scrollbars=yes,height=360,width=550');");

        return parent::render();
    }
}
