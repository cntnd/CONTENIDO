<?php

/**
 * This file contains the PifaFieldCollection & PifaField class.
 *
 * @package    Plugin
 * @subpackage FormAssistant
 * @author     Marcus Gnaß <marcus.gnass@4fb.de>
 * @copyright  four for business AG
 * @link       https://www.4fb.de
 */

// assert CONTENIDO framework
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * PIFA field item collection class.
 * It's a kind of model.
 *
 * @author Marcus Gnaß <marcus.gnass@4fb.de>
 * @method PifaField createNewItem($data)
 * @method PifaField|bool next
 */
class PifaFieldCollection extends ItemCollection {
    /**
     * Create an instance.
     *
     * @param string|bool $where clause to be used to load items or false
     *
     * @throws cDbException
     * @throws cInvalidArgumentException
     */
    public function __construct($where = false) {
        parent::__construct(cRegistry::getDbTableName('pifa_field'), 'idfield');
        $this->_setItemClass('PifaField');
        if (false !== $where) {
            $this->select($where);
        }
    }

    /**
     * Reorders a forms fields according to the given.
     *
     * @param int    $idform
     * @param string $idfields containing a CSV list of idfield as integer
     *
     * @throws cDbException
     */
    public static function reorder($idform, $idfields) {
        $sql = "-- PifaFieldCollection::reorder()
            UPDATE
                " . cRegistry::getDbTableName('pifa_field') . "
            SET
                field_rank = FIND_IN_SET(idfield, '$idfields')
            WHERE
                idform = $idform
            ;";

        cRegistry::getDb()->query($sql);
    }
}

/**
 * PIFA field item class.
 * It's a kind of model.
 *
 * @author Marcus Gnaß <marcus.gnass@4fb.de>
 */
class PifaField extends Item {

    /**
     * Size to use for VARCHAR fields.
     * Remember: the maximum row size for the used table type, not counting
     * BLOBs, is 65535.
     *
     * @var int
     * @todo PIFA should be able to calculate the size for one record by the
     *       size of its fields and handle it accordingly.
     */
    const VARCHAR_SIZE = 255;

    /**
     * Input field for single-line text.
     *
     * @var int
     */
    const INPUTTEXT = 1;

    /**
     * Input field for multi-line text.
     *
     * @var int
     */
    const TEXTAREA = 2;

    /**
     * Input field for single-line password.
     *
     * @var int
     */
    const INPUTPASSWORD = 3;

    /**
     * Radiobox.
     *
     * @var int
     */
    const INPUTRADIO = 4;

    /**
     * Checkbox
     *
     * @var int
     */
    const INPUTCHECKBOX = 5;

    /**
     * Selectbox allowing for selection of a single option.
     *
     * @var int
     */
    const SELECT = 6;

    /**
     * Selectbox allowing for selection of multiple options.
     *
     * @var int
     */
    const SELECTMULTI = 7;

    /**
     * Input field for date selection.
     *
     * @var int
     */
    const DATEPICKER = 8;

    /**
     * Input field for file selection.
     *
     * @var int
     */
    const INPUTFILE = 9;

    /**
     * Processbar.
     *
     * @var int
     */
    const PROCESSBAR = 10;

    /**
     * Slider.
     *
     * @var int
     */
    const SLIDER = 11;

    /**
     * Captcha.
     *
     * @var int
     */
    const CAPTCHA = 12;

    /**
     * Button of type submit.
     *
     * @var int
     */
    const BUTTONSUBMIT = 13;

    /**
     * Button of type reset.
     *
     * @var int
     */
    const BUTTONRESET = 14;

    /**
     * General button.
     *
     * @deprecated use PifaField::BUTTON instead
     * @var int
     */
    const BUTTONBACK = 15;

    /**
     * General button.
     *
     * @var int
     */
    const BUTTON = 15;

    /**
     * @var int
     */
    const MATRIX = 16;

    /**
     * A text to be displayed between form fields.
     * It's no form field on its own but should be handled like one.
     *
     * @var int
     */
    const PARA = 17;

    /**
     * A hidden input field.
     *
     * @var int
     */
    const INPUTHIDDEN = 18;

    /**
     * Begin of a fieldset.
     *
     * @var int
     */
    const FIELDSET_BEGIN = 19;

    /**
     * End of a fieldset.
     *
     * @var int
     */
    const FIELDSET_END = 20;

