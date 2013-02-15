<?php

/**
 *
 * @package Plugin
 * @subpackage PIFA Form Assistant
 * @version SVN Revision $Rev:$
 * @author marcus.gnass
 * @copyright four for business AG
 * @link http://www.4fb.de
 */

// assert CONTENIDO framework
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 *
 * @author marcus.gnass
 */
class PifaAjaxHandler {

    /**
     * to display a form for editing a PIFA form field
     *
     * @var string
     */
    const GET_FIELD_FORM = 'get_field_form';

    /**
     * to process a form for editing a PIFA form field
     *
     * @var string
     */
    const POST_FIELD_FORM = 'post_field_form';

    /**
     *
     * @var string
     */
    const REORDER_FIELDS = 'reorder_fields';

    /**
     *
     * @var string
     */
    const EXPORT_DATA = 'export_data';

    /**
     *
     * @var string
     */
    const GET_FILE = 'get_file';

    /**
     *
     * @var string
     */
    const DELETE_FIELD = 'delete_field';

    /**
     *
     * @var string
     */
    const GET_OPTION_ROW = 'get_option_row';

    /**
     *
     * @throws Exception
     */
    function dispatch($action) {

        switch ($action) {

            case self::GET_FIELD_FORM:
                // display a form for editing a PIFA form field
                $idform = cSecurity::toInteger($_GET['idform']);
                $idfield = cSecurity::toInteger($_GET['idfield']);
                $fieldType = cSecurity::toInteger($_GET['field_type']);
                $this->_getFieldForm($idform, $idfield, $fieldType);
                break;

            case self::POST_FIELD_FORM:
                // process a form for editing a PIFA form field
                $idform = cSecurity::toInteger($_POST['idform']);
                $idfield = cSecurity::toInteger($_POST['idfield']);
                // $this->_editFieldForm($idform, $idfield);
                $this->_postFieldForm($idform, $idfield);
                break;

            case self::DELETE_FIELD:
                $idfield = cSecurity::toInteger($_GET['idfield']);
                $this->_deleteField($idfield);
                break;

            case self::REORDER_FIELDS:
                $idform = cSecurity::toInteger($_POST['idform']);
                $idfields = implode(',', array_map(function ($value) {
                    return cSecurity::toInteger($value);
                }, explode(',', $_POST['idfields'])));
                $this->_reorderFields($idform, $idfields);
                break;

            case self::EXPORT_DATA:
                $idform = cSecurity::toInteger($_GET['idform']);
                $this->_exportData($idform);
                break;

            case self::GET_FILE:
                $name = cSecurity::toString($_GET['name']);
                $file = cSecurity::toString($_GET['file']);
                $this->_getFile($name, $file);
                break;

            case self::GET_OPTION_ROW:
                $index = cSecurity::toInteger($_GET['index']);
                $this->_getOptionRow($index);
                break;

            default:
                throw new Exception('unknown action ' . $_REQUEST['action']);

        }

    }

