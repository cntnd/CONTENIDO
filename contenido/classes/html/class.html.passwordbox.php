<?php

/**
 * This file contains the cHTMLPasswordbox class.
 *
 * @package Core
 * @subpackage GUI_HTML
 *
 * @author Simon Sprankel
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * cHTMLPasswordbox class represents a password form field.
 *
 * @package Core
 * @subpackage GUI_HTML
 */
class cHTMLPasswordbox extends cHTMLFormElement {

    /**
     * Constructor to create an instance of this class.
     *
     * Creates an HTML password box.
     *
     * If no additional parameters are specified, the default width is
     * 20 units.
     *
     * @param string $name
     *         Name of the element
     * @param string $value [optional]
     *         Initial value of the box
     * @param int $width [optional]
     *         width of the text box
     * @param int $maxlength [optional]
     *         maximum input length of the box
     * @param string $id [optional]
     *         ID of the element
     * @param bool $disabled [optional]
     *         Item disabled flag (non-empty to set disabled)
     * @param int|null $tabindex [optional]
     *         Tab index for form elements
     * @param string $accessKey [optional]
     *         Key to access the field
     * @param string $class [optional]
     *         the class of this element
     */
    public function __construct($name, $value = '', $width = 0, $maxlength = 0, $id = '', $disabled = false, $tabindex = null, $accessKey = '', $class = '') {
        parent::__construct($name, $id, $disabled, $tabindex, $accessKey, $class);
        $this->_tag = 'input';
        $this->setValue($value);

        $this->setWidth($width);
        $this->setMaxLength($maxlength);

        $this->updateAttribute('type', 'password');
    }

    /**
     * Sets the autocomplete attribute of the element.
     *
     * @param string $value - The autocomplete attribute value
     * @return cHTMLPasswordbox|cHTML
     */
    public function setAutocomplete($value) {
        $value = cString::toLowerCase(cSecurity::toString($value));
        return $this->updateAttribute('autocomplete', $value);
    }

    /**
     * Sets the width of the text box.
     *
     * @param int $width
     *         width of the text box
     * @return cHTMLPasswordbox
     *         $this for chaining
     */
    public function setWidth($width) {
        $width = cSecurity::toInteger($width);

        if ($width <= 0) {
            $width = 20;
        }

        return $this->updateAttribute('size', $width);
    }

    /**
     * Sets the maximum input length of the text box.
     *
     * @param int $maxLength
     *         maximum input length
     * @return cHTMLPasswordbox
     *         $this for chaining
     */
    public function setMaxLength($maxLength) {
        $maxLength = cSecurity::toInteger($maxLength);

        if ($maxLength <= 0) {
            return $this->removeAttribute('maxlength');
        } else {
            return $this->updateAttribute('maxlength', $maxLength);
        }
    }

    /**
     * Sets the initial value of the text box.
     *
     * @param string $value
     *         Initial value
     * @return cHTMLPasswordbox
     *         $this for chaining
     */
    public function setValue($value) {
        return $this->updateAttribute('value', $value);
    }

    /**
     * Generates the HTML markup for the input field of type password.
     * Additionally, it deals with the attribute "autocomplete" set to "off".
     * This should work as expected but some browser or password manager may
     * still pre fill the field with the previous stored value.
     * Setting the field initially to readonly and enabling it again after
     * getting focus does the trick!
     *
     * @TODO This function could be moved to somewhere else, because ll input, textarea,
     *       select and form elements could use the autocomplete attribute.
     *       But, only input and textarea can have readonly attribute.
     *
     *
     * @return string
     */
    public function toHtml() {
        $sReadonly = $this->getAttribute('readonly') !== null;
        $autocomplete = $this->getAttribute('autocomplete');

        if ($autocomplete !== 'off' || $sReadonly) {
            // Field has no autocomplete="off" or has already readonly attribute, nothing to do here...
            return parent::toHtml();
        }

        // Handle autocomplete="off", disable the field and enable it again via JavaScript!

        if (!$this->getAttribute('id')) {
            $this->advanceID();
        }
        $this->setAttribute('readonly', 'readonly');

        $html = parent::toHtml();
        // NOTE: If you change the code below, don't forget to adapt the unit test
        //       cHtmlPasswordBoxTest->testAutocomplete()!
        $html .= '
    <script type="text/javascript">
        (function(Con, $) {
            $(function() {
                // Remove readonly attribute on focus
                $("#' . $this->getID() . '").on("focus", function() {
                    $(this).prop("readonly", false);
                });
            });
        })(Con, Con.$);
    </script>
        ';

        return $html;
    }

}