    /**
     * Button of type image (which is in fact a submit button).
     *
     * @var int
     */
    const BUTTONIMAGE = 21;

    /**
     * The form fields value.
     *
     * @var mixed
     */
    private $_value = NULL;

    /**
     * The file that was transmitted in case of INPUTFILE.
     *
     * @var array
     */
    private $_file = NULL;

    /**
     * Create an instance.
     *
     * @param string|bool $id ID of item to be loaded or false
     *
     * @throws cDbException
     * @throws cException
     */
    public function __construct($id = false) {
        parent::__construct(cRegistry::getDbTableName('pifa_field'), 'idfield');
        $this->setFilters([], []);
        if (false !== $id) {
            $this->loadByPrimaryKey($id);
        }
    }

    /**
     * Loads a pifa field dummy recordset by a template, which is used during
     * the creation of a new pifa form field.
     *
     * @param array $aRecordSet Associative array with pre-set values.
     * @return void
     */
    function loadByRecordSetTemplate(array $aRecordSet) {
        $aRecordSet = array_merge([
            'idfield' => 0,
            'idform' => 0,
            'field_rank' => 0,
            'field_type' => 0,
            'column_name' => '',
            'label' => '',
            'display_label' => 1,
            'default_value' => '',
            'option_labels' => '',
            'option_values' => '',
            'option_class' => '',
            'help_text' => '',
            'obligatory' => 1,
            'rule' => '',
            'error_message' => '',
            'css_class' => '',
        ], $aRecordSet);
        $this->loadByRecordSet($aRecordSet);
    }

    /**
     * Rule has to be stripslashed to allow regular expressions with
     * backslashes.
     *
     * @see Item::getField()
     *
     * @param string $field
     * @param bool   $bSafe
     *
     * @return mixed|string
     */
    function getField($field, $bSafe = true) {
        $value = parent::getField($field, $bSafe);
        if ('rule' === $field) {
            $value = stripslashes($value);
        }
        return $value;
    }

    /**
     * Getter for protected prop.
     */
    public function getLastError() {
        return $this->lasterror;
    }

    /**
     * Returns this fields stored value.
     *
     * @return mixed the $_value
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     *
     * @param mixed $value
     */
    public function setValue($value) {
        $this->_value = $value;
    }

    /**
     * keys: name, tmp_name
     *
     * @return array the $_file
     */
    public function getFile() {
        return $this->_file;
    }

    /**
     * @param array $_file
     */
    public function setFile(array $_file) {
        $this->_file = $_file;
    }