    /**
     * Displays a form for editing a PIFA form field.
     *
     * @param int $idform
     * @param int $idfield
     * @param int $fieldType
     * @throws Exception
     */
    private function _getFieldForm($idform, $idfield, $fieldType) {

        $cfg = cRegistry::getConfig();

        // get field
        if (0 < $idfield) {
            // edit existing field
            $field = new PifaField();
            $field->loadByPrimaryKey($idfield);
        } elseif (0 < $fieldType) {
            // create new field by type
            $field = new PifaField();
            $field->loadByRecordSet(array(
                'field_type' => $fieldType
            ));
        } else {
            // bugger off
            throw new Exception('form could not be created');
        }

        // get option classes
        $optionClasses = Pifa::getExtensionClasses('ExternalOptionsDatasourceInterface');
        array_unshift($optionClasses, array(
            'value' => '',
            'label' => Pifa::i18n('none')
        ));

        // create form
        $tpl = Contenido_SmartyWrapper::getInstance(true);

        // translations
        $tpl->assign('trans', array(
            'idfield' => Pifa::i18n('ID'),
            'fieldRank' => Pifa::i18n('RANK'),
            'fieldType' => Pifa::i18n('FIELD_TYPE'),
            'columnName' => Pifa::i18n('COLUMN_NAME'),
            'label' => Pifa::i18n('LABEL'),
            'displayLabel' => Pifa::i18n('DISPLAY_LABEL'),
            'defaultValue' => Pifa::i18n('DEFAULT_VALUE'),
            'helpText' => Pifa::i18n('HELP_TEXT'),
            'rule' => Pifa::i18n('VALIDATION_RULE'),
            'errorMessage' => Pifa::i18n('ERROR_MESSAGE'),
            'database' => Pifa::i18n('DATABASE'),
            'options' => Pifa::i18n('OPTIONS'),
            'general' => Pifa::i18n('GENERAL'),
            'obligatory' => Pifa::i18n('OBLIGATORY'),
            'value' => Pifa::i18n('VALUE'),
            'addOption' => Pifa::i18n('ADD_OPTION'),
            'submitValue' => Pifa::i18n('SAVE'),
            'styling' => Pifa::i18n('STYLING'),
            'cssClass' => Pifa::i18n('CSS_CLASS'),
            'externalOptionsDatasource' => Pifa::i18n('EXTERNAL_OPTIONS_DATASOURCE')
        ));

        // hidden form values
        $tpl->assign('contenido', cRegistry::getBackendSessionId());
        $tpl->assign('action', self::POST_FIELD_FORM);
        $tpl->assign('idform', $idform);

        // field
        $tpl->assign('field', $field);

        // CSS classes
        $tpl->assign('cssClasses', explode(',', getEffectiveSetting('pifa', 'field-css-classes', 'pifa-field-1,pifa-field-2,pifa-field-3')));

        // option classes (external options datasources)
        $tpl->assign('optionClasses', $optionClasses);

        // build href to call empty option row
        $tpl->assign('hrefAddOption', 'main.php?' . implode('&', array(
            'area=form_ajax',
            'frame=4',
            'contenido=' . cRegistry::getBackendSessionId(),
            'action=' . PifaAjaxHandler::GET_OPTION_ROW
        )));

        // path to partial template for displaying a single option row
        $tpl->assign('partialOptionRow', $cfg['templates']['pifa_ajax_option_row']);

        $tpl->display($cfg['templates']['pifa_ajax_field_form']);

    }

    // /**
    // * Processes a form for editing a PIFA form field.
    // *
    // * @deprecated use _editFieldFormKK instead
    // * @param int $idform
    // * @param int $idfield
    // * @throws Exception
    // */
    // private function _editFieldForm($idform, $idfield) {

    // if (0 < $idfield) {
    // // load field
    // $pifaField = new PifaField($idform);
    // if (!$pifaField->isLoaded()) {
    // throw new Exception('field is not loaded');
    // }
    // } else {
    // // get field type for new form field
    // $fieldType = cSecurity::toInteger($_POST['field_type']);
    // // create field
    // $collection = new PifaFieldCollection();
    // $pifaField = $collection->createNewItem(array(
    // 'idform' => $idform,
    // 'field_type' => $fieldType
    // ));
    // }

    // // remember old column name
    // $oldColumnName = $pifaField->get('column_name');

    // /*
    // * Read item data from form, validate item data and finally set item
    // * data. Which data is editable depends upon the field type. So a
    // * certain data will only be stored if its field is shown in the form.
    // * Never, really never, call Item->set() if the value doesn't differ
    // * from the previous one. Otherwise the genericDb thinks that the item
    // * is modified and tries to store it resulting in a return value of
    // * false!
    // */

    // // According to the MySQL documentation table and column names
    // // must
    // // not be longer than 64 charcters.
    // if ($pifaField->showField('column_name')) {
    // $columnName = cSecurity::toString($_POST['column_name']);
    // $columnName = trim($columnName);
    // $columnName = strtolower($columnName);
    // $columnName = preg_replace('/[^a-z0-9_]/', '_', $columnName);
    // $columnName = substr($columnName, 0, 64);
    // if ($columnName !== $pifaField->get('column_name')) {
    // $pifaField->set('column_name', $columnName);
    // }
    // }

    // if ($pifaField->showField('label')) {
    // $label = cSecurity::toString($_POST['label']);
    // $label = trim($label);
    // $label = substr($label, 0, 255);
    // if ($label !== $pifaField->get('label')) {
    // $pifaField->set('label', $label);
    // }
    // }

    // if ($this->_pifaField->showField('default_value')) {
    // $defaultValue = cSecurity::toString($_POST['default_value']);
    // $defaultValue = trim($defaultValue);
    // $defaultValue = substr($defaultValue, 0, 255);
    // if ($defaultValue !== $pifaField->get('default_value')) {
    // $pifaField->set('default_value', $defaultValue);
    // }
    // }

