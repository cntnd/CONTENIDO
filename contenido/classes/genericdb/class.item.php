<?php

/**
 * This file contains the generic db item class.
 *
 * @package Core
 * @subpackage GenericDB
 *
 * @author Timo Hummel
 * @author Murat Purc <murat@purc.de>
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Class Item
 * Abstract class for database based items.
 *
 * @package Core
 * @subpackage GenericDB
 */
abstract class Item extends cItemBaseAbstract {

    /**
     * Storage of the source table to use for the user informations
     *
     * @var array
     */
    public $values;

    /**
     * Storage of the fields which were modified, where the keys are the
     * fieldnames and the values just simple bools.
     *
     * @var array
     */
    protected $modifiedValues;

    /**
     * Stores the old primary key, just in case somebody wants to change it
     *
     * @var string
     */
    protected $oldPrimaryKey;

    /**
     * List of funcion names of the filters used when data is stored to the db.
     *
     * @var array
     */
    protected $_arrInFilters = [
        'htmlspecialchars',
        'addslashes'
    ];

    /**
     * List of funcion names of the filters used when data is retrieved from the
     * db
     *
     * @var array
     */
    protected $_arrOutFilters = [
        'stripslashes',
        'htmldecode'
    ];

    /**
     * Class name of meta object
     *
     * @var string
     */
    protected $_metaObject;

    /**
     * Last executed SQL statement
     *
     * @var string
     */
    protected $_lastSQL;

    /**
     * Constructor to create an instance of this class.
     *
     * @param string $sTable
     *         The table to use as information source
     * @param string $sPrimaryKey
     *         The primary key to use
     *
     * @throws cInvalidArgumentException
     */
    public function __construct($sTable, $sPrimaryKey) {
        parent::__construct($sTable, $sPrimaryKey, get_parent_class($this));
    }

    /**
     * Resets class variables back to default
     * This is handy in case a new item is tried to be loaded into this class instance.
     */
    protected function _resetItem() {
        parent::_resetItem();

        // make sure not to reset filters because then default filters would always be used for loading
        $this->values = null;
        $this->modifiedValues = null;
        $this->_metaObject = null;
        $this->_lastSQL = null;
    }

    /**
     * Loads an item by colum/field from the database.
     *
     * @param string $sField
     *                      Specifies the field
     * @param mixed  $mValue
     *                      Specifies the value
     * @param bool   $bSafe [optional]
     *                      Use inFilter or not
     * @param bool   $bAllowOneResult [optional]
     *                      Flag to allow only one result
     *
     * @return bool
     *                      True if the load was successful
     * @throws cDbException
     * @throws cException if more than one item has been found matching the given arguments
     */
    public function loadBy($sField, $mValue, $bSafe = true, $bAllowOneResult = true) {
        // reset class variables back to default before loading
        $this->_resetItem();

        if ($bSafe) {
            $mValue = $this->inFilter($mValue);
        }

        // check, if cache contains a matching entry
        $aRecordSet = NULL;
        if ($sField === $this->_primaryKeyName) {
            $aRecordSet = $this->_oCache->getItem($mValue);
        } else {
            $aRecordSet = $this->_oCache->getItemByProperty($sField, $mValue);
        }

        if ($aRecordSet) {
            // entry in cache found, load entry from cache
            $this->loadByRecordSet($aRecordSet);
            return true;
        }

        // SQL-Statement to select by field
        $sql = "SELECT * FROM `%s` WHERE %s = '%s'";
        $sql = $this->db->prepare($sql, $this->table, $sField, $mValue);

        // Query the database
        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($bAllowOneResult && $this->db->numRows() > 1) {
            $msg = "Tried to load a single line with field $sField and value $mValue from " . $this->table . " but found more than one row";
            throw new cException($msg);
        }

        // Advance to the next record, return false if nothing found
        if (!$this->db->nextRecord()) {
            return false;
        }

        $this->loadByRecordSet($this->db->toArray());
        $this->_setLoaded(true);
        return true;
    }