    /**
     * @throws PifaValidationException if an error occurred
     */
    public function validate() {

        // get value
        $values = $this->getValue();
        if (NULL === $values) {
            $values = $this->get('default_value');
        }

        // value could be an array (for form fields with suffix '[]')
        // so make it an array anyway ...
        if (!is_array($values)) {
            $values = [
                $values
            ];
        }

        foreach ($values as $value) {
            if (self::CAPTCHA == $this->get('field_type')) {
                // site secret key
                try {
                    $secret = getEffectiveSetting('pifa-recaptcha', 'secret', '');
                } catch (cDbException $e) {
                    $secret = '';
                } catch (cException $e) {
                    $secret = '';
                }

                if (cString::getStringLength($secret) === 0 || !isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                    $isValid = false;
                } else {
                    //get verify response data
                    $response = urlencode($_POST['g-recaptcha-response']);
                    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $response);
                    $responseData   = json_decode($verifyResponse);

                    $isValid = $responseData->success ? true : false;
                }
            } elseif (1 === cSecurity::toInteger($this->get('obligatory')) && 0 === cString::getStringLength($value)) {
                // check for obligatory & rule
                $isValid = false;
            } elseif (0 < cString::getStringLength($this->get('rule')) && in_array(preg_match($this->get('rule'), $value), [
                    false,
                    0
                ])) {
                // check for rule
                $isValid = false;
            } else {
                $isValid = true;
            }

            // throw error
            if (true !== $isValid) {
                $error_message = $this->get('error_message');
                if (NULL === $error_message) {
                    // $error_message = 'invalid data';
                    $error_message = '';
                }
                throw new PifaValidationException([
                    $this->get('idfield') => $error_message
                ]);
            }
        }
    }

    /**
     * Returns HTML for this form that should be displayed in frontend.
     *
     * @param array|null $errors to be displayed for form field
     *
     * @return string
     * @throws PifaException
     */
    public function toHtml(array $errors = NULL) {
        $out = '';
        switch (cSecurity::toInteger($this->get('field_type'))) {

            case self::FIELDSET_BEGIN:

                // optional class for field
                if (0 < cString::getStringLength(trim($this->get('css_class')))) {
                    $class = ' class="' . implode(' ', explode(',', $this->get('css_class'))) . '"';
                } else {
                    $class = '';
                }
                $out .= "\n\t<fieldset$class>";
                // add optional legend/description
                if (1 === cSecurity::toInteger($this->get('display_label'))) {
                    $label = $this->get('label');
                    $out .= "\n\t<legend>$label</legend>";
                }

                return $out;

            case self::FIELDSET_END:

                return "\n\t</fieldset>";

            default:

                $error = null;

                // build HTML content
                $content = [];
                try {
                    $content[] = $this->_getElemLabel();
                    $content[] = $this->_getElemField();
                    $content[] = $this->_getElemHelp();
                    $content[] = $this->_getElemScript();
                    // add this fields error message
                    if (isset($errors[$this->get('idfield')])) {
                        $error = $errors[$this->get('idfield')];
                        if (0 < cString::getStringLength($error)) {
                            $content[] = new cHTMLParagraph($error, 'pifa-error-message');
                        }
                    }
                } catch (PifaNotImplementedException $e) {
                    return NULL; // PASS // warning?
                }

                $content = array_filter($content);
                if (empty($content)) {
                    return NULL; // PASS // warning?
                }

                // CSS class for surrounding division
                $class = 'pifa-field-' . $this->get('field_type');
                // optional class for field
                if (0 < cString::getStringLength(trim($this->get('css_class')))) {
                    $class .= ' ' . implode(' ', explode(',', $this->get('css_class')));
                }
                // optional class for obligatory field
                if (true === (bool) $this->get('obligatory')) {
                    $class .= ' pifa-obligatory';
                }
                // optional error class for field
                if (NULL !== $error) {
                    $class .= ' pifa-error';
                }

                // ID for surrounding division
                $id = 'pifa-field-' . $this->get('idfield');

                // surrounding division
                $div = new cHTMLDiv($content, $class, $id);

                return "\n\t" . $div->render();
        }
    }

    /**
     * @return cHTMLLabel|string
     */
    private function _getElemLabel() {
        if (1 !== cSecurity::toInteger($this->get('display_label'))) {
            return '';
        }

        // get field data
        $idfield = cSecurity::toInteger($this->get('idfield'));
        $fieldType = cSecurity::toInteger($this->get('field_type'));
        $label = strip_tags($this->get('label'));

        if (NULL === $label) {
            return NULL;
        }

        // buttons have no external label
        // the label is instead used as element value (see _getElemField())
        if (in_array($fieldType, [
            self::BUTTONSUBMIT,
            self::BUTTONRESET,
            self::BUTTON,
            self::BUTTONIMAGE
        ])) {
            return NULL;
        }

        // obligatory fields have an additional ' *'
        if (true === (bool) $this->get('obligatory')) {
            $label .= ' *';
        }

        $elemLabel = new cHTMLLabel($label, 'pifa-field-elm-' . $idfield, 'pifa-field-lbl');
        if (self::INPUTRADIO === $fieldType) {
            $elemLabel->removeAttribute('for');
        }
        // set ID (workaround: remove ID first!)
        $elemLabel->removeAttribute('id');

        return $elemLabel;
    }

    /**
     * Creates the HTML for a form field.
     *
     * The displayed value of a form field can be set via a GET param whose name
     * has to be the fields column name which is set if the form is displayed
     * for the first time and user hasn't entered another value.
     *
     * @return cHTMLTextbox cHTMLTextarea cHTMLPasswordbox cHTMLSpan
     *         cHTMLSelectElement NULL cHTMLButton
     *
     * @throws PifaException
     * @throws PifaNotImplementedException if field type is not implemented
     */
    private function _getElemField() {

        // get field data
        $idfield = cSecurity::toInteger($this->get('idfield'));

        $fieldType = cSecurity::toInteger($this->get('field_type'));

        $columnName = $this->get('column_name');

        $label = strip_tags($this->get('label'));

        $uri = $this->get('uri');

        // get option labels & values
        // either from field or from external data source class
        $optionClass = $this->get('option_class');
        if (0 === cString::getStringLength(trim($optionClass))) {
            $optionLabels = $this->get('option_labels');
            if (NULL !== $optionLabels) {
                $optionLabels = explode(',', $optionLabels);
            }
            $optionValues = $this->get('option_values');
            if (NULL !== $optionValues) {
                $optionValues = explode(',', $optionValues);
            }
        } else {
            $filename = Pifa::fromCamelCase($optionClass);
            $filename = "extensions/class.pifa.$filename.php";
            if (false === file_exists(Pifa::getPath() . $filename)) {
                $msg = Pifa::i18n('MISSING_EOD_FILE');
                $msg = sprintf($msg, $filename);
                throw new PifaException($msg);
            }
            plugin_include(Pifa::getName(), $filename);
            if (false === class_exists($optionClass)) {
                $msg = Pifa::i18n('MISSING_EOD_CLASS');
                $msg = sprintf($msg, $optionClass);
                throw new PifaException($msg);
            }
            /** @var PifaExternalOptionsDatasourceInterface $dataSource */
            $dataSource = new $optionClass();
            $optionLabels = $dataSource->getOptionLabels();
            $optionValues = $dataSource->getOptionValues();
        }

        // ID for field & FOR for label
        $id = 'pifa-field-elm-' . $idfield;

        // get current value
        $value = $this->getValue();

        // if no current value is given
        if (NULL === $value) {
            // the fields default value is used
            $value = $this->get('default_value');
            // which could be overwritten by a GET param
            if (array_key_exists($columnName, $_GET)) {
                $value = $_GET[$columnName];
            }
        }

        // try to prevent XSS ... the lazy way ...
        if ( is_array($value) ) {
            foreach ($value as $key => $val) {
                $value[$key] = conHtmlentities($val, ENT_COMPAT | ENT_HTML401, 'UTF-8');
            }
        } else {
            $value = conHtmlentities($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }

        switch ($fieldType) {

            case self::INPUTTEXT:

                $elemField = new cHTMLTextbox($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                // due to a bug setting NULL as title leads to title="title"
                if (!is_null($this->get('default_value'))) {
                    $elemField->setAttribute('title', $this->get('default_value'));
                }
                if (!is_null($value)) {
                    $elemField->setValue($value);
                }
                break;

            case self::TEXTAREA:

                $elemField = new cHTMLTextarea($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                if (!is_null($this->get('default_value'))) {
                    $elemField->setAttribute('title', $this->get('default_value'));
                }
                if (!is_null($value)) {
                    $elemField->setValue($value);
                }
                break;

            case self::INPUTPASSWORD:

                $elemField = new cHTMLPasswordbox($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                if (!is_null($this->get('default_value'))) {
                    $elemField->setAttribute('title', $this->get('default_value'));
                }
                if (!is_null($value)) {
                    $elemField->setValue($value);
                }
                break;

            case self::INPUTRADIO:
            case self::INPUTCHECKBOX:

                $count = min([
                    count($optionLabels),
                    count($optionValues)
                ]);
                $tmpHtml = '';
                for ($i = 0; $i < $count; $i++) {
                    if (self::INPUTRADIO === $fieldType) {
                        $elemField = new cHTMLRadiobutton($columnName, $optionValues[$i]);
                    } elseif (self::INPUTCHECKBOX === $fieldType) {
                        $elemField = new cHTMLCheckbox($columnName . '[]', $optionValues[$i]);
                    }
                    if (!is_array($value)) {
                        $value = explode(',', $value);
                    }
                    if (isset($elemField)) {
                        $elemField->setChecked(in_array($optionValues[$i], $value));
                        $elemField->setLabelText($optionLabels[$i]);
                        $tmpHtml .= $elemField->render();
                    }
                }
                $elemField = new cHTMLSpan($tmpHtml);
                break;

            case self::SELECT:

                $elemField = new cHTMLSelectElement($columnName);

                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                $autofill = [];
                $count = min([
                    count($optionLabels),
                    count($optionValues)
                ]);

                for ($i = 0; $i < $count; $i++) {
                    $autofill[$optionValues[$i]] = $optionLabels[$i];
                }

                $elemField->autoFill($autofill);
                $elemField->setDefault($value);

                break;

            case self::SELECTMULTI:

                $elemField = new cHTMLSelectElement($columnName);
                $elemField->setMultiselect();

                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                $autofill = [];
                $count = min([
                    count($optionLabels),
                    count($optionValues)
                ]);

                for ($i = 0; $i < $count; $i++) {
                    $autofill[$optionValues[$i]] = $optionLabels[$i];
                }

                $elemField->autoFill($autofill);
                $elemField->setDefault($value);

                break;

            case self::DATEPICKER:

                // hidden field to post date in generic date format
                $hiddenField = new cHTMLHiddenField($columnName);
                $hiddenField->removeAttribute('id')->setID($id . '-hidden');
                if (!is_null($value)) {
                    $hiddenField->setValue($value);
                }

                // textbox to display date in localized date format
                $textbox = new cHTMLTextbox($columnName . '_visible');
                // set ID (workaround: remove ID first!)
                $textbox->removeAttribute('id')->setID($id);

                // surrounding div
                $elemField = new cHTMLDiv([
                    $hiddenField,
                    $textbox
                ]);

                break;

            case self::INPUTFILE:

                $elemField = new cHTMLUpload($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                break;

            case self::PROCESSBAR:

                $elemField = NULL;
                // TODO PROCESSBAR is NYI
                // $elemField = new cHTML();
                break;

            case self::SLIDER:

                $elemField = NULL;
                // TODO SLIDER is NYI
                // $elemField = new cHTML();
                break;

            case self::CAPTCHA:
                // input
                $elemField = new cHTMLTextbox($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                if (null !== $value) {
                    $elemField->setValue($value);
                }

                // google recaptcha integration
                try {
                    $sitekey = getEffectiveSetting('pifa-recaptcha', 'sitekey', '');
                } catch (cDbException $e) {
                    $sitekey = '';
                } catch (cException $e) {
                    $sitekey = '';
                }

                $elemScript = new cHTMLScript();
                $elemScript->setAttribute("src", "https://www.google.com/recaptcha/api.js");
                $elemDiv = new cHTMLDiv('<div class="g-recaptcha" data-sitekey="' . $sitekey . '"></div>');
                $elemField = $elemScript . PHP_EOL . $elemDiv;

                break;

            case self::BUTTONSUBMIT:
            case self::BUTTONRESET:
            case self::BUTTON:
            case self::BUTTONIMAGE:
                $modes = [
                    self::BUTTONSUBMIT => 'submit',
                    self::BUTTONRESET => 'reset',
                    self::BUTTON => 'button',
                    self::BUTTONIMAGE => 'image'
                ];
                $mode = $modes[$fieldType];
                $elemField = new cHTMLButton($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                $elemField->setMode($mode);
                if ('image' === $mode) {
                    $elemField->setAttribute('src', $uri);
                    $elemField->removeAttribute('value');
                } else {
                    // set label or mode as value
                    $elemField->setTitle($label? $label : $mode);
                }
                break;

            case self::MATRIX:

                $elemField = NULL;
                // TODO MATRIX is NYI
                // $elemField = new cHTML();
                break;

            case self::PARA:
                $elemField = NULL;
                // TODO PARA is NYI
                // $elemField = new cHTML();
                break;

            case self::INPUTHIDDEN:
                $elemField = new cHTMLHiddenField($columnName);
                // set ID (workaround: remove ID first!)
                $elemField->removeAttribute('id')->setID($id);
                if (NULL !== $value) {
                    $elemField->setValue($value);
                }
                break;

            default:
                $msg = Pifa::i18n('NOT_IMPLEMENTED_FIELDTYPE');
                $msg = sprintf($msg, $fieldType);
                throw new PifaNotImplementedException($msg);
        }

        return $elemField;
    }

    /**
     * @return cHTMLParagraph|null
     */
    private function _getElemHelp() {
        $helpText = $this->get('help_text');

        $p = NULL;
        if (0 < cString::getStringLength($helpText)) {
            $p = new cHTMLParagraph($helpText, 'pifa-field-help');
        }

        return $p;
    }

    /**
     * @return cHTMLScript|null
     */
    public function _getElemScript() {

        // ID for field & FOR for label
        $idfield = cSecurity::toInteger($this->get('idfield'));
        $fieldType = cSecurity::toInteger($this->get('field_type'));

        switch ($fieldType) {
            case self::DATEPICKER:
                $sel = '#pifa-field-elm-' . $idfield;
                // dateFormat: 'yy-mm-dd', // could be different
                // altFormat as ISO_8601
                $script = "if (typeof jQuery == \"function\") {
                	jQuery(function(){ jQuery('$sel').datepicker({
                    	altFormat: 'yy-mm-dd',
                    	altField: '$sel-hidden'
                	});});
                }";
                break;
            default:
                $script = '';
        }

        $elemScript = NULL;
        if (0 < cString::getStringLength($script)) {
            $elemScript = new cHTMLScript();
            $elemScript->setContent($script);
        }

        return $elemScript;
    }

    /**
     * Returns an array containing all field type ids.
     *
     * @return array
     */
    public static function getFieldTypeIds() {
        return array_keys(self::getFieldTypeNames());
    }

    /**
     * Returns an array containing all field type ids mapped to their names.
     *
     * The order of field types in this array influences the order of icons
     * displayed in the backend for selection!
     *
     * @return array
     */
    public static function getFieldTypeNames() {
        return [
            self::INPUTTEXT => Pifa::i18n('INPUTTEXT'),
            self::TEXTAREA => Pifa::i18n('TEXTAREA'),
            self::INPUTPASSWORD => Pifa::i18n('INPUTPASSWORD'),
            self::INPUTRADIO => Pifa::i18n('INPUTRADIO'),
            self::INPUTCHECKBOX => Pifa::i18n('INPUTCHECKBOX'),
            self::SELECT => Pifa::i18n('SELECT'),
            self::SELECTMULTI => Pifa::i18n('SELECTMULTI'),
            self::DATEPICKER => Pifa::i18n('DATEPICKER'),
            self::INPUTFILE => Pifa::i18n('INPUTFILE'),
            self::PROCESSBAR => Pifa::i18n('PROCESSBAR'),
            self::SLIDER => Pifa::i18n('SLIDER'),
            self::CAPTCHA => Pifa::i18n('CAPTCHA'),
            // self::MATRIX => Pifa::i18n('MATRIX'),
            self::PARA => Pifa::i18n('PARAGRAPH'),
            self::INPUTHIDDEN => Pifa::i18n('INPUTHIDDEN'),
            self::FIELDSET_BEGIN => Pifa::i18n('FIELDSET_BEGIN'),
            self::FIELDSET_END => Pifa::i18n('FIELDSET_END'),
            self::BUTTONSUBMIT => Pifa::i18n('BUTTONSUBMIT'),
            self::BUTTONRESET => Pifa::i18n('BUTTONRESET'),
            self::BUTTON => Pifa::i18n('BUTTON'),
            self::BUTTONIMAGE => Pifa::i18n('BUTTONIMAGE')
        ];
    }

    /**
     * Return the field type name for the given field type id.
     *
     * @param int $fieldTypeId
     * @return string
     */
    public static function getFieldTypeName($fieldTypeId) {
        $fieldTypeId = cSecurity::toInteger($fieldTypeId);
        $fieldTypeNames = self::getFieldTypeNames();

        if (array_key_exists($fieldTypeId, $fieldTypeNames)) {
            $fieldTypeName = $fieldTypeNames[$fieldTypeId];
        } else {
            $fieldTypeName = Pifa::i18n('UNKNOWN');
        }

        return $fieldTypeName;
    }

    /**
     * Return this fields type name.
     *
     * @return string
     */
    public function getMyFieldTypeName() {
        return self::getFieldTypeName($this->get('field_type'));
    }

    /**
     * Returns a string describing this fields database data type as used in
     * MySQL CREATE and ALTER TABLE statements.
     *
     * @throws PifaException if field is not loaded
     * @throws PifaException if field type is not implemented
     */
    public function getDbDataType() {
        if (!$this->isLoaded()) {
            $msg = Pifa::i18n('FIELD_LOAD_ERROR');
            throw new PifaException($msg);
        }

        $fieldType = cSecurity::toInteger($this->get('field_type'));

        switch ($fieldType) {

            // Text and password input fields can store a string of
            // arbitrary length. Cause they are single lined it does not
            // make sense to enable them storing more than 1023 characters
            // though.
            case self::INPUTTEXT:
            case self::INPUTPASSWORD:
            case self::INPUTHIDDEN:
            case self::INPUTFILE:

                return 'VARCHAR(' . self::VARCHAR_SIZE . ')';

            // A group of checkboxes can store multiple values. So this has
            // to be implemented as a CSV string of max. 1023 characters.
            case self::INPUTCHECKBOX:

                return 'VARCHAR(' . self::VARCHAR_SIZE . ')';

            // Textareas are designed to store longer texts so I chose the
            // TEXT data type to enable them storing up to 65535 characters.
            case self::TEXTAREA:

                return 'TEXT';

            // A group of radiobuttons or a select box can store a single
            // value of a given set of options. This can be implemented
            // as an enumeration.
            case self::INPUTRADIO:
            case self::SELECT:
            case self::SELECTMULTI:

                // $optionValues = $this->get('option_values');
                // $optionValues = explode(',', $optionValues);
                // array_map(function ($val) {
                // return "'$val'";
                // }, $optionValues);
                // $optionValues = join(',', $optionValues);

                // return "ENUM($optionValues)";

                // as long as the GUI does not offer an entry to define option
                // values when creating a new field assume VARCHAR as data type
                return 'VARCHAR(' . self::VARCHAR_SIZE . ')';

            // The datepicker can store a date having an optional time
            // portion. I chose DATETIME as data type over TIMESTAMP due to
            // its limitation of storing dates before 1970-01-01 although
            // even DATETIME can't store dates before 1000-01-01.
            case self::DATEPICKER:

                return 'DATETIME';

            // Buttons don't have any values that should be stored.
            case self::BUTTONSUBMIT:
            case self::BUTTONRESET:
            case self::BUTTON:
            case self::BUTTONIMAGE:

                return NULL;

            // TODO For some filed types I havn't yet decided which data
            // type to use.
            case self::PROCESSBAR:
            case self::SLIDER:
            case self::CAPTCHA:
            case self::MATRIX:
            case self::PARA:
            case self::FIELDSET_BEGIN:
            case self::FIELDSET_END:

                return NULL;

            default:
                $msg = Pifa::i18n('NOT_IMPLEMENTED_FIELDTYPE');
                $msg = sprintf($msg, $fieldType);
                // Util::logDump($this->values);
                throw new PifaException($msg);
        }
    }

    /**
     * Deletes this form with all its fields and stored data.
     * The forms data table is also dropped.
     *
     * @throws PifaException
     * @throws cDbException
     */
    public function delete() {
        $db = cRegistry::getDb();

        if (!$this->isLoaded()) {
            $msg = Pifa::i18n('FIELD_LOAD_ERROR');
            throw new PifaException($msg);
        }

        // update ranks of younger siblings
        $sql = "-- PifaField->delete()
            UPDATE
                " . cRegistry::getDbTableName('pifa_field') . "
            SET
                field_rank = field_rank - 1
            WHERE
                idform = " . cSecurity::toInteger($this->get('idform')) . "
                AND field_rank > " . cSecurity::toInteger($this->get('field_rank')) . "
            ;";
        $db->query($sql);

        // delete field
        $sql = "-- PifaField->delete()
            DELETE FROM
                " . cRegistry::getDbTableName('pifa_field') . "
            WHERE
                idfield = " . cSecurity::toInteger($this->get('idfield')) . "
            ;";
        if (false === $db->query($sql)) {
            $msg = Pifa::i18n('FIELD_DELETE_ERROR');
            throw new PifaException($msg);
        }

        // drop column of data table
        if (0 < cString::getStringLength(trim($this->get('column_name')))) {
            $pifaForm = new PifaForm($this->get('idform'));

            if (0 < cString::getStringLength(trim($pifaForm->get('data_table')))) {
                $sql = "-- PifaField->delete()
                    ALTER TABLE
                        `" . cSecurity::toString($pifaForm->get('data_table')) . "`
                    DROP COLUMN
                        `" . cSecurity::toString($this->get('column_name')) . "`
                    ;";
                if (false === $db->query($sql)) {
                    $msg = Pifa::i18n('COLUMN_DROP_ERROR');
                    throw new PifaException($msg);
                }
            }
        }
    }

    /**
     * Determines for which form field types which data should be editable in
     * backend.
     *
     * @param string $columnName for data to edit
     *
     * @return bool
     *
     * @throws PifaException
     */
    public function showField($columnName) {
        $fieldType = $this->get('field_type');
        $fieldType = cSecurity::toInteger($fieldType);

        switch ($columnName) {

            case 'idfield':
            case 'idform':
            case 'field_rank':
            case 'field_type':
                // will never be editable directly
                return false;

            case 'column_name':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    // self::BUTTONSUBMIT,
                    // self::BUTTONRESET,
                    // self::BUTTON,
                    // self::BUTTONIMAGE,
                    self::MATRIX,
                    self::INPUTHIDDEN,
                    // self::FIELDSET_BEGIN,
                    // self::FIELDSET_END,
                ]);

            case 'label':
            case 'display_label':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    self::BUTTONSUBMIT,
                    self::BUTTONRESET,
                    self::BUTTON,
                    // self::BUTTONIMAGE,
                    self::MATRIX,
                    self::PARA,
                    // self::INPUTHIDDEN,
                    self::FIELDSET_BEGIN,
                    // self::FIELDSET_END,
                ]);

            case 'default_value':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    self::BUTTONSUBMIT,
                    self::BUTTONRESET,
                    self::BUTTON,
                    // self::BUTTONIMAGE,
                    self::MATRIX,
                    self::INPUTHIDDEN,
                ]);

            case 'option_labels':
            case 'option_values':
            case 'option_class':
                return in_array($fieldType, [
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                ]);

            case 'help_text':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    self::BUTTONSUBMIT,
                    self::BUTTONRESET,
                    self::BUTTON,
                    self::BUTTONIMAGE,
                    self::MATRIX,
                    self::PARA,
                    self::FIELDSET_BEGIN,
                ]);

            case 'obligatory':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    // self::BUTTONSUBMIT,
                    // self::BUTTONRESET,
                    // self::BUTTON,
                    // self::BUTTONIMAGE,
                    self::MATRIX,
                    self::INPUTHIDDEN,
                ]);

            case 'rule':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    // self::BUTTONSUBMIT,
                    // self::BUTTONRESET,
                    // self::BUTTON,
                    // self::BUTTONIMAGE,
                    self::MATRIX,
                    self::INPUTHIDDEN,
                ]);

            case 'error_message':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    // self::BUTTONSUBMIT,
                    // self::BUTTONRESET,
                    // self::BUTTON,
                    // self::BUTTONIMAGE,
                    self::MATRIX,
                    self::INPUTHIDDEN,
                ]);

            case 'css_class':
                return in_array($fieldType, [
                    self::INPUTTEXT,
                    self::TEXTAREA,
                    self::INPUTPASSWORD,
                    self::INPUTRADIO,
                    self::INPUTCHECKBOX,
                    self::SELECT,
                    self::SELECTMULTI,
                    self::DATEPICKER,
                    self::INPUTFILE,
                    self::PROCESSBAR,
                    self::SLIDER,
                    self::CAPTCHA,
                    self::BUTTONSUBMIT,
                    self::BUTTONRESET,
                    self::BUTTON,
                    self::BUTTONIMAGE,
                    self::MATRIX,
                    self::PARA,
                    self::FIELDSET_BEGIN,
                    // self::INPUTHIDDEN,
                ]);

            case 'uri':
                return in_array($fieldType, [
                    // self::INPUTTEXT,
                    // self::TEXTAREA,
                    // self::INPUTPASSWORD,
                    // self::INPUTRADIO,
                    // self::INPUTCHECKBOX,
                    // self::SELECT,
                    // self::SELECTMULTI,
                    // self::DATEPICKER,
                    // self::INPUTFILE,
                    // self::PROCESSBAR,
                    // self::SLIDER,
                    // self::CAPTCHA,
                    // self::BUTTONSUBMIT,
                    // self::BUTTONRESET,
                    // self::BUTTON,
                    self::BUTTONIMAGE,
                    // self::MATRIX,
                    // self::PARA,
                    // self::FIELDSET_BEGIN,
                    // self::INPUTHIDDEN,
                ]);

            default:
                $msg = Pifa::i18n('NOT_IMPLEMENTED_FIELDPROP');
                $msg = sprintf($msg, $columnName);
                throw new PifaException($msg);
        }
    }

    /**
     * @return array
     */
    public function getOptions() {
        $option_labels = $this->get('option_labels');
        $option_values = $this->get('option_values');

        $out = [];
        if (0 < cString::getStringLength($option_labels . $option_values)) {
            $option_labels = explode(',', $option_labels);
            $option_values = explode(',', $option_values);

            // str_getcsv requires PHP 5.3 :(
            // $option_labels = str_getcsv($option_labels);
            // $option_values = str_getcsv($option_values);

            // instead replace commas stored as entities by real commas
            $func = function($v) {
                return str_replace('&#44;', ',', $v);
            };
            $option_labels = array_map($func, $option_labels);
            $option_values = array_map($func, $option_values);

            foreach (array_keys($option_labels) as $key) {
                $out[] = [
                    'label' => $option_labels[$key],
                    'value' => $option_values[$key]
                ];
            }
        }

        return $out;
    }
}