    // if ($this->_pifaField->showField('option_labels')) {
    // $optionLabels = cSecurity::toString($_POST['option_labels']);
    // $optionLabels = join(',', array_map(function ($value) {
    // return trim(cSecurity::toString($value));
    // }, explode(',', $optionLabels)));
    // $optionLabels = substr($optionLabels, 0, 1023);
    // if ($optionLabels !== $pifaField->get('option_labels')) {
    // $pifaField->set('option_labels', $optionLabels);
    // }
    // }

    // if ($pifaField->showField('option_values')) {
    // $optionValues = cSecurity::toString($_POST['option_values']);
    // $optionValues = join(',', array_map(function ($value) {
    // return trim(cSecurity::toString($value));
    // }, explode(',', $optionValues)));
    // $optionValues = substr($optionValues, 0, 1023);
    // if ($optionValues !== $pifaField->get('option_values')) {
    // $pifaField->set('option_values', $optionValues);
    // }
    // }

    // if ($pifaField->showField('help_text')) {
    // $helpText = cSecurity::toString($_POST['help_text']);
    // $helpText = trim($helpText);
    // $helpText = substr($helpText, 0, 255);
    // if ($helpText !== $pifaField->get('help_text')) {
    // $pifaField->set('help_text', $helpText);
    // }
    // }

    // if ($this->_pifaField->showField('obligatory')) {
    // $obligatory = cSecurity::toString($_POST['obligatory']);
    // $obligatory = trim($obligatory);
    // $obligatory = 'on' === $obligatory? 1 : 0;
    // if ($obligatory !== $pifaField->get('obligatory')) {
    // $pifaField->set('obligatory', $obligatory);
    // }
    // }

    // if ($this->_pifaField->showField('rule')) {
    // $rule = cSecurity::toString($_POST['rule']);
    // $rule = trim($rule);
    // $rule = substr($rule, 0, 255);
    // if ($rule !== $pifaField->get('rule')) {
    // $pifaField->set('rule', $rule);
    // }
    // }

    // if ($this->_pifaField->showField('error_message')) {
    // $errorMessage = cSecurity::toString($_POST['error_message']);
    // $errorMessage = trim($errorMessage);
    // $errorMessage = substr($errorMessage, 0, 255);
    // if ($errorMessage !== $pifaField->get('error_message')) {
    // $pifaField->set('error_message', $errorMessage);
    // }
    // }

    // // store item
    // if (false === $pifaField->store()) {
    // throw new Exception('could not store field: ' .
    // $pifaField->getLastError());
    // }

    // // rename column if name has changed
    // $pifaForm = new PifaForm($idform);
    // $pifaForm->renameColumn($oldColumnName, $pifaField);

    // }