    /**
     * Loads an item by columns/fields from the database.
     *
     * @param array $aAttributes
     *                     associative array with field / value pairs
     * @param bool  $bSafe [optional]
     *                     Use inFilter or not
     * @param bool   $bAllowOneResult [optional]
     *                      Flag to allow only one result
     * @return bool
     *                     True if the load was successful
     * @throws cDbException
     * @throws cException if more than one item could be found matching the given arguments
     */
    public function loadByMany(array $aAttributes, $bSafe = true, $bAllowOneResult = true) {
        // reset class variables back to default before loading
        $this->_resetItem();

        if ($bSafe) {
            $aAttributes = $this->inFilter($aAttributes);
        }

        // check, if cache contains a matching entry
        $aRecordSet = NULL;
        if (count($aAttributes) == 1 && isset($aAttributes[$this->getPrimaryKeyName()])) {
            $aRecordSet = $this->_oCache->getItem($aAttributes[$this->getPrimaryKeyName()]);
        } else {
            $aRecordSet = $this->_oCache->getItemByProperties($aAttributes);
        }

        if ($aRecordSet) {
            // entry in cache found, load entry from cache
            $this->loadByRecordSet($aRecordSet);
            return true;
        }

        // SQL-Statement to select by fields
        $sql = $this->_buildLoadByManyQuery($aAttributes);

        // Query the database
        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($bAllowOneResult && $this->db->numRows() > 1) {
            $msg = 'Tried to load a single line with fields ' . print_r(array_keys($aAttributes), true) . ' and values ' . print_r(array_values($aAttributes), true) . ' from ' . $this->table . ' but found more than one row';
            throw new cException($msg);
        }

        // Advance to the next record, return false if nothing found
        if (!$this->db->nextRecord()) {
            return false;
        }

        $this->loadByRecordSet($this->db->toArray());
        $this->_setLoaded(true);
        return true;
    }