    /**
     * Processes a form for editing a PIFA form field.
     * KKs version
     *
     * @param int $idform
     * @param int $idfield
     * @throws Exception
     */
    private function _postFieldForm($idform, $idfield) {

        global $area;
        $cfg = cRegistry::getConfig();

        // load or create field
        if (0 < $idfield) {
            // load field
            $pifaField = new PifaField($idfield);
            if (!$pifaField->isLoaded()) {
                throw new Exception('field is not loaded');
            }
            $isFieldCreated = false;
        } else {
            // get field type for new form field
            $fieldType = cSecurity::toInteger($_POST['field_type']);
            // create field
            $collection = new PifaFieldCollection();
            $pifaField = $collection->createNewItem(array(
                'idform' => $idform,
                'field_type' => $fieldType
            ));
            $isFieldCreated = true;
        }

        // remember old column name
        $oldColumnName = $pifaField->get('column_name');

        /*
         * Read item data from form, validate item data and finally set item
         * data. Which data is editable depends upon the field type. So a
         * certain data will only be stored if its field is shown in the form.
         * Never, really never, call Item->set() if the value doesn't differ
         * from the previous one. Otherwise the genericDb thinks that the item
         * is modified and tries to store it resulting in a return value of
         * false!
         */

        // set the new rank of the item
        $fieldRank = cSecurity::toInteger($_POST['field_rank']);
        if ($fieldRank !== $pifaField->get('field_rank')) {
            $pifaField->set('field_rank', $fieldRank);
        }

        // According to the MySQL documentation table and column names
        // must not be longer than 64 charcters.
        if ($pifaField->showField('column_name')) {
            $columnName = cSecurity::toString($_POST['column_name']);
            $columnName = trim($columnName);
            $columnName = strtolower($columnName);
            $columnName = preg_replace('/[^a-z0-9_]/', '_', $columnName);
            $columnName = substr($columnName, 0, 64);
            if ($columnName !== $pifaField->get('column_name')) {
                $pifaField->set('column_name', $columnName);
            }
        }

        if ($pifaField->showField('label')) {
            $label = cSecurity::toString($_POST['label']);
            $label = trim($label);
            $label = substr($label, 0, 1023);
            if ($label !== $pifaField->get('label')) {
                $pifaField->set('label', $label);
            }
        }

        if ($pifaField->showField('default_value')) {
            $defaultValue = cSecurity::toString($_POST['default_value']);
            $defaultValue = trim($defaultValue);
            $defaultValue = substr($defaultValue, 0, 1023);
            if ($defaultValue !== $pifaField->get('default_value')) {
                $pifaField->set('default_value', $defaultValue);
            }
        }

        if ($pifaField->showField('option_labels')) {
            $optionLabels = implode(',', array_map(function ($value) {
                $value = cSecurity::toString($value);
                $value = trim($value);
                return $value;
            }, $_POST['option_labels']));
            $optionLabels = substr($optionLabels, 0, 1023);
            if ($optionLabels !== $pifaField->get('option_labels')) {
                $pifaField->set('option_labels', $optionLabels);
            }
        }

        if ($pifaField->showField('option_values')) {
            $optionValues = implode(',', array_map(function ($value) {
                $value = cSecurity::toString($value);
                $value = trim($value);
                return $value;
            }, $_POST['option_values']));
            $optionValues = substr($optionValues, 0, 1023);
            if ($optionValues !== $pifaField->get('option_values')) {
                $pifaField->set('option_values', $optionValues);
            }
        }

        if ($pifaField->showField('help_text')) {
            $helpText = cSecurity::toString($_POST['help_text']);
            $helpText = trim($helpText);
            if ($helpText !== $pifaField->get('help_text')) {
                $pifaField->set('help_text', $helpText);
            }
        }

        if ($pifaField->showField('obligatory')) {
            $obligatory = cSecurity::toString($_POST['obligatory']);
            $obligatory = trim($obligatory);
            $obligatory = 'on' === $obligatory? 1 : 0;
            if ($obligatory !== $pifaField->get('obligatory')) {
                $pifaField->set('obligatory', $obligatory);
            }
        }

        if ($pifaField->showField('rule')) {
            $rule = cSecurity::toString($_POST['rule']);
            $rule = trim($rule);
            $rule = substr($rule, 0, 1023);
            if ($rule !== $pifaField->get('rule')) {
                $pifaField->set('rule', $rule);
            }
        }

        if ($pifaField->showField('error_message')) {
            $errorMessage = cSecurity::toString($_POST['error_message']);
            $errorMessage = trim($errorMessage);
            $errorMessage = substr($errorMessage, 0, 1023);
            if ($errorMessage !== $pifaField->get('error_message')) {
                $pifaField->set('error_message', $errorMessage);
            }
        }

        if ($pifaField->showField('css_class')) {
            $cssClass = implode(',', array_map(function ($value) {
                $value = cSecurity::toString($value);
                $value = trim($value);
                return $value;
            }, $_POST['css_class']));
            $cssClass = substr($cssClass, 0, 1023);
            if ($cssClass !== $pifaField->get('css_class')) {
                $pifaField->set('css_class', $cssClass);
            }
        }

        if ($pifaField->showField('option_class')) {
            $optionClass = cSecurity::toString($_POST['option_class']);
            $optionClass = trim($optionClass);
            $optionClass = substr($optionClass, 0, 1023);
            if ($optionClass !== $pifaField->get('option_class')) {
                $pifaField->set('option_class', $optionClass);
            }
        }

        // store item
        if (false === $pifaField->store()) {
            throw new Exception('could not store field: ' . $pifaField->getLastError());
        }

        // if a new field was created
        // younger siblings have to be moved
        if (true === $isFieldCreated) {

            // update ranks of younger siblings
            $sql = "-- PifaAjaxHandler->_editFieldFormKK()
				UPDATE
					" . $cfg['tab']['pifa_field'] . "
				SET
					field_rank = field_rank + 1
				WHERE
					idform = " . cSecurity::toInteger($idform) . "
					AND field_rank >= " . cSecurity::toInteger($fieldRank) . "
					AND idfield <> " . cSecurity::toInteger($pifaField->get('idfield')) . "
				;";

            $db = cRegistry::getDb();
            if (false === $db->query($sql)) {
                // false is returned if no fields were updated
                // but that doesn't matter cause new field might
                // have no younger siblings
            }

        }

        // create or rename column in data table
        $pifaForm = new PifaForm($idform);
        if (true === $isFieldCreated) {
            // add column for current field to table of current form
            $pifaForm->addColumn($pifaField);
        } else {
            // rename column if name has changed
            $pifaForm->renameColumn($oldColumnName, $pifaField);
        }

        // return new row to be displayed in list
        $editField = new cHTMLLink();
        $editField->setCLink($area, 4, self::GET_FIELD_FORM);
        $editField->setCustom('idform', $idform);
        $editField = $editField->getHref();

        $deleteField = new cHTMLLink();
        $deleteField->setCLink($area, 4, self::DELETE_FIELD);
        $deleteField->setCustom('idform', $idform);
        $deleteField = $deleteField->getHref();

        $tpl = Contenido_SmartyWrapper::getInstance(true);

        // translations
        $tpl->assign('trans', array(
            'edit' => Pifa::i18n('edit'),
            'delete' => Pifa::i18n('delete')
        ));

        // the field
        $tpl->assign('field', $pifaField);

        $tpl->assign('editField', $editField);
        $tpl->assign('deleteField', $deleteField);

        $tpl->display($cfg['templates']['pifa_ajax_field_row']);

    }

    /**
     *
     * @param int $idfield
     * @throws Exception
     */
    private function _deleteField($idfield) {

        if (0 == $idfield) {
            throw new Exception('no idfield given');
        }

        $pifaField = new PifaField($idfield);
        $pifaField->delete();

    }

    /**
     * reorder fields
     *
     * @param int $idform
     * @param string $idfields CSV of integers
     */
    private function _reorderFields($idform, $idfields) {

        PifaFieldCollection::reorder($idform, $idfields);

    }

    /**
     *
     * @param int $idform
     */
    private function _exportData($idform) {

        // read and echo data
        $pifaForm = new PifaForm($idform);
        $filename = $pifaForm->get('data_table') . date('_Y_m_t_H_i_s') . '.csv';
        $data = $pifaForm->getDataAsCsv();

        // prevent caching
        session_cache_limiter('private');
        session_cache_limiter('must-revalidate');

        // set header
        header('Pragma: cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private');
        header('Content-Type: text/csv');
        header('Content-Length: ' . strlen($data));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');

        // echo payload
        echo $data;

    }

    /**
     *
     * @param string $name
     * @param string $file
     */
    private function _getFile($name, $file) {

        $cfg = cRegistry::getConfig();

        $path = $cfg['path']['contenido_cache'] . 'form_assistant/';

        $file = basename($file);

        header('Pragma: cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private');

        /*
         * TODO find solution application/zip works on Ubuntu 12.04 but has
         * problems on XP/IE7/IE8 application/octet-stream works on XP/IE7/IE8
         * but has problems on Ubuntu 12.04
         */
        header('Content-Type: application/octet-stream');

        header('Content-Length: ' . filesize($path . $file));
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Transfer-Encoding: binary');

        $buffer = '';
        $handle = fopen($path . $file, 'rb');
        if (false === $handle) {
            return false;
        }
        while (!feof($handle)) {
            print fread($handle, 1 * (1024 * 1024));
            ob_flush();
            flush();
        }
        fclose($handle);

    }

    /**
     *
     * @param int $index
     */
    private function _getOptionRow($index) {

        $cfg = cRegistry::getConfig();

        $tpl = Contenido_SmartyWrapper::getInstance(true);

        // translations
        $tpl->assign('trans', array(
            'label' => Pifa::i18n('LABEL'),
            'value' => Pifa::i18n('VALUE')
        ));

        $tpl->assign('i', $index);

        // option
        $tpl->assign('option', array(
            'label' => '',
            'value' => ''
        ));

        $tpl->display($cfg['templates']['pifa_ajax_option_row']);

    }

}

?>