    /**
     * Creates a select query by maany fields
     * @param array $fields Associative fields and values list
     *
     * @return string Build SQL statement
     * @throws cDbException
     */
    protected function _buildLoadByManyQuery(array $fields) {
        // SQL-Statement to select by fields
        $fieldsSql = [];

        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $fieldsSql[] = "`$key` = ':$key'";
            } elseif (is_null($value)) {
                $fieldsSql[] = "`$key` IS NULL";
            } else {
                $fieldsSql[] = "`$key` = :$key";
            }
        }
        $sql = 'SELECT * FROM `:mytab` WHERE ' . implode(' AND ', $fieldsSql);

        $sql = $this->db->prepare($sql, array_merge([
            'mytab' => $this->table
        ], $fields));

        return $sql;
    }

    /**
     * Loads an item by passed where clause from the database.
     * This function is expensive, since it executes allways a query to the
     * database
     * to retrieve the primary key, even if the record set is aleady cached.
     * NOTE: Passed value has to be escaped before. This will not be done by
     * this function.
     *
     * @param string $sWhere
     *         The where clause like 'idart = 123 AND idlang = 1'
     * @return bool
     *         True if the load was successful
     *
     * @throws cDbException
     * @throws cException if more than one item could be found matching the given where clause
     */
    protected function _loadByWhereClause($sWhere) {
        // SQL-Statement to select by whee clause
        $sql = "SELECT %s AS `pk` FROM `%s` WHERE " . (string) $sWhere;
        $sql = $this->db->prepare($sql, $this->getPrimaryKeyName(), $this->table);

        // Query the database
        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($this->db->numRows() > 1) {
            $msg = "Tried to load a single line with where clause '" . $sWhere . "' from " . $this->table . " but found more than one row";
            throw new cException($msg);
        }

        // Advance to the next record, return false if nothing found
        if (!$this->db->nextRecord()) {
            return false;
        }

        $id = $this->db->f('pk');
        return $this->loadByPrimaryKey($id);
    }

    /**
     * Loads an item by ID from the database.
     *
     * @param string|int $mValue
     *         Specifies the primary key value
     *
     * @return bool
     *         True if the load was successful
     * @throws cDbException
     * @throws cException
     */
    public function loadByPrimaryKey($mValue) {
        if (is_null($mValue) || (is_string($mValue) && empty($mValue))) {
            return false;
        }
        $bSuccess = $this->loadBy($this->_primaryKeyName, $mValue);

        if ($bSuccess && method_exists($this, '_onLoad')) {
            $this->_onLoad();
        }

        return $bSuccess;
    }

    /**
     * Loads an item by it's recordset.
     *
     * @param array $aRecordSet
     *         The recordset of the item
     */
    public function loadByRecordSet(array $aRecordSet) {
        $this->values = $aRecordSet;
        $this->oldPrimaryKey = $this->values[$this->getPrimaryKeyName()];
        $this->_setLoaded(true);
        $this->_oCache->addItem($this->oldPrimaryKey, $this->values);

        if (method_exists($this, '_onLoad')) {
            $this->_onLoad();
        }
    }

    /**
     * Function which is called whenever an item is loaded.
     * Inherited classes should override this function if desired.
     */
    protected function _onLoad() {
    }

    /**
     * Gets the value of a specific field.
     *
     * @param string $sField
     *         Specifies the field to retrieve
     * @param bool $bSafe [optional]
     *         Flag to run defined outFilter on passed value
     * @return mixed
     *         Value of the field
     */
    public function getField($sField, $bSafe = true) {
        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        if ($bSafe) {
            if ($sField === 'active') mp_d($this->values[$sField], '$this->values[$sField]', 'vd');
            return $this->outFilter($this->values[$sField]);
        } else {
            return $this->values[$sField];
        }
    }

    /**
     * Wrapper for getField (less to type).
     *
     * @param string $sField
     *         Specifies the field to retrieve
     * @param bool $bSafe [optional]
     *         Flag to run defined outFilter on passed value
     * @return mixed
     *         Value of the field
     */
    public function get($sField, $bSafe = true) {
        return $this->getField($sField, $bSafe);
    }

    /**
     * Sets the value of a specific field.
     *
     * @param string $sField
     *         Field name
     * @param mixed $mValue
     *         Value to set
     * @param bool $bSafe [optional]
     *         Flag to run defined inFilter on passed value
     * @return bool
     */
    public function setField($sField, $mValue, $bSafe = true) {
        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        if ($sField == $this->getPrimaryKeyName()) {
            $this->oldPrimaryKey = $this->values[$sField];
        }

        // Apply filter on value
        if ($bSafe) {
            $mValue = $this->inFilter($mValue);
        }

        // Flag as modified
        if (!isset($this->values[$sField])) {
            $this->modifiedValues[$sField] = true;
        }

        // Set new value
        $this->values[$sField] = $mValue;

        return true;
    }

    /**
     * Shortcut to setField.
     *
     * @param string $sField
     *         Field name
     * @param mixed $mValue
     *         Value to set
     * @param bool $bSafe [optional]
     *         Flag to run defined inFilter on passed value
     * @return bool
     */
    public function set($sField, $mValue, $bSafe = true) {
        return $this->setField($sField, $mValue, $bSafe);
    }

    /**
     * Stores the loaded and modified item to the database.
     *
     * @return bool
     * @throws cDbException
     * @throws cInvalidArgumentException
     */
    public function store() {
        $class = get_class($this);

        $this->_executeCallbacks(self::STORE_BEFORE, $class, [$this]);

        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            $this->_executeCallbacks(self::STORE_FAILURE, $class, [$this]);
            return false;
        }

        if (!is_array($this->modifiedValues)) {
            $this->_executeCallbacks(self::STORE_SUCCESS, $class, [$this]);
            return true;
        }

        $sql = $this->_buildStoreQuery($this->modifiedValues);
        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($this->db->affectedRows() > 0) {
            $this->_oCache->addItem($this->oldPrimaryKey, $this->values);
            $this->_executeCallbacks(self::STORE_SUCCESS, $class, [$this]);
            return true;
        }

        $this->_executeCallbacks(self::STORE_FAILURE, $class, [$this]);
        return false;
    }

    /**
     * Creates a update query by maany fields
     * @param array $fields Associative fields and values list
     *
     * @return string
     */
    protected function _buildStoreQuery(array $fields) {
        $fieldsSql = [];

        foreach ($fields as $key => $mValue) {
            $value = $this->values[$key];
            if (is_string($value)) {
                $fieldsSql[] = "`$key` = '" . $this->db->escape($value) . "'";
            } elseif (is_null($value)) {
                $fieldsSql[] = "`$key` = NULL";
            } else {
                $fieldsSql[] = "`$key` = " . $value;
            }
        }

        $sql = 'UPDATE `' . $this->table . '` SET ' . implode(', ', $fieldsSql);
        $sql .= " WHERE `" . $this->getPrimaryKeyName() . "` = ";
        if (is_string($this->oldPrimaryKey)) {
            $sql .= "'" . $this->oldPrimaryKey . "'";
        } else {
            $sql .= $this->oldPrimaryKey;
        }

        return $sql;
    }

    /**
     * Returns current item data as an assoziative array.
     *
     * @return array|false
     */
    public function toArray() {
        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        $aReturn = [];
        foreach ($this->values as $field => $value) {
            $aReturn[$field] = $this->getField($field);
        }
        return $aReturn;
    }

    /**
     * Returns current item data as an object.
     *
     * @return stdClass|false
     */
    public function toObject() {
        $return = $this->toArray();
        return (false !== $return) ? (object) $return : $return;
    }

    /**
     * Sets a custom property.
     *
     * @param string $sType
     *                        Specifies the type
     * @param string $sName
     *                        Specifies the name
     * @param mixed  $mValue
     *                        Specifies the value
     * @param int    $iClient [optional]
     *                        Id of client to set property for
     *
     * @return bool
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public function setProperty($sType, $sName, $mValue, $iClient = 0) {
        // If this object wasn't loaded before, return false
        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        // Set the value
        $oProperties = $this->_getPropertiesCollectionInstance($iClient);
        $bResult = $oProperties->setValue($this->getPrimaryKeyName(), $this->get($this->getPrimaryKeyName()), $sType, $sName, $mValue);
        return $bResult;
    }

    /**
     * Returns a custom property.
     *
     * @param string $sType
     *                        Specifies the type
     * @param string $sName
     *                        Specifies the name
     * @param int    $iClient [optional]
     *                        Id of client to set property for
     *
     * @return mixed
     *                        Value of the given property or false
     * @throws cDbException
     * @throws cException
     */
    public function getProperty($sType, $sName, $iClient = 0) {
        // If this object wasn't loaded before, return false
        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        // Return the value
        $oProperties = $this->_getPropertiesCollectionInstance($iClient);
        $mValue = $oProperties->getValue($this->getPrimaryKeyName(), $this->get($this->getPrimaryKeyName()), $sType, $sName);
        return $mValue;
    }

    /**
     * Deletes a custom property.
     *
     * @param string $sType
     *                        Specifies the type
     * @param string $sName
     *                        Specifies the name
     * @param int    $iClient [optional]
     *                        Id of client to delete properties
     *
     * @return bool
     *
     * @throws cDbException
     * @throws cInvalidArgumentException
     */
    public function deleteProperty($sType, $sName, $iClient = 0) {
        // If this object wasn't loaded before, return false
        if (true !== $this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        // Delete the value
        $oProperties = $this->_getPropertiesCollectionInstance($iClient);
        $bResult = $oProperties->deleteValue($this->getPrimaryKeyName(), $this->get($this->getPrimaryKeyName()), $sType, $sName);
        return $bResult;
    }

    /**
     * Deletes a custom property by its id.
     *
     * @param int $idprop
     *         Id of property
     *
     * @return bool
     *
     * @throws cDbException
     * @throws cInvalidArgumentException
     */
    public function deletePropertyById($idprop) {
        $oProperties = $this->_getPropertiesCollectionInstance();
        return $oProperties->delete($idprop);
    }

    ///**
    // * Deletes the current item
    // * Method doesn't work, remove in future versions.
    // */
    // function delete() {
    // $this->_collectionInstance->delete($item->get($this->getPrimaryKeyName()));
    // }

    /**
     * Define the filter functions used when data is being stored or retrieved
     * from the database.
     *
     * Examples:
     * <pre>
     * $obj->setFilters(['addslashes'], ['stripslashes']);
     * $obj->setFilters(['htmlencode', 'addslashes'], ['stripslashes',
     * 'htmlencode']);
     * </pre>
     *
     * @param array $aInFilters [optional]
     *         Array with function names
     * @param array $aOutFilters [optional]
     *         Array with function names
     */
    public function setFilters($aInFilters = [], $aOutFilters = []) {
        $this->_arrInFilters = $aInFilters;
        $this->_arrOutFilters = $aOutFilters;
    }

    /**
     * @deprecated Since 4.10.2, use {@see Item::inFilter()} instead
     */
    public function _inFilter($mData) {
        cDeprecated("The function _inFilter() is deprecated since CONTENIDO 4.10.2, use Item::inFilter() instead.");
        return $this->inFilter($mData);
    }

    /**
     * Filters the passed data using the functions defines in the _arrInFilters
     * array.
     *
     * @since CONTENIDO 4.10.2
     * @see Item::setFilters()
     * @param mixed $mData
     *         Data to filter
     * @return mixed
     *         Filtered data
     */
    public function inFilter($mData) {
        return $this->_filter($mData, $this->_arrInFilters);
    }

    /**
     * Filters the passed data using the functions defines in the _arrOutFilters
     * array.
     *
     * @see Item::setFilters()
     * @param mixed $mData
     *         Data to filter
     * @return mixed
     *         Filtered data
     */
    public function outFilter($mData) {
        return $this->_filter($mData, $this->_arrOutFilters);
    }

    /**
     * Filters the passed data using the passed filter functions list.
     *
     * @param mixed $mData
     *         Data to filter
     * @param array $filterFunctions
     *         List of functions
     * @return mixed
     *         Filtered data
     */
    protected function _filter($mData, array $filterFunctions) {
        foreach ($filterFunctions as $_function) {
            if (function_exists($_function)) {
                // Check whether it is a string function and therefore
                // expects a value of type string
                $isStringFunction = in_array(
                    $_function, $this->_settings['string_filter_funtions']
                );
                if (is_array($mData)) {
                    foreach ($mData as $key => $value) {
                        if ($isStringFunction) {
                            if (is_string($value)) {
                                $mData[$key] = $_function($value);
                            }
                        } else {
                            $mData[$key] = $_function($value);
                        }
                    }
                } else {
                    if ($isStringFunction) {
                        if (is_string($mData)) {
                            $mData = $_function($mData);
                        }
                    } else {
                        $mData = $_function($mData);
                    }
                }
            }
        }
        return $mData;
    }

    /**
     * Set meta object class name.
     *
     * @param string $metaObject
     */
    protected function _setMetaObject($metaObject) {
        $this->_metaObject = $metaObject;
    }

    /**
     * Return meta object instance.
     * This object might be retrieved from a global cache ($_metaObjectCache).
     *
     * @return object
     */
    public function getMetaObject() {
        global $_metaObjectCache;

        if (!is_array($_metaObjectCache)) {
            $_metaObjectCache = [];
        }

        $sClassName = $this->_metaObject;
        $qclassname = cString::toLowerCase($sClassName);

        if (array_key_exists($qclassname, $_metaObjectCache)) {
            if (is_object($_metaObjectCache[$qclassname])) {
                if (cString::toLowerCase(get_class($_metaObjectCache[$qclassname])) == $qclassname) {
                    $_metaObjectCache[$qclassname]->setPayloadObject($this);
                    return $_metaObjectCache[$qclassname];
                }
            }
        }

        if (class_exists($sClassName)) {
            $_metaObjectCache[$qclassname] = new $sClassName($this);
            return $_metaObjectCache[$qclassname];
        }
    }
}